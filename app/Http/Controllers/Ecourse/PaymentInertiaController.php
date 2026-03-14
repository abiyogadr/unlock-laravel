<?php

namespace App\Http\Controllers\Ecourse;

use App\Http\Controllers\Controller;
use App\Models\Ecourse\Course;
use App\Models\Ecourse\CoursePackage;
use App\Models\Ecourse\CourseTransaction;
use App\Models\Ecourse\UserCourse;
use App\Models\Ecourse\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ApplyVoucherRequest;
use App\Http\Requests\PaymentPageRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Midtrans\Config;
use Midtrans\Snap;
use App\Services\VoucherService;

class PaymentInertiaController extends Controller
{
    protected VoucherService $vouchers;

    public function __construct(VoucherService $vouchers)
    {
        // Initialize Midtrans Config
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        $this->vouchers = $vouchers;
    }

    /**
     * Checkout process for course or package purchase
     */
    public function checkout(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        Log::info('Payment checkout initiated', [
            'user_id' => $user->id,
            'type' => $request->input('type'),
            'course_id' => $request->input('course_id'),
            'package_id' => $request->input('package_id'),
        ]);
        
        $validated = $request->validate([
            'type' => 'required|in:course,package',
            'course_id' => 'nullable|exists:courses,id',
            'package_id' => 'required_if:type,package|exists:course_packages,id',
            'payment_method' => 'nullable|in:ustar,idr',
            'voucher_code' => 'nullable|string|max:100',
        ]);

        if ($validated['type'] === 'course' && !($validated['course_id'] ?? null)) {
            Log::warning('Course type selected but no course_id provided', ['user_id' => $user->id]);
            return response()->json(['success' => false, 'message' => 'Kursus tidak valid.'], 400);
        }

        try {
            DB::beginTransaction();

            // resolve payment context from server-side data
            $ctxQuery = ['type' => $validated['type']];
            if (!empty($validated['course_id'])) {
                $course = Course::findOrFail($validated['course_id']);
                $ctxQuery['course_slug'] = $course->slug;
            }
            if (!empty($validated['package_id'])) {
                $ctxQuery['package_id'] = $validated['package_id'];
            }
            $paymentContext = $this->vouchers->resolvePaymentContext($ctxQuery);

            // perform voucher validation if code provided
            $summary = null;
            if (!empty($validated['voucher_code'])) {
                $summary = $this->vouchers->applyToPaymentContext(
                    $validated['voucher_code'],
                    $user,
                    $paymentContext
                );
            }

            if ($validated['type'] === 'course') {
                $result = $this->processCourseCheckout($user, $validated, $summary);
            } else {
                $result = $this->processPackageCheckout($user, $validated, $summary);
            }

            if ($summary && isset($summary['voucher'])) {
                $this->vouchers->markAsUsed(
                    \App\Models\Voucher::find($summary['voucher']['id']),
                    $user,
                    $result['data']['order'],
                    array_merge($summary, ['context' => $paymentContext])
                );
                $result['data']['summary'] = $summary;
            }

            DB::commit();

            Log::info('Payment checkout completed successfully', [
                'user_id' => $user->id,
                'type' => $validated['type'],
                'order_id' => $result['data']['order_id'] ?? null,
                'transaction_id' => $result['data']['transaction_id'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data'],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'type' => $validated['type'] ?? 'unknown',
                'course_id' => $validated['course_id'] ?? null,
                'package_id' => $validated['package_id'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Show payment page or status page
     */
    public function show(\App\Http\Requests\PaymentPageRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // validated already by PaymentPageRequest
        $type = $request->query('type');
        $courseSlug = $request->query('course_slug');

        // resolve payment context early, will be reused during voucher operations
        // (skip when showing payment status page, since it may not contain type/course_slug)
        $context = null;
        $initialSummary = [
            'subtotal' => 0,
            'discount' => 0,
            'grand_total' => 0,
            'voucher' => null,
        ];

        if ($type) {
            $context = $this->vouchers->resolvePaymentContext($request->query());
            $initialSummary = [
                'subtotal' => $context['subtotal'],
                'discount' => 0,
                'grand_total' => $context['subtotal'],
                'voucher' => null,
            ];
        }

        // base $data with context so we can merge later
        $data = [
            'payment_context' => $context,
            'summary' => $initialSummary,
        ];

        // If redirected from payment gateway (finish callback) or USTAR payment
        $status = $request->query('status');
        $orderId = $request->query('order_id');
        if ($status && $orderId) {
            // Normalize external status to frontend-friendly values
            $rawStatus = strtolower($status);
            $map = [
                'paid' => 'success',
                'success' => 'success',
                'canceled' => 'failed',
                'cancel' => 'failed',
                'expired' => 'failed',
                'failed' => 'failed',
                'pending' => 'pending',
            ];
            $status = $map[$rawStatus] ?? $rawStatus;

            $transaction = null;
            
            // Handle USTAR transactions (order_id starts with USTAR-)
            if (str_starts_with($orderId, 'USTAR-')) {
                $transactionId = str_replace('USTAR-', '', $orderId);
                $transaction = CourseTransaction::find($transactionId);
            } else {
                // Handle regular Midtrans transactions
                $transaction = CourseTransaction::where('external_reference', $orderId)->first();
            }

            $data = [
                'status' => $status,
                'order_id' => $orderId,
                'transaction' => $transaction ? [
                    'id' => $transaction->id,
                    'status' => $transaction->status,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'meta' => $transaction->meta,
                ] : null,
                'redirect_url' => null,
                'course_name' => $request->query('course_name'),
                'purchase_time' => $request->query('purchase_time'),
            ];

            // If the transaction has a linked course (either direct purchase or bought from a course page)
            $targetCourseId = $transaction ? ($transaction->course_id ?: ($transaction->meta['course_id'] ?? null)) : null;

            if ($transaction && $targetCourseId) {
                $course = Course::find($targetCourseId);
                if ($course) {
                    // Update transaction meta with course_slug and first_module_slug if not present
                    $meta = $transaction->meta ?? [];
                    $updatedMeta = false;
                    if (!isset($meta['course_slug']) || $meta['course_slug'] !== $course->slug) {
                        $meta['course_slug'] = $course->slug;
                        $updatedMeta = true;
                    }

                    // Redirect to course page instead of module player
                    $data['redirect_url'] = route('ecourse.course.show', $course->slug);
                    $data['course_name'] = $data['course_name'] ?: ($transaction->meta['type'] === 'course' ? $course->title : null);
                    $data['purchase_time'] = $data['purchase_time'] ?: $transaction->created_at->format('d M Y, H:i');

                    // If course has modules, set first module player URL so frontend can link directly
                    $firstModule = $course->modules()->orderBy('order_num')->first();
                    if ($firstModule) {
                        $data['redirect_module_url'] = route('ecourse.player', [$course->slug, $firstModule->slug]);
                        // include first module slug for frontend fallback
                        $data['first_module_slug'] = $firstModule->slug;
                        if (!isset($meta['first_module_slug']) || $meta['first_module_slug'] !== $firstModule->slug) {
                            $meta['first_module_slug'] = $firstModule->slug;
                            $updatedMeta = true;
                        }
                    }

                    // Update meta if changed
                    if ($updatedMeta) {
                        $transaction->update(['meta' => $meta]);
                    }
                } else {
                    // Fallback: try to use slug from transaction meta if course not found
                    $metaSlug = $transaction->meta['course_slug'] ?? null;
                    if ($metaSlug) {
                        $data['redirect_url'] = route('ecourse.course.show', $metaSlug);
                        // Try to find first module by slug fallback (best-effort)
                        $firstModule = Course::where('slug', $metaSlug)->first()?->modules()->orderBy('order_num')->first();
                        if ($firstModule) {
                            $data['redirect_module_url'] = route('ecourse.player', [$metaSlug, $firstModule->slug]);
                            $data['first_module_slug'] = $firstModule->slug;
                        }
                    }
                }
            }

            return Inertia::render('PaymentStatus', $data);
        }

        // preserve existing data (payment_context & summary)
        // $data already contains them

        // Get user's active subscription and remaining USTAR
        $activeSubscription = $user->subscriptions()
            ->where('status', 'active')
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            })
            ->first();

        $remainingUstar = $activeSubscription 
            ? ($activeSubscription->ustar_total - $activeSubscription->ustar_used) 
            : 0;

        $data['user_ustar'] = [
            'total' => $activeSubscription->ustar_total ?? 0,
            'used' => $activeSubscription->ustar_used ?? 0,
            'remaining' => $remainingUstar,
            'has_subscription' => !!$activeSubscription,
        ];

        if ($type === 'course' && $courseSlug) {
            $course = Course::where('slug', $courseSlug)->firstOrFail();
            
            $data['type'] = 'course';
            $data['course'] = [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'short_description' => $course->short_description,
                'price' => $course->price,
                'credit_cost' => $course->credit_cost ?? 0,
                'thumbnail_url' => $course->thumbnail_url,
            ];

            // Determine default payment method (Commented out USTAR logic)
            // $canUseUstar = $course->credit_cost > 0 && $remainingUstar >= $course->credit_cost;
            // $data['default_payment_method'] = $canUseUstar ? 'ustar' : 'idr';
            $data['default_payment_method'] = 'idr';

            // Get recommended packages if user doesn't have USTAR (Commented out USTAR logic)
            /* if (!$activeSubscription || $remainingUstar < $course->credit_cost) {
                $data['recommended_packages'] = CoursePackage::where('is_active', true)
                    ->where('ustar_credits', '>=', $course->credit_cost)
                    ->orderBy('price', 'asc')
                    ->limit(3)
                    ->get()
                    ->map(fn($pkg) => [
                        'id' => $pkg->id,
                        'name' => $pkg->name,
                        'price' => $pkg->price,
                        'discount_price' => $pkg->discount_price,
                        'ustar_credits' => $pkg->ustar_credits,
                        'duration_days' => $pkg->duration_days,
                    ]);
            } */

            // Also provide subscription packages so the frontend can list them under the course
            $data['default_package_id'] = $request->query('package_id');
            $data['packages'] = CoursePackage::where('is_active', true)
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
        } else {
            $data['type'] = 'package';
            $data['packages'] = CoursePackage::where('is_active', true)
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
        }

        return Inertia::render('Payment', $data);
    }

    /**
     * AJAX endpoint invoked by frontend when user applies a voucher code.
     * Context is resolved server-side from identifiers to prevent subtotal manipulation.
     */
    public function applyVoucher(ApplyVoucherRequest $request)
    {
        $user = $request->user();
        try {
            // resolve context from validated identifiers (server-side pricing)
            $ctx = $this->vouchers->resolvePaymentContext($request->validated());
            $summary = $this->vouchers->applyToPaymentContext($request->input('code'), $user, $ctx);
            return response()->json(['data' => $summary]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // voucher not found or context model missing
            return response()->json(['message' => 'Kode voucher tidak ditemukan.'], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Simple helper to clear voucher from calculation. Frontend also clears its own state.
     */
    public function removeVoucher(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:course,package',
            'course_slug' => 'nullable|required_if:type,course|string|exists:courses,slug',
            'package_id' => 'nullable|required_if:type,package|integer|exists:course_packages,id',
        ]);

        try {
            $ctx = $this->vouchers->resolvePaymentContext($validated);
            $subtotal = $ctx['subtotal'];
        } catch (\Exception $e) {
            $subtotal = 0;
        }

        return response()->json(['data' => [
            'subtotal' => $subtotal,
            'discount' => 0,
            'grand_total' => $subtotal,
            'voucher' => null,
        ]]);
    }

    /**
     * Process course purchase
     */
    private function processCourseCheckout($user, $validated, $summary = null)
    {
        $course = Course::findOrFail($validated['course_id']);
        
        // Check if already purchased
        $existingCourse = UserCourse::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('acquisition_type', 'idr')
            ->first();

        if ($existingCourse) {
            throw new \Exception('Anda sudah memiliki akses ke kursus ini.');
        }

        // Determine payment method
        $paymentMethod = $validated['payment_method'] ?? $this->determinePaymentMethod($user, $course->price, $course->credit_cost);

        if ($paymentMethod === 'ustar') {
            return $this->processUstarPayment($user, $course);
        } else {
            return $this->processIDRPayment($user, $course, 'course', null, $summary);
        }
    }

    /**
     * Process package purchase
     */
    private function processPackageCheckout($user, $validated, $summary = null)
    {
        $package = CoursePackage::where('is_active', true)
            ->findOrFail($validated['package_id']);

        // Check if user has active subscription
        $activeSubscription = $user->subscriptions()
            ->where('status', 'active')
            ->where('subscription_type', 'monthly')
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            })
            ->first();

        if ($activeSubscription) {
            Log::info('User has active subscription but proceeding with new package purchase for top-up', ['user_id' => $user->id, 'package_id' => $package->id]);
        }

        return $this->processIDRPayment($user, $package, 'package', $validated['course_id'] ?? null, $summary);
    }

    /**
     * Process payment using USTAR credits
     */
    private function processUstarPayment($user, $course)
    {
        // Get active subscription with sufficient credits
        $subscription = $user->subscriptions()
            ->where('status', 'active')
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            })
            ->whereRaw('(ustar_total - ustar_used) >= ?', [$course->credit_cost])
            ->first();

        if (!$subscription) {
            throw new \Exception('Kredit USTAR Anda tidak mencukupi. Silakan beli paket terlebih dahulu.');
        }

        // Deduct USTAR credits
        $subscription->increment('ustar_used', $course->credit_cost);

        // Create transaction record
        $transaction = CourseTransaction::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'user_subscription_id' => $subscription->id,
            'amount' => $course->credit_cost,
            'currency' => 'USTAR',
            'status' => 'paid',
            'payment_method' => 'ustar_credits',
            'meta' => [
                'previous_ustar_used' => $subscription->ustar_used - $course->credit_cost,
                'new_ustar_used' => $subscription->ustar_used,
            ],
        ]);

        // Grant access to course
        $userCourse = UserCourse::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'subscription_id' => $subscription->id,
            'enrolled_at' => now(),
            'total_modules' => $course->modules()->count(),
            'completed_modules' => 0,
            'progress' => 0,
            'status' => 'enrolled',
            'acquisition_type' => 'ustar',
        ]);

        return [
            'message' => 'Pembayaran berhasil dengan USTAR!',
            'data' => [
                'payment_method' => 'ustar',
                'redirect_url' => route('ecourse.course.show', $course->slug),
                'transaction_id' => $transaction->id,
                'remaining_ustar' => $subscription->ustar_total - $subscription->ustar_used,
                'status' => 'success',
                'order_id' => 'USTAR-' . $transaction->id,
                'course_name' => $course->title,
                'purchase_time' => $transaction->created_at->format('d M Y, H:i'),
                'order' => $transaction,
            ],
        ];
    }

    /**
     * Update transaction status (used to mark failed/canceled by frontend when appropriate)
     */
    public function updateStatus(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'order_id' => 'required|string',
            'status' => 'required|string',
        ]);

        $orderId = $validated['order_id'];
        $status = strtolower($validated['status']);

        // Normalize some common frontend values to match DB enum
        $normalize = [
            'cancel' => 'canceled',
            'cancelled' => 'canceled',
            'success' => 'paid',
        ];
        if (isset($normalize[$status])) {
            $status = $normalize[$status];
        }

        // Allowed statuses (must match the enum in migration)
        $allowed = ['pending', 'paid', 'failed', 'expired', 'canceled'];
        if (!in_array($status, $allowed)) {
            return response()->json(['success' => false, 'message' => 'Status tidak valid.'], 422);
        }

        $transaction = CourseTransaction::where('external_reference', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (!$transaction) {
            return response()->json(['success' => false, 'message' => 'Transaksi tidak ditemukan.'], 404);
        }

        try {
            $transaction->update([
                'status' => $status,
                'meta' => array_merge($transaction->meta ?? [], [
                    'updated_by_frontend_at' => now()->toDateTimeString(),
                    'frontend_status' => $status,
                ]),
            ]);

            return response()->json(['success' => true, 'message' => 'Status transaksi diperbarui: ' . $status]);
        } catch (\Exception $e) {
            Log::error('Update transaction status error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui status transaksi'], 500);
        }
    }

    /**
     * Get transaction status (used by frontend polling)
     */
    public function getStatus(Request $request, $orderId)
    {
        $user = Auth::user();
        $transaction = CourseTransaction::where('external_reference', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (!$transaction) {
            return response()->json(['success' => false, 'message' => 'Transaksi tidak ditemukan.'], 404);
        }

        // Map internal status to frontend-friendly status
        $internal = $transaction->status;
        $map = [
            'paid' => 'success',
            'canceled' => 'failed',
            'cancel' => 'failed',
            'expired' => 'failed',
            'failed' => 'failed',
            'pending' => 'pending',
        ];

        $frontendStatus = $map[strtolower($internal)] ?? $internal;

        return response()->json([
            'success' => true,
            'status' => $frontendStatus,
            'meta' => $transaction->meta ?? [],
        ]);
    }

    /**
     * Process payment using IDR (Midtrans)
     */
    private function processIDRPayment($user, $item, $type, $targetCourseId = null, $summary = null)
    {
        $orderId = 'ECO-' . strtoupper($type) . '-' . time() . '-' . $user->id;
        
        if ($type === 'course') {
            $originalAmount = $item->price;
            $itemName = $item->title;
            $courseId = $item->id;
            $packageId = null;
            $courseSlug = $item->slug ?? null;
        } else {
            $originalAmount = $item->discount_price ?? $item->price;
            $itemName = $item->name;
            $courseId = $targetCourseId; // use target course id if provided (buying package from course page)
            $packageId = $item->id;
            
            // fetch course slug if course id is provided
            $courseSlug = null;
            if ($courseId) {
                $courseSlug = Course::where('id', $courseId)->value('slug');
            }
        }

        // Apply voucher discount if present
        $amount = $originalAmount;
        if ($summary && $summary['discount'] > 0) {
            $amount = max($originalAmount - $summary['discount'], 0);
        }

        // If amount is zero after discount, grant access immediately without Midtrans
        if ($amount <= 0) {
            $transaction = CourseTransaction::create([
                'user_id' => $user->id,
                'package_id' => $packageId,
                'course_id' => $courseId,
                'amount' => 0,
                'currency' => 'IDR',
                'status' => 'paid',
                'payment_method' => 'voucher',
                'external_reference' => $orderId,
                'meta' => [
                    'type' => $type,
                    'item_name' => $itemName,
                    'course_id' => $courseId,
                    'course_slug' => $courseSlug ?? null,
                    'package_id' => $packageId,
                    'original_amount' => $originalAmount,
                    'voucher_code' => $summary['voucher']['code'] ?? null,
                    'voucher_discount' => $summary['discount'] ?? 0,
                ],
            ]);

            if ($type === 'course' && $courseId) {
                UserCourse::firstOrCreate(
                    ['user_id' => $user->id, 'course_id' => $courseId],
                    [
                        'enrolled_at' => now(),
                        'total_modules' => $item->modules()->count(),
                        'completed_modules' => 0,
                        'progress' => 0,
                        'status' => 'enrolled',
                        'acquisition_type' => 'idr',
                    ]
                );
            }

            return [
                'message' => 'Pembayaran berhasil dengan voucher!',
                'data' => [
                    'payment_method' => 'voucher',
                    'redirect_url' => $courseSlug ? route('ecourse.course.show', $courseSlug) : null,
                    'transaction_id' => $transaction->id,
                    'status' => 'success',
                    'order_id' => $orderId,
                    'course_name' => $itemName,
                    'purchase_time' => $transaction->created_at->format('d M Y, H:i'),
                    'order' => $transaction,
                ],
            ];
        }

        // Check for existing pending transaction to reuse
        $existing = CourseTransaction::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('payment_method', 'midtrans')
            ->where(function($q) use ($type, $courseId, $packageId) {
                if ($type === 'course') {
                    $q->where('course_id', $courseId);
                } else {
                    $q->where('package_id', $packageId);
                }
            })
            ->first();

        if ($existing) {
            $meta = $existing->meta ?? [];
            // If existing has snap_token, reuse it
            if (!empty($meta['snap_token'])) {
                return [
                    'message' => 'Menggunakan transaksi pending yang sudah ada.',
                    'data' => [
                        'payment_method' => 'idr',
                        'snap_token' => $meta['snap_token'],
                        'transaction_id' => $existing->id,
                        'order_id' => $existing->external_reference,
                        'order' => $existing,
                    ],
                ];
            }
            // otherwise, we'll reuse the transaction record and generate a token below
            $orderId = $existing->external_reference;
            $transaction = $existing;
        } else {
            // Create transaction record
            $transaction = CourseTransaction::create([
                'user_id' => $user->id,
                'package_id' => $packageId,
                'course_id' => $courseId,
                'amount' => $amount,
                'currency' => 'IDR',
                'status' => 'pending',
                'payment_method' => 'midtrans',
                'external_reference' => $orderId,
                'meta' => array_merge([
                    'type' => $type,
                    'item_name' => $itemName,
                    'course_id' => $courseId,
                    'course_slug' => $courseSlug ?? null,
                    'package_id' => $packageId,
                    'original_amount' => $originalAmount,
                ], $type === 'package' ? [
                    'plan_duration' => $item->plan_duration ?? null,
                    'duration_days' => $item->duration_days ?? null,
                ] : [], $summary ? [
                    'voucher_code' => $summary['voucher']['code'] ?? null,
                    'voucher_discount' => $summary['discount'] ?? 0,
                ] : []),
            ]);
        }

        // Prepare Midtrans transaction parameters
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'item_details' => [
                [
                    'id' => $type === 'course' ? "course-{$courseId}" : "package-{$packageId}",
                    'price' => (int) $amount,
                    'quantity' => 1,
                    'name' => $itemName,
                ],
            ],
            'callbacks' => [
                'finish' => route('ecourse.payment') . '?status=success&order_id=' . $orderId,
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            
            $transaction->update([
                'meta' => array_merge($transaction->meta ?? [], [
                    'snap_token' => $snapToken,
                ]),
            ]);

            return [
                'message' => 'Transaksi berhasil dibuat.',
                'data' => [
                    'payment_method' => 'idr',
                    'snap_token' => $snapToken,
                    'transaction_id' => $transaction->id,
                    'order_id' => $orderId,
                    'order' => $transaction,
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Midtrans Snap Token Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            // Include detailed message when app is in debug mode to aid testing
            $message = config('app.debug') ? ('Gagal membuat pembayaran. ' . $e->getMessage()) : 'Gagal membuat pembayaran. Silakan coba lagi.';
            throw new \Exception($message);
        }
    }



    /**
     * Determine best payment method based on availability
     */
    private function determinePaymentMethod($user, $idrPrice, $ustarCost)
    {
        if (!$ustarCost || $ustarCost <= 0) {
            return 'idr';
        }

        $subscription = $user->subscriptions()
            ->where('status', 'active')
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            })
            ->first();

        if (!$subscription) {
            return 'idr';
        }

        $remainingUstar = $subscription->ustar_total - $subscription->ustar_used;

        return $remainingUstar >= $ustarCost ? 'ustar' : 'idr';
    }
}
