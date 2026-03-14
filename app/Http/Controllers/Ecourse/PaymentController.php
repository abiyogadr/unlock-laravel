<?php

namespace App\Http\Controllers\Ecourse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Midtrans\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Ecourse\CourseTransaction;
use App\Models\Ecourse\Course;
use App\Models\Ecourse\CoursePackage;
use App\Models\Ecourse\UserCourse;
use App\Models\Ecourse\UserSubscription;

/**
 * PaymentController - Ecourse Payment Handler
 * 
 * Note: Checkout logic is handled by PaymentInertiaController
 * Callback handling is now centralized in MidtransController
 * which routes to appropriate handler based on order_id pattern (ECO- prefix)
 */
class PaymentController extends Controller
{
    // This controller is kept for reference and future extensions
    // All payment logic is now in PaymentInertiaController


    /**
     * Midtrans payment callback handler for ecourse
     */
    public function callback(Request $request)
    {
        try {
            $notification = new Notification();

            $orderId = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status ?? 'accept';

            Log::info('Ecourse Midtrans Callback', [
                'order_id' => $orderId,
                'status' => $transactionStatus,
                'fraud' => $fraudStatus,
            ]);

            // Find ecourse transaction
            $transaction = CourseTransaction::where('external_reference', $orderId)->first();

            if (!$transaction) {
                Log::warning('Ecourse transaction not found for order: ' . $orderId);
                return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }

            DB::beginTransaction();

            // Handle different payment statuses
            if (in_array($transactionStatus, ['capture', 'settlement'])) {
                if ($fraudStatus === 'accept') {
                    $this->handleSuccessfulPayment($transaction);
                } else {
                    $transaction->update(['status' => 'failed']);
                }
            } elseif ($transactionStatus === 'pending') {
                $transaction->update(['status' => 'pending']);
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $transaction->update(['status' => 'failed']);
            }

            DB::commit();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ecourse Midtrans Callback Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle successful payment - grant access and create subscriptions
     */
    private function handleSuccessfulPayment($transaction)
    {
        $transaction->update(['status' => 'paid']);
        $user = $transaction->user; // Eloquent relation (User model)

        $meta = $transaction->meta ?? [];
        $type = $meta['type'] ?? null;

        if ($type === 'course' && $transaction->course_id) {
            // Grant course access
            $course = Course::find($transaction->course_id);
            
            if ($course) {
                // Check if user already has access
                $existingAccess = UserCourse::where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->first();

                        if (!$existingAccess) {
                    UserCourse::create([
                        'user_id' => $user->id,
                        'course_id' => $course->id,
                        'enrolled_at' => now(),
                        'total_modules' => $course->modules()->count(),
                        'completed_modules' => 0,
                        'progress' => 0,
                        'status' => 'enrolled',
                        'acquisition_type' => 'idr',
                    ]);
                    
                    Log::info('Course access granted after Midtrans payment', [
                        'user_id' => $user->id,
                        'course_id' => $course->id,
                        'transaction_id' => $transaction->id,
                    ]);
                } else {
                    // If user already has access but acquisition_type is not 'idr', update it to 'idr'
                    if (($existingAccess->acquisition_type ?? '') !== 'idr') {
                        $previousAcq = $existingAccess->acquisition_type ?? null;
                        $existingAccess->update(['acquisition_type' => 'idr']);
                        Log::info('Updated existing access acquisition_type to idr after direct purchase', [
                            'user_id' => $user->id,
                            'course_id' => $course->id,
                            'previous_acquisition' => $previousAcq,
                            'transaction_id' => $transaction->id,
                        ]);
                    }
                }
            }

        } elseif ($type === 'package' && $transaction->package_id) {
            // Create subscription for package purchase
            $package = CoursePackage::find($transaction->package_id);
            
            if ($package) {
                $endDate = $package->duration_days 
                    ? now()->addDays($package->duration_days) 
                    : null;

                UserSubscription::create([
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'start_date' => now(),
                    'end_date' => $endDate,
                    'ustar_total' => $package->ustar_credits,
                    'ustar_used' => 0,
                    'status' => 'active',
                ]);
                
                Log::info('Subscription created after Midtrans payment', [
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'ustar_credits' => $package->ustar_credits,
                    'transaction_id' => $transaction->id,
                ]);
            }
        }
    }
}
