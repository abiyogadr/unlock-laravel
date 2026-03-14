<?php

namespace App\Http\Controllers\Ecourse;

use App\Http\Controllers\Controller;
use App\Models\Ecourse\Course;
use App\Models\Ecourse\CourseCategory;
use App\Models\Ecourse\CourseModule;
use App\Models\Ecourse\UserCourse;
use App\Models\Ecourse\UserCourseModule;
use App\Models\Ecourse\CourseCertificate;
use App\Models\Ecourse\ModuleMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EcourseInertiaController extends Controller
{
    public function dashboard()
    {
        /** @var User|null $user */
        $user = $this->currentUser();

        // Guests should be redirected to login (outside the SPA)
        if (! $user) {
            return redirect('/login?redirect=' . request()->path());
        }

        // Compute subscription metadata first
        $activeSubs = $user->subscriptions()
            ->where('status', 'active')
            ->where(function($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->get();

        $hasActiveSubscription = $activeSubs->isNotEmpty();
        $maxEnd = $activeSubs->pluck('end_date')->filter()->max();

        $certificateRemaining = $activeSubs->reduce(function($carry, $s) {
            $quota = $s->certificate_quota ?? 0;
            $used = $s->certificate_used ?? 0;
            return $carry + max(0, $quota - $used);
        }, 0);

        // Small helper to apply the "subscription-only unless has certificate" filter
        $applySubFilter = function ($q) use ($user, $hasActiveSubscription) {
            if (! $hasActiveSubscription) {
                $q->where(function ($q2) use ($user) {
                    $q2->where('acquisition_type', '!=', 'subscription')
                       ->orWhereExists(function ($sub) use ($user) {
                           $sub->selectRaw('1')
                               ->from('course_certificates')
                               ->whereColumn('course_certificates.course_id', 'user_courses.course_id')
                               ->where('course_certificates.user_id', $user->id);
                       });
                });
            }
        };

        // Aggregates (total / in progress / completed) using filtered ecourses
        $aggregatesQuery = $user->ecourses();
        $applySubFilter($aggregatesQuery);

        $aggregates = $aggregatesQuery
            ->selectRaw(
                'COUNT(*) as total, SUM(CASE WHEN progress < 100 THEN 1 ELSE 0 END) as on_progress, SUM(CASE WHEN progress = 100 THEN 1 ELSE 0 END) as completed'
            )
            ->first();

        // Recent courses (limited)
        $recentQuery = $user->ecourses()->with(['course.speaker', 'course.categories', 'currentModule'])
            ->latest('updated_at')
            ->limit(6);
        $applySubFilter($recentQuery);

        $recentCourses = $recentQuery->get()->map(function ($userCourse) {
            return [
                'id' => $userCourse->id,
                'progress' => $userCourse->progress,
                'current_module' => $userCourse->currentModule?->title,
                'course' => $this->mapCourseSummary($userCourse->course, $userCourse),
            ];
        });

        // Per-category breakdown using same filtered ownership rules
        $categoryStats = CourseCategory::active()->get()->map(function ($category) use ($user, $applySubFilter) {
            $ownedQuery = $user->ecourses()->whereHas('course.categories', function ($q) use ($category) {
                $q->where('id', $category->id);
            });

            $applySubFilter($ownedQuery);

            $owned = $ownedQuery->get();

            $total = $owned->count();
            $completed = $owned->where('progress', '>=', 100)->count();
            $inProgress = $owned->where('progress', '<', 100)->count();
            $avgProgress = $total ? (int) round($owned->avg('progress')) : 0;

            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'total' => $total,
                'completed' => $completed,
                'in_progress' => $inProgress,
                'avg_progress' => $avgProgress,
            ];
        })->values();

        $stats = [
            'total_owned'  => (int) ($aggregates->total ?? 0),
            'in_progress'  => (int) ($aggregates->on_progress ?? 0),
            'completed'    => (int) ($aggregates->completed ?? 0),
            'certificates' => $user->courseCertificates()->count(),
            // Subscription meta
            'subscription_end' => $maxEnd ? $maxEnd->format('d M Y') : null,
            'certificate_remaining' => (int) $certificateRemaining,
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recent_courses' => $recentCourses,
            'category_stats' => $categoryStats,
            'user_name' => $user->name,
        ]);
    }

    public function myJourney(Request $request)
    {
        $user = $this->currentUser();

        $query = $user->ecourses()->with(['course.speaker', 'course.categories']);

        // Prioritize active monthly subscription for access rules
        $hasActiveSubscription = $user->subscriptions()
            ->where('status', 'active')
            ->where('subscription_type', 'monthly')
            ->where('end_date', '>=', now())
            ->exists();

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('course.categories', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Search
        if ($request->filled('search')) {
            $query->whereHas('course', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Jika tidak ada subscription aktif, sembunyikan entri yang akuisisinya 'subscription'
        // kecuali jika user sudah memiliki sertifikat untuk course tersebut
        if (! $hasActiveSubscription) {
            $query->where(function ($q) use ($user) {
                $q->where('acquisition_type', '!=', 'subscription')
                  ->orWhereExists(function ($sub) use ($user) {
                      $sub->selectRaw('1')
                          ->from('course_certificates')
                          ->whereColumn('course_certificates.course_id', 'user_courses.course_id')
                          ->where('course_certificates.user_id', $user->id);
                  });
            });
        }

        // Ambil semua userCourses yang sesuai filter pencarian/kategori
        $userCourses = $query->latest('updated_at')->get();

        // Ambil SEMUA userCourses (sesuai rule di atas) untuk menghitung angka di sebelah label kategori
        $allOwnedCoursesQuery = $user->ecourses()->with('course.categories');
        if (! $hasActiveSubscription) {
            $allOwnedCoursesQuery->where(function ($q) {
                $q->where('acquisition_type', '!=', 'subscription')
                  ->orWhere('progress', '>=', 100);
            });
        }
        $allOwnedCourses = $allOwnedCoursesQuery->get();

        $categories = CourseCategory::active()
            ->get(['id', 'name', 'slug'])
            ->map(function ($category) use ($allOwnedCourses) {
                $count = $allOwnedCourses->filter(function ($uc) use ($category) {
                    return $uc->course && $uc->course->categories->contains('id', $category->id);
                })->count();

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'courses_count' => $count,
                ];
            });

        $courses = $userCourses->map(fn ($userCourse) => [
            'id' => $userCourse->id,
            'progress' => $userCourse->progress,
            'status' => $userCourse->status,
            'course' => $this->mapCourseSummary($userCourse->course, $userCourse),
        ]);

        return Inertia::render('MyJourney', [
            'courses' => $courses,
            'categories' => $categories,
            'filters' => [
                'category' => $request->category,
                'search' => $request->search,
            ],
        ]);
    }

    public function player($courseSlug, $moduleSlug)
    {
        $user = $this->currentUser();
        
        $course = Course::where('slug', $courseSlug)
            ->with(['modules.materials'])
            ->firstOrFail();

        $module = $course->modules->firstWhere('slug', $moduleSlug) ?? abort(404);

        $userCourse = $user->ecourses()
            ->with(['userCourseModules'])
            ->where('course_id', $course->id)
            ->first();

        // Determine whether this user has an issued certificate for this course.
        $hasCertificate = CourseCertificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        // Restrict access: only logged-in users with an active subscription, direct purchase, or a certificate can view this module.
        // If the user does not have a UserCourse record, allow if they have an active subscription or a certificate.
        if (!$userCourse) {
            // Pick active subscription: require subscription_type = 'monthly' and end_date >= now()
            $activeSubscription = $user->subscriptions()
                ->where('status', 'active')
                ->where('subscription_type', 'monthly')
                ->where('end_date', '>=', now())
                ->orderByDesc('end_date')
                ->first();

            if ($activeSubscription) {
                $userCourse = UserCourse::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'enrolled_at' => now(),
                    'started_at' => now(),
                    'status' => 'in_progress',
                    'total_modules' => $course->modules()->count(),
                    'completed_modules' => 0,
                    'progress' => 0,
                    'acquisition_type' => 'subscription',
                    'subscription_id' => $activeSubscription->id,
                    'access_expires_at' => $activeSubscription->end_date,
                    'current_module_id' => $module->id,
                ]);
                $userCourse->load('userCourseModules');
            } elseif ($hasCertificate) {
                // Allow access if user has already earned a certificate for this course
                $userCourse = UserCourse::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'enrolled_at' => now(),
                    'started_at' => now(),
                    'status' => 'in_progress',
                    'total_modules' => $course->modules()->count(),
                    'completed_modules' => 0,
                    'progress' => 0,
                    'acquisition_type' => 'certificate',
                    'access_expires_at' => null,
                    'current_module_id' => $module->id,
                ]);
                $userCourse->load('userCourseModules');
            } else {
                abort(403, 'You do not have access to this course.');
            }
        }

        // If user already has a record, ensure it represents a valid purchase/subscription/certificate.
        if (!in_array($userCourse->acquisition_type, ['subscription', 'direct', 'idr', 'idr_purchase', 'certificate'], true)) {
            abort(403, 'You do not have access to this course.');
        }

        // Ensure current module track
        if ($userCourse->current_module_id !== $module->id) {
            $userCourse->update(['current_module_id' => $module->id]);
        }

        // Ensure progress record for this module exists
        UserCourseModule::firstOrCreate(
            ['user_course_id' => $userCourse->id, 'course_module_id' => $module->id],
            ['started_at' => now()]
        );

        $moduleProgress = $userCourse->userCourseModules->keyBy('course_module_id');

        $modules = $course->modules
            ->where('module_type', 'video')
            ->map(fn ($mod) => $this->mapModule($mod, $moduleProgress->get($mod->id)));

        $currentModule = $this->mapModule($module, $moduleProgress->get($module->id));

        $playerData = [
            'course' => $this->mapCourseSummary($course, $userCourse),
            'module' => $currentModule,
            'modules' => $modules,
            'progress' => $userCourse->progress,
            'completed_modules' => $moduleProgress->where('is_completed', true)->count(),
            'total_modules' => $modules->count(),
        ];

        return Inertia::render('Player', [
            'course_slug' => $courseSlug,
            'module_slug' => $moduleSlug,
            'player_data' => $playerData,
        ]);
    }

    public function certificates()
    {
        $user = $this->currentUser();
        
        $certificates = \App\Models\Ecourse\CourseCertificate::where('user_id', $user->id)
            ->with('course')
            ->latest('issued_at')
            ->paginate(9)
            ->through(fn ($certificate) => [
                'id' => $certificate->id,
                'course_title' => $certificate->course?->title,
                'certificate_number' => $certificate->certificate_number,
                'score' => $certificate->score,
                'issued_at' => optional($certificate->issued_at)->toDateString(),
                'thumbnail_url' => $certificate->course?->thumbnail_url,
                'view_url' => route('certificate.view', $certificate->certificate_number),

            ]);

        return Inertia::render('Certificates', [
            'certificates' => $certificates,
        ]);
    }

    public function getRecentIncomplete()
    {
        $user = $this->currentUser();
        
        $recentIncomplete = $user->ecourses()
            ->with(['course', 'currentModule'])
            ->where('progress', '<', 100)
            ->latest('updated_at')
            ->limit(3)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $recentIncomplete->map(function ($userCourse) {
                return [
                    'course_name' => $userCourse->course->title,
                    'course_slug' => $userCourse->course->slug,
                    'progress' => $userCourse->progress,
                    'current_module' => $userCourse->currentModule?->title,
                ];
            }),
        ]);
    }

    /**
     * Return the issued certificate for the authenticated user for a given course, if any.
     */
    public function getCourseCertificate(Course $course)
    {
        $user = $this->currentUser();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $certificate = \App\Models\Ecourse\CourseCertificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->latest('issued_at')
            ->first();

        if ($certificate) {
            return response()->json(['success' => true, 'data' => [
                'certificate_number' => $certificate->certificate_number,
                'view_url' => route('certificate.view', $certificate->certificate_number),
                'issued_at' => optional($certificate->issued_at)->toDateString(),
            ]]);
        } else {
            return response()->json(['success' => false, 'message' => 'No certificate found'], 404);
        }
    }

    /**
     * Return certificate status for given course including completion and subscription info
     */
    public function courseCertificateStatus(Course $course)
    {
        $user = $this->currentUser();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $certificate = \App\Models\Ecourse\CourseCertificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->latest('issued_at')
            ->first();

        // Check if user completed the course
        $userCourse = $user->ecourses()->where('course_id', $course->id)->first();
        $isCompleted = $userCourse ? ($userCourse->progress >= 100 || $userCourse->status === 'completed') : false;

        // Active subscription and remaining certificate quota
        $activeSubs = $user->subscriptions()
            ->where('status', 'active')
            ->where(function($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->get();

        $hasActiveSubscription = $activeSubs->isNotEmpty();
        $certificateRemaining = $activeSubs->reduce(function($carry, $s) {
            $quota = $s->certificate_quota ?? 0;
            $used = $s->certificate_used ?? 0;
            return $carry + max(0, $quota - $used);
        }, 0);

        $canGenerate = $isCompleted && $certificateRemaining > 0;

        return response()->json(['success' => true, 'data' => [
            'certificate' => $certificate ? [
                'certificate_number' => $certificate->certificate_number,
                'view_url' => route('certificate.view', $certificate->certificate_number),
                'issued_at' => optional($certificate->issued_at)->toDateString(),
            ] : null,
            'is_completed' => $isCompleted,
            'has_active_subscription' => $hasActiveSubscription,
            'certificate_remaining' => (int) $certificateRemaining,
            'can_generate' => (bool) $canGenerate,
        ]]);
    }

    /**
     * Attempt to generate a certificate for a completed course.
     */
    public function generateCourseCertificate(Course $course)
    {
        $user = $this->currentUser();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $userCourse = $user->ecourses()->where('course_id', $course->id)->first();
        if (! $userCourse || ($userCourse->progress < 100 && $userCourse->status !== 'completed')) {
            return response()->json(['success' => false, 'message' => 'Course not completed'], 422);
        }

        // Ensure certificate doesn't already exist
        $exists = \App\Models\Ecourse\CourseCertificate::where('user_id', $user->id)->where('course_id', $course->id)->exists();
        if ($exists) {
            $certificate = \App\Models\Ecourse\CourseCertificate::where('user_id', $user->id)->where('course_id', $course->id)->latest('issued_at')->first();
            return response()->json(['success' => true, 'data' => [
                'certificate_number' => $certificate->certificate_number,
                'view_url' => route('certificate.view', $certificate->certificate_number),
                'issued_at' => optional($certificate->issued_at)->toDateString(),
            ]]);
        }

        // Find an active subscription with available quota
        $activeSubs = $user->subscriptions()
            ->where('status', 'active')
            ->where(function($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->orderByDesc('end_date')
            ->get();

        $target = $activeSubs->first(function ($s) {
            return (($s->certificate_quota ?? 0) - ($s->certificate_used ?? 0)) > 0;
        });

        if (! $target) {
            return response()->json(['success' => false, 'message' => 'No certificate quota available'], 402);
        }

        // Consume quota and create certificate
        $target->certificate_used = ($target->certificate_used ?? 0) + 1;
        $target->save();

        $certificate = \App\Models\Ecourse\CourseCertificate::create([
            'certificate_number' => 'UNL-ECO' . now()->format('ym') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)),
            'user_id' => $user->id,
            'course_id' => $course->id,
            'course_title' => $course->title,
            'user_name' => $user->name,
            'score' => round($userCourse->progress ?? 100),
            'issued_at' => now(),
        ]);

        return response()->json(['success' => true, 'data' => [
            'certificate_number' => $certificate->certificate_number,
            'view_url' => route('certificate.view', $certificate->certificate_number),
            'issued_at' => optional($certificate->issued_at)->toDateString(),
        ]]);
    }

    /**
     * Ensure a UserCourse exists for the authenticated user and the given course
     * If current_module_id is missing, set it to the first module and return redirect URL
     */
    public function ensureUserCourse(Course $course)
    {
        $user = $this->currentUser();

        // Check active subscription (require subscription_type = 'monthly' and end_date >= now())
        $activeSubscription = $user->subscriptions()
            ->where('status', 'active')
            ->where('subscription_type', 'monthly')
            ->where('end_date', '>=', now())
            ->orderByDesc('end_date')
            ->first();

        // Prepare default attributes for create
        $createAttrs = [
            'enrolled_at' => now(),
            'started_at' => now(),
            'status' => 'in_progress',
            'total_modules' => $course->modules()->count(),
            'completed_modules' => 0,
            'progress' => 0,
        ];

        if ($activeSubscription) {
            $createAttrs['acquisition_type'] = 'subscription';
            $createAttrs['subscription_id'] = $activeSubscription->id;
            if ($activeSubscription->end_date) {
                $createAttrs['access_expires_at'] = $activeSubscription->end_date;
            }
        }

        // Find existing UserCourse or create a new one
        $userCourse = UserCourse::firstOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ],
            $createAttrs
        );

        // Verify user has access via subscription, direct ownership, or certificate
        $hasDirectOwnership = $userCourse->acquisition_type && in_array(
            $userCourse->acquisition_type,
            ['direct', 'idr', 'idr_purchase']
        );

        if (!$activeSubscription && !$hasDirectOwnership) {
            // Check if user has a valid certificate for this course
            $hasCertificate = \App\Models\Ecourse\CourseCertificate::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->exists();
            
            if (!$hasCertificate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke kursus ini. Silakan beli atau berlangganan.',
                ], 403);
            }
        }

        // If user has active subscription but existing row lacks acquisition_type AND it's not a direct purchase 
        // We only "claim" it as subscription if it wasn't bought directly before.
        if ($activeSubscription && !$userCourse->acquisition_type) {
            $userCourse->update([
                'acquisition_type' => 'subscription',
                'subscription_id' => $activeSubscription->id,
                'access_expires_at' => $activeSubscription->end_date ?? $userCourse->access_expires_at,
            ]);
        }

        // Refresh userCourse collection/properties after possible update
        $userCourse->refresh();

        // If current_module_id is already set, get that module
        if ($userCourse->current_module_id) {
            $mod = CourseModule::find($userCourse->current_module_id);
            if ($mod) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('ecourse.player', ['course' => $course->slug, 'module' => $mod->slug]),
                ]);
            }
        }

        // Otherwise set current_module_id to the first module of the course
        $firstModule = $course->modules()->orderBy('order_num')->first();
        if (! $firstModule) {
            return response()->json(['success' => false, 'message' => 'Course has no modules'], 422);
        }

        // Update user course with current module and make sure a module progress record exists
        $userCourse->update(['current_module_id' => $firstModule->id, 'started_at' => $userCourse->started_at ?? now()]);

        // Ensure UserCourseModule row exists (to track progress)
        UserCourseModule::firstOrCreate(
            [
                'user_course_id' => $userCourse->id,
                'course_module_id' => $firstModule->id,
            ],
            [
                'started_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'redirect' => route('ecourse.player', ['course' => $course->slug, 'module' => $firstModule->slug]),
        ]);
    }

    public function updateProgress(CourseModule $module, Request $request)
    {
        $request->validate([
            'progress_percentage' => 'required|numeric|min:0|max:100'
        ]);

        $user = $this->currentUser();
        
        $userCourse = $user->ecourses()
            ->where('course_id', $module->course_id)
            ->firstOrFail();

        // Update atau create module progress tracking
        $moduleProgress = UserCourseModule::updateOrCreate(
            [
                'user_course_id' => $userCourse->id,
                'course_module_id' => $module->id
            ],
            [
                'started_at' => $user->ecourses()->where('course_id', $module->course_id)->first()->started_at ?? now(),
            ]
        );

        $newProgress = max($moduleProgress->progress ?? 0, $request->progress_percentage);
        
        $moduleProgress->update([
            'progress' => $newProgress,
            'is_completed' => $newProgress >= 95 // Tandai selesai jika >= 95% (opsional, tapi bagus untuk UX)
        ]);

        // Selalu update progres keseluruhan course
        $this->updateCourseProgress($userCourse);

        return response()->json(['success' => true]);
    }

    public function completeModule(CourseModule $module, Request $request)
    {
        $user = $this->currentUser();
        
        $userCourse = $user->ecourses()
            ->where('course_id', $module->course_id)
            ->firstOrFail();

        // Update ke modul yang selesai
        UserCourseModule::updateOrCreate(
            [
                'user_course_id' => $userCourse->id,
                'course_module_id' => $module->id
            ],
            [
                'progress' => 100,
                'is_completed' => true,
                'completed_at' => now(),
            ]
        );

        // Update overall course progress
        $this->updateCourseProgress($userCourse);

        // Cari modul berikutnya
        $nextModule = $module->course->modules()
            ->where('order_num', '>', $module->order_num)
            ->orderBy('order_num')
            ->first();

        if ($nextModule) {
            // Update current_module_id ke modul berikutnya
            $userCourse->update(['current_module_id' => $nextModule->id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Modul selesai! Lanjut ke modul berikutnya.',
                'next_module_url' => route('ecourse.player', ['course' => $userCourse->course->slug, 'module' => $nextModule->slug])
            ]);
        } else {
            // Course selesai
            $userCourse->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Selamat! Kamu telah menyelesaikan course ini.',
                'is_completed' => true
            ]);
        }
    }

    public function addComment(Request $request, CourseModule $module)
    {
        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        // Fitur diskusi (Placeholder)
        return response()->json([
            'success' => true,
            'message' => 'Pertanyaan Anda telah dikirim.'
        ]);
    }

    /**
     * Try to create a course certificate when a user finishes a course.
     */
    private function maybeCreateCourseCertificate(UserCourse $userCourse)
    {
        // Skip if certificate already exists for this user+course
        $exists = \App\Models\Ecourse\CourseCertificate::where('user_id', $userCourse->user_id)
            ->where('course_id', $userCourse->course_id)
            ->exists();

        if ($exists) return;

        $acq = $userCourse->acquisition_type;
        $user = $userCourse->user;

        // Direct payment cases: create immediately
        if (in_array($acq, ['idr','idr_purchase','direct','free'])) {
            $this->createCourseCertificate($userCourse);
            return;
        }

        // Subscription: check quota across user's active subscriptions (global)
        if ($acq === 'subscription') {
            $activeSubs = $user->subscriptions()
                ->where('status', 'active')
                ->where(function($q) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                })
                ->orderByDesc('end_date')
                ->get();

            // Find first subscription with available quota
            $target = $activeSubs->first(function ($s) {
                return (($s->certificate_quota ?? 0) - ($s->certificate_used ?? 0)) > 0;
            });

            if ($target) {
                $target->certificate_used = ($target->certificate_used ?? 0) + 1;
                $target->save();
                $this->createCourseCertificate($userCourse);
            } else {
                // no quota available: do nothing (could notify user)
            }
        }
    }

    private function createCourseCertificate(UserCourse $userCourse)
    {
        $user = $userCourse->user;
        $course = $userCourse->course;

        // Generate certificate code in format: UNL-ECOYYMM-KODEUNIK
        $yy = now()->format('y');
        $mm = now()->format('m');
        $unik = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)); // 6 hex chars
        $certNumber = "UNL-ECO{$yy}{$mm}-{$unik}";

        // Create course certificate without template dependency (CertificateTemplate removed)
        \App\Models\Ecourse\CourseCertificate::create([
            'certificate_number' => $certNumber,
            'user_id' => $user->id,
            'course_id' => $course->id,
            'course_title' => $course->title,
            'user_name' => $user->name,
            'score' => round($userCourse->progress ?? 100),
            'issued_at' => now(),
        ]);
    }

    private function updateCourseProgress(UserCourse $userCourse)
    {
        $wasCompleted = $userCourse->status === 'completed';

        $totalModules = $userCourse->course->modules()->count();
        $completedModules = $userCourse->userCourseModules()
            ->where('is_completed', true)
            ->count();

        // Progress berdasarkan modul yang sudah selesai
        $progress = $totalModules > 0 ? ($completedModules / $totalModules) * 100 : 0;

        $userCourse->update([
            'completed_modules' => $completedModules,
            'total_modules' => $totalModules,
            'progress' => round($progress, 2),
            'status' => $progress >= 100 ? 'completed' : 'in_progress'
        ]);

        // Jika baru saja selesai, coba buat sertifikat sesuai rule
        if (!$wasCompleted && $userCourse->status === 'completed') {
            $this->maybeCreateCourseCertificate($userCourse);
        }
    }

    private function mapCourseSummary(Course $course, ?UserCourse $userCourse = null): array
    {
        return [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'short_description' => $course->short_description,
            'thumbnail_url' => $course->thumbnail_url,
            'level' => $course->level,
            'formatted_duration' => $course->formatted_duration,
            'is_free' => $course->is_free,
            'price' => $course->price,
            'is_owned' => (bool) $userCourse,
            'speaker' => $course->speaker ? [
                'name' => $course->speaker->speaker_name,
                'job' => $course->speaker->position ?? 'Pembicara',
            ] : null,
            'categories' => $course->categories?->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])->values(),
        ];
    }

    private function mapModule(CourseModule $module, $userModule = null): array
    {
        return [
            'id' => $module->id,
            'slug' => $module->slug,
            'title' => $module->title,
            'order_num' => $module->order_num,
            'description' => $module->description,
            'module_type' => $module->module_type ?? 'video',
            'video_url' => $module->video_url,
            'video_path' => $module->video_path,
            'pdf_url' => $module->pdf_url,
            'pdf_path' => $module->pdf_path,
            'formatted_duration' => $module->formatted_duration,
            'objectives' => $module->objectives ?? [],
            'is_completed' => (bool) ($userModule?->is_completed),
            'progress' => $userModule?->progress ?? 0,
            'materials' => $module->materials?->map(fn ($material) => [
                'id' => $material->id,
                'title' => $material->title,
                'type' => $material->type,
                'file_url' => $material->file_url,
                'file_path' => $material->file_path,
                'file_size' => $material->file_size,
                'icon' => $material->icon,
                'type_color' => $material->type_color,
            ])->values(),
        ];
    }

    /**
     * Download material file with permission validation
     * 
     * @param CourseModule $module
     * @param ModuleMaterial $material
     * @return StreamedResponse|mixed
     */
    public function downloadMaterial(CourseModule $module, ModuleMaterial $material)
    {
        $user = $this->currentUser();
        
        // Check user is authenticated
        if (!$user) {
            abort(401, 'Unauthorized. Please login first.');
        }

        // Verify material belongs to the module
        if ($material->course_module_id !== $module->id) {
            abort(404, 'Material not found.');
        }

        // Get the course for this module
        $course = $module->course;
        if (!$course) {
            abort(404, 'Course not found.');
        }

        // Check if user has access to this course
        // User must have either:
        // 1. Active subscription (monthly subscription with valid end_date)
        // 2. Direct purchase of this course (acquisition_type = 'direct', 'idr', or 'idr_purchase')
        $hasActiveSubscription = $user->subscriptions()
            ->where('status', 'active')
            ->where('subscription_type', 'monthly')
            ->where(function($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->exists();

        $userCourse = $user->ecourses()
            ->where('course_id', $course->id)
            ->first();

        $hasDirectAccess = $userCourse && in_array($userCourse->acquisition_type, ['direct', 'idr', 'idr_purchase']);

        if (!$hasActiveSubscription && !$hasDirectAccess) {
            abort(403, 'You do not have permission to download this material. Please purchase this course or subscribe.');
        }

        // Get file path
        $filePath = $material->file_path;

        // Handle external URLs
        if (str_starts_with($filePath, 'http')) {
            // For external URLs, redirect to the URL
            return redirect($filePath);
        }

        // Handle storage paths (stored under storage/app/public)
        $storagePath = ltrim($filePath, '/');
        $disk = Storage::disk('public');

        // Security: Verify file exists and prevent directory traversal
        if (str_contains($filePath, '..') || ! $disk->exists($storagePath)) {
            abort(404, 'File not found.');
        }

        // Get the full file path
        $fullPath = $disk->path($storagePath);

        // Additional security check
        if (!file_exists($fullPath) || !is_file($fullPath)) {
            abort(404, 'File not found.');
        }

        // Determine filename for download
        $filename = basename($fullPath);
        if (!$filename) {
            $filename = $material->title . '.' . pathinfo($filePath, PATHINFO_EXTENSION);
        }

        // Return file as download
        return response()->download(
            $fullPath,
            $filename,
            [
                'Content-Type' => mime_content_type($fullPath),
                'Cache-Control' => 'public, max-age=3600',
            ]
        );
    }

    protected function currentUser(): ?User
    {
        return Auth::user();
    }
}
