<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Packet;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;

class AdsRegistrationController extends Controller
{
    /**
     * Simpan registrasi dari ads landing page.
     * - User yang login → user_id = auth()->id()
     * - Tamu (tidak login) → user_id = 1 (default guest)
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'event_id'              => 'required|exists:events,id',
            'packet_id'             => 'required|exists:packets,id',
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|max:255',
            'whatsapp'              => 'required|string|max:20',
            'gender'                => 'required|in:male,female',
            'age'                   => 'required|integer|min:10|max:100',
            'province'              => 'required|string|max:100',
            'city'                  => 'required|string|max:100',
            'profession'            => 'required|string|max:255',
            'channel_information'   => 'required|array|min:1',
            'channel_information.*' => 'string|max:255',
        ], [
            'event_id.required'           => 'Event tidak ditemukan.',
            'packet_id.required'          => 'Pilih paket terlebih dahulu.',
            'name.required'               => 'Nama lengkap wajib diisi.',
            'email.required'              => 'Email wajib diisi.',
            'email.email'                 => 'Format email tidak valid.',
            'whatsapp.required'           => 'Nomor WhatsApp wajib diisi.',
            'gender.required'             => 'Jenis kelamin wajib dipilih.',
            'age.required'                => 'Usia wajib diisi.',
            'age.min'                     => 'Usia minimal 10 tahun.',
            'age.max'                     => 'Usia maksimal 100 tahun.',
            'province.required'           => 'Provinsi wajib diisi.',
            'city.required'               => 'Kota/Kabupaten wajib diisi.',
            'profession.required'         => 'Profesi wajib dipilih.',
            'channel_information.required'=> 'Pilih minimal satu sumber informasi.',
            'channel_information.min'     => 'Pilih minimal satu sumber informasi.',
        ]);

        $event  = Event::findOrFail($validated['event_id']);
        $packet = Packet::findOrFail($validated['packet_id']);

        // Validasi packet milik event ini
        if (! $event->packets()->where('packets.id', $packet->id)->exists()) {
            return response()->json(['message' => 'Paket tidak valid untuk event ini.'], 422);
        }

        // Sanitasi nomor WhatsApp → hapus leading 0 / 62 / +, sisakan digit saja
        $whatsapp = preg_replace('/^0/', '', $validated['whatsapp']);
        $whatsapp = preg_replace('/^62/', '', $whatsapp);
        $whatsapp = ltrim($whatsapp, '+');
        $whatsapp = preg_replace('/[^0-9]/', '', $whatsapp);

        $isExisting = Registration::where('email', $validated['email'])
                        ->orWhere('whatsapp', $whatsapp)
                        ->exists();

        $registrationCode = Registration::generateRegistrationCode($event);

        $registration = Registration::create([
            'event_id'            => $event->id,
            'packet_id'           => $packet->id,
            'user_id'             => Auth::id() ?? 1,   // default 1 untuk tamu
            'event_code'          => $event->event_code,
            'event_name'          => $event->event_title,
            'packet_name'         => $packet->packet_name,
            'name'                => $validated['name'],
            'email'               => $validated['email'],
            'whatsapp'            => $whatsapp,
            'gender'              => $validated['gender'],
            'age'                 => $validated['age'],
            'province'            => $validated['province'],
            'city'                => $validated['city'],
            'profession'          => $validated['profession'],
            'channel_information' => $validated['channel_information'],
            'price'               => $packet->price,
            'payment_status'      => 'pending',
            'registration_status' => 'pending',
            'registration_code'   => $registrationCode,
            'flag_sub'            => $isExisting ? 'existing' : 'new',
        ]);

        $paymentUrl = route('ads.payment', ['registration' => $registration->id]);
        $loginUrl   = route('login') . '?redirect=' . urlencode($paymentUrl);

        return response()->json([
            'success'           => true,
            'message'           => 'Pendaftaran berhasil! Lanjutkan ke pembayaran.',
            'registration_code' => $registrationCode,
            'redirect_url'      => $paymentUrl,
            'login_url'         => $loginUrl,
        ]);
    }

    /**
     * Halaman pembayaran khusus ads — auto-open Midtrans Snap.
     */
    public function payment(Registration $registration)
    {
        $event  = $registration->event;
        $packet = $registration->packet;

        // Konfigurasi Midtrans
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized  = config('midtrans.is_sanitized');
        Config::$is3ds        = config('midtrans.is_3ds');

        // Hitung harga akhir
        $finalPayable = max(0, (int) $registration->price - (int) ($registration->voucher_discount ?? 0));
        $grossAmount  = $finalPayable + (int) $event->payment_unique_code;

        $itemDetails = [
            [
                'id'       => $packet->id,
                'price'    => (int) $registration->price,
                'quantity' => 1,
                'name'     => substr($packet->packet_name . ' - ' . $event->event_title, 0, 50),
            ],
            [
                'id'       => 'unique_code',
                'price'    => (int) $event->payment_unique_code,
                'quantity' => 1,
                'name'     => 'Kode Unik Pembayaran',
            ],
        ];

        if ((int) ($registration->voucher_discount ?? 0) > 0) {
            $itemDetails[] = [
                'id'       => 'voucher_discount',
                'price'    => -(int) $registration->voucher_discount,
                'quantity' => 1,
                'name'     => 'Diskon Voucher (' . ($registration->voucher_code ?? '') . ')',
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id'     => $registration->registration_code,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $registration->name,
                'email'      => $registration->email,
                'phone'      => $registration->whatsapp,
            ],
            'item_details' => $itemDetails,
            'callbacks' => [
                // Redirect back to ads payment page so the merchant view can show correct state
                'finish'   => route('ads.payment', ['registration' => $registration->id]) . '?status=success',
                'unfinish' => route('ads.payment', ['registration' => $registration->id]) . '?status=pending',
                'error'    => route('ads.payment', ['registration' => $registration->id]) . '?status=error',
            ],
        ];

        // Generate atau reuse snap token
        if (!empty($registration->snap_token)) {
            $snapToken = $registration->snap_token;
        } else {
            $snapToken = Snap::getSnapToken($params);
            $registration->update(['snap_token' => $snapToken]);
        }

        return view('ads.payment', compact('registration', 'event', 'packet', 'snapToken', 'grossAmount'));
    }
}
