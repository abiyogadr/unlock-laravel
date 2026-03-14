<?php

namespace App\Http\Controllers\Ecourse;

use App\Http\Controllers\Controller;
use App\Models\Ecourse\Course;
use App\Models\Ecourse\CourseCategory;
use App\Models\Ecourse\CoursePackage;
use App\Models\Ecourse\UserCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;

class CourseInertiaController extends Controller
{
    public function catalog(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();

        $query = Course::published();

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('short_description', 'like', '%' . $request->search . '%');
            });
        }

        // Filter: Show courses owned directly OR if they have active subscription, show everything
        if ($user) {
            $userCourseIds = $user->ecourses()
                ->where(function($q) {
                    $q->where('acquisition_type', '!=', 'subscription')
                      ->orWhere('progress', '>=', 100);
                })
                ->pluck('course_id')
                ->toArray();

            $hasActiveSubscription = $user->subscriptions()
                ->where('status', 'active')
                ->where('subscription_type', 'monthly')
                ->where('end_date', '>=', now())
                ->exists();
        } else {
            $userCourseIds = [];
            $hasActiveSubscription = false;
        }

        // Ambil semua course yang sesuai filter (tanpa pagination)
        $courses = $query
            ->with(['speaker', 'categories'])
            ->get();

        // For the public catalog, show all published courses.
        // The `is_owned` flag is computed per-course using $userCourseIds (empty for guests).
        $catalogCourses = $courses;

        // Ambil semua kursus per kategori (untuk ditampilkan horisontal)
        $categories = CourseCategory::active()
            ->with(['courses' => function($q) {
                $q->published()->with(['speaker', 'categories']);
            }])
            ->get(['id', 'name', 'slug'])
            ->map(function($category) use ($userCourseIds, $hasActiveSubscription) {
                $filteredCourses = $category->courses; // show all published courses in each category
                
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'courses' => $filteredCourses->map(fn ($course) => $this->mapCourse($course, $userCourseIds))->values(),
                ];
            });

        return Inertia::render('Catalog', [
            'courses' => $catalogCourses->map(fn ($course) => $this->mapCourse($course, $userCourseIds))->values(),
            'categories' => $categories,
            'filters' => [
                'category' => $request->category,
                'search' => $request->search,
            ],
        ]);
    }

    public function show(Request $request, Course $course)
    {
        /** @var User|null $user */
        $user = Auth::user();

        $course->load(['speaker', 'categories', 'modules']);

        $userCourse = $user ? $user->ecourses()->where('course_id', $course->id)->first() : null;
        $userProgress = $userCourse?->progress ?? 0;

        $userModulesProgress = $userCourse
            ? $userCourse->userCourseModules()->pluck('progress', 'course_module_id')->toArray()
            : [];

        $completedModuleIds = $userCourse
            ? $userCourse->userCourseModules()->where('is_completed', true)->pluck('course_module_id')->toArray()
            : [];

        $modules = $course->modules
            ->map(function ($module) use ($userModulesProgress, $completedModuleIds) {
                return [
                    'id' => $module->id,
                    'title' => $module->title,
                    'slug' => $module->slug,
                    'formatted_duration' => $module->formatted_duration,
                    'progress' => $userModulesProgress[$module->id] ?? 0,
                    'is_completed' => in_array($module->id, $completedModuleIds),
                ];
            });

        // retrieve all active monthly subscriptions (same as dashboard)
        $activeSubs = $user ? $user->subscriptions()
            ->where('status', 'active')
            ->where('subscription_type', 'monthly')
            ->where('end_date', '>=', now())
            ->get() : collect();

        $hasActiveSubscription = $activeSubs->isNotEmpty();
        // choose the most-recently-ending sub for display purposes
        $subscription = $activeSubs->sortByDesc('end_date')->first();

        // compute remaining certificates across all active subs
        $certificateRemaining = $activeSubs->reduce(function($carry, $s) {
            $quota = $s->certificate_quota ?? 0;
            $used  = $s->certificate_used ?? 0;
            return $carry + max(0, $quota - $used);
        }, 0);

        // Granular ownership flags
        $isOwnedDirectly = $userCourse && $userCourse->acquisition_type !== 'subscription';
        // Access is granted if they own it directly OR have an active subscription
        $hasAccess = $isOwnedDirectly || (bool)$hasActiveSubscription;

        // For the 'is_owned' flag in the mapped course, we use the broad 'hasAccess' 
        // to make sure the UI treats it as an accessible course.
        $ownedCourseIds = $hasAccess ? [$course->id] : [];

        // load subscription packages similar to payment page so the popup can show options
        $packages = CoursePackage::where('is_active', true)
            ->where('package_type', 'subscription')
            ->orderBy('price', 'asc')
            ->get()
            ->map(fn($pkg) => [
                'id' => $pkg->id,
                'name' => $pkg->name,
                'price' => $pkg->price,
                'discount_price' => $pkg->discount_price,
                'ustar_credits' => $pkg->ustar_credits,
                'duration_days' => $pkg->duration_days,
                'plan_duration' => $pkg->plan_duration,
                'package_type' => $pkg->package_type,
                'description' => $pkg->description ?? null,
            ]);

        return Inertia::render('CourseShow', [
            'course_slug' => $course->slug,
            'course' => $this->mapCourse($course, $ownedCourseIds),
            'modules' => $modules,
            'stats' => [
                'module_count' => $course->modules->count(),
                'formatted_duration' => $this->formatDuration($course->modules->sum('duration_minutes')),
                'total_duration' => $course->modules->sum('duration_minutes'),
                'students_count' => $course->userCourses()->count(),
            ],
            'user_progress' => $userProgress,
            'first_module_slug' => $modules->first()['slug'] ?? null,
            'subscription' => $subscription ? [
                'status' => $subscription->status,
                'ends_at' => $subscription->end_date?->locale('id')->translatedFormat('d M Y'),
            ] : null,
            'certificate_remaining' => $certificateRemaining,
            'has_user_course' => (bool) $userCourse,
            'is_owned_directly' => $isOwnedDirectly,
            'has_current_module' => (bool) ($userCourse && $userCourse->current_module_id),
            'packages' => $packages,
        ]);
    }

    private function formatDuration($minutes)
    {
        if (!$minutes) return '0 menit';
        
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        if ($hours > 0 && $mins > 0) {
            return $hours . ' jam ' . $mins . ' menit';
        } elseif ($hours > 0) {
            return $hours . ' jam';
        } else {
            return $mins . ' menit';
        }
    }

    private function mapCourse(Course $course, array $ownedCourseIds): array
    {
        return [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'short_description' => $course->short_description,
            'description' => $course->description,
            'thumbnail_url' => $course->thumbnail_url,
            'kv_url' => $course->kv_url,
            'price' => $course->price,
            'credit_cost' => $course->credit_cost ?? 0,
            'is_free' => $course->is_free,
            'level' => $course->level,
            'objectives' => $course->objectives ? json_decode($course->objectives, true) : [],
            'speaker' => $course->speaker ? [
                'name' => $course->speaker->speaker_name,
                'job' => $course->speaker->position ?? 'Pembicara',
                'bio' => $course->speaker->company ?? '',
            ] : null,
            'categories' => $course->categories?->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])->values(),
            'is_owned' => in_array($course->id, $ownedCourseIds),
            'formatted_duration' => $course->formatted_duration,
        ];
    }
}
