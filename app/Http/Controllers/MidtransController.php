<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Notification;
use App\Models\Registration;
use App\Models\Payment;
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
        $transactionStatus = $notif->transaction_status;
        $fraudStatus = $notif->fraud_status ?? null;
        
        $registration = Registration::where('registration_code', $orderId)->first();
        $previousStatus = $registration->payment_status;
        if (!$registration) {
            return response()->json(['message' => 'Registration not found'], 404);
        }
        
        $registration->update(['payment' => $notif->payment_type]);

        if (!$registration) {
            return response()->json(['message' => 'Registration not found'], 404);
        }

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

        // 5. SIMPAN KE TABLE PAYMENTS
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

        // 6. UPDATE RINGKAS KE REGISTRATIONS
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
}
