<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Notification;
use App\Models\Registration;
use App\Models\Payment;
use App\Models\Ecourse\CourseTransaction;
use Midtrans\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\RegistrationSuccessMail;

class MidtransController extends Controller
{
    public function callback(Request $request)
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized  = config('midtrans.is_sanitized', true);
        Config::$is3ds        = config('midtrans.is_3ds', true);
        
        $notif = new Notification();
        $orderId = $notif->order_id;

        Log::info('Midtrans callback received', ['order_id' => $orderId]);

        // Check if this is an ecourse payment (order_id starts with ECO-)
        if (strpos($orderId, 'ECO-') === 0) {
            return $this->handleEcourseCallback($notif, $request);
        }

        // Otherwise handle as registration callback
        return $this->handleRegistrationCallback($notif, $request);
    }

    /**
     * Handle ecourse payment callback
     */
    private function handleEcourseCallback($notif, $request)
    {
        try {
            $orderId = $notif->order_id;
            $transactionStatus = $notif->transaction_status;
            $fraudStatus = $notif->fraud_status ?? 'accept';

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

            // Handle different payment statuses
            if (in_array($transactionStatus, ['capture', 'settlement'])) {
                if ($fraudStatus === 'accept') {
                    $this->handleSuccessfulEcoursePayment($transaction);
                } else {
                    $transaction->update(['status' => 'failed']);
                }
            } elseif ($transactionStatus === 'pending') {
                $transaction->update(['status' => 'pending']);
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $transaction->update(['status' => 'failed']);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Ecourse Midtrans Callback Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle registration payment callback (original logic)
     */
    private function handleRegistrationCallback($notif, $request)
    {
        $orderId = $notif->order_id;
        $transactionStatus = $notif->transaction_status;
        $fraudStatus = $notif->fraud_status ?? null;

        Log::info('Registration Midtrans Callback', [
            'order_id' => $orderId,
            'status' => $transactionStatus,
        ]);
        
        $registration = Registration::where('registration_code', $orderId)->first();
        $previousStatus = $registration ? $registration->payment_status : null;

        if (!$registration) {
            Log::warning('Registration not found for order: ' . $orderId);
            return response()->json(['message' => 'Registration not found'], 404);
        }
        
        $registration->update(['payment' => $notif->payment_type]);

        if (
            ($transactionStatus === 'capture' && $fraudStatus === 'accept') ||
            $transactionStatus === 'settlement'
        ) {
            $paymentStatus = 'success';
            $paidAt = $notif->settlement_time ?? $notif->transaction_time ?? now();

            if ($registration->uploadProofs()->exists()) {
                $registrationStatus = 'pending';
            } else {
                $registrationStatus = 'verified'; 
            }
        } elseif ($transactionStatus === 'pending') {
            $paymentStatus = 'pending';
            $registrationStatus = 'pending';
            $paidAt = null;
        } else {
            $paymentStatus = 'failed';
            $registrationStatus = 'cancelled';
            $paidAt = null;
        }

        // SIMPAN KE TABLE PAYMENTS
        Payment::updateOrCreate(
            [
                'registration_id' => $registration->id,
                'transaction_id'  => $notif->transaction_id ?? null,
            ],
            [
                'registration_code'   => $registration->registration_code,
                'transaction_status'  => $transactionStatus,
                'fraud_status'        => $fraudStatus,
                'registration_status' => $registrationStatus,
                'payment_type'        => $notif->payment_type ?? null,
                'gross_amount'        => $notif->gross_amount ?? null,
                'transaction_time'    => $notif->transaction_time ?? null,
                'settlement_time'     => $notif->settlement_time ?? null,
                'paid_at'             => $paidAt,
                'raw_callback'        => json_encode($request->all()),
            ]
        );

        // UPDATE REGISTRATIONS
        $registration->update([
            'payment_status'      => $paymentStatus,
            'registration_status' => $registrationStatus,
            'paid_at'             => $paidAt,
        ]);

        // KIRIM EMAIL KE PESERTA
        if ($paymentStatus === 'success' && $previousStatus !== 'success') {
            try {
                Mail::to($registration->email)->send(new RegistrationSuccessMail($registration));
                
                Log::info("Email sukses terkirim ke: " . $registration->email . " untuk Order ID: " . $orderId);
            } catch (\Exception $e) {
                Log::error("Email pendaftaran gagal dikirim: " . $e->getMessage());
            }
        }

        return response()->json(['message' => 'Callback processed'], 200);
    }

    /**
     * Handle successful ecourse payment
     */
    private function handleSuccessfulEcoursePayment($transaction)
    {
        $transaction->update(['status' => 'paid']);
        $user = $transaction->user;

        $meta = $transaction->meta ?? [];
        $type = $meta['type'] ?? null;
        $courseId = $meta['course_id'] ?? $transaction->course_id;
        $packageId = $meta['package_id'] ?? $transaction->package_id;

        Log::info('Processing successful ecourse payment', [
            'transaction_id' => $transaction->id,
            'user_id' => $user->id,
            'type' => $type,
            'course_id' => $courseId,
            'package_id' => $packageId,
        ]);

        // Import models
        $courseModel = \App\Models\Ecourse\Course::class;
        $packageModel = \App\Models\Ecourse\CoursePackage::class;
        $userCourseModel = \App\Models\Ecourse\UserCourse::class;
        $userSubscriptionModel = \App\Models\Ecourse\UserSubscription::class;

        if ($type === 'course' && $courseId) {
            // Grant course access
            $course = $courseModel::find($courseId);
            
            if ($course) {
                // Check if user already has access
                $existingAccess = $userCourseModel::where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->first();

                if (!$existingAccess) {
                    $userCourse = $userCourseModel::create([
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
                        'user_course_id' => $userCourse->id,
                        'transaction_id' => $transaction->id,
                    ]);
                } else {
                    // If user already has access but acquisition_type is not 'idr', update it
                    try {
                        if ($existingAccess->acquisition_type !== 'idr') {
                            $previousAcquisition = $existingAccess->acquisition_type;
                            $existingAccess->update(['acquisition_type' => 'idr']);

                            Log::info('Updated existing UserCourse acquisition_type to idr after Midtrans payment', [
                                'user_id' => $user->id,
                                'course_id' => $course->id,
                                'previous_acquisition_type' => $previousAcquisition,
                                'transaction_id' => $transaction->id,
                            ]);
                        } else {
                            Log::info('User already has access to this course', [
                                'user_id' => $user->id,
                                'course_id' => $course->id,
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to update UserCourse acquisition_type: ' . $e->getMessage(), [
                            'user_id' => $user->id,
                            'course_id' => $course->id,
                            'transaction_id' => $transaction->id,
                        ]);
                    }
                }
            } else {
                Log::warning('Course not found for transaction', [
                    'course_id' => $courseId,
                    'transaction_id' => $transaction->id,
                ]);
            }

        } elseif ($type === 'package' && $packageId) {
            // Create a fresh subscription record for each package purchase (do not modify existing subscriptions or update transaction->user_subscription_id)
            $package = $packageModel::find($packageId);

            if ($package) {
                // Ensure duration_days is numeric before passing to Carbon
                $endDate = $package->duration_days && is_numeric($package->duration_days)
                    ? now()->addDays((int) $package->duration_days)
                    : null;

                $userSubscription = $userSubscriptionModel::create([
                    'user_id' => $user->id,
                    'package_id' => $packageId,
                    'subscription_type' => $package->package_type === 'ustar' ? 'ustar' : 'monthly',
                    'plan_duration' => $package->plan_duration,
                    'start_date' => now(),
                    'end_date' => $endDate,
                    'certificate_quota' => $package->certificate_quota ?? 0,
                    'certificate_used' => 0,
                    'ustar_total' => $package->ustar_credits ?? 0,
                    'ustar_used' => 0,
                    'status' => 'active',
                ]);

                Log::info('Subscription row created after Midtrans package purchase', [
                    'user_id' => $user->id,
                    'package_id' => $packageId,
                    'user_subscription_id' => $userSubscription->id,
                    'subscription_type' => $userSubscription->subscription_type,
                    'certificate_quota' => $userSubscription->certificate_quota,
                    'ustar_credits' => $userSubscription->ustar_total,
                    'transaction_id' => $transaction->id,
                ]);

                // Update transaction meta with subscription start/end for display only (DO NOT store subscription id)
                try {
                    $transaction->update([
                        'meta' => array_merge($transaction->meta ?? [], [
                            'subscription_start' => optional($userSubscription->start_date)->format('d M Y, H:i'),
                            'subscription_end' => $userSubscription->end_date ? optional($userSubscription->end_date)->format('d M Y, H:i') : null,
                        ]),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to update transaction meta with subscription dates: ' . $e->getMessage());
                }

            } else {
                Log::warning('Package not found for transaction', [
                    'package_id' => $packageId,
                    'transaction_id' => $transaction->id,
                ]);
            }
        } else {
            Log::warning('Unknown payment type for transaction', [
                'transaction_id' => $transaction->id,
                'type' => $type,
                'course_id' => $courseId,
                'package_id' => $packageId,
            ]);
        }
    }
}

