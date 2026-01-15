<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Packet;
use App\Models\Registration;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\RegistrationSuccessMail;

class RegistrationController extends Controller
{
    public function create(Request $request)
    {   
        if ($user = Auth::user()){
            $requiredFields = [
                'name' => 'Nama Lengkap',
                'username' => 'Username', 
                'phone' => 'No. WhatsApp',
                'email' => 'Email',
                'gender' => 'Jenis Kelamin',
                'birth_place' => 'Tempat Lahir',
                'birth_date' => 'Tanggal Lahir',
                'city' => 'Kabupaten Domisili',
                'province' => 'Provinsi',
                'job' => 'Pekerjaan'
            ];
            $incompleteFields = [];
            foreach ($requiredFields as $field => $label) {
                if (empty($user->$field)) {
                    $incompleteFields[] = $label;
                }
            }
            if (!empty($incompleteFields)) {
                session(['url.intended' => url()->current()]);

                return redirect()->route('profile')
                    ->with('warning', 'Lengkapi data profil kamu sebelum melakukan pendaftaran webinar');
            }
            if (!empty($incompleteFields)) {

                if (!session()->has('url.intended')) {
                    session(['url.intended' => url()->previous()]);
                }

                return redirect()->route('profile')
                    ->with('warning', 'Lengkapi data profil kamu sebelum melakukan pendaftaran webinar');
            }
        }

        $eventCode = $request->get('event_code');
        $defaultEventId = null;

        if ($eventCode) {
            $event = Event::where('event_code', $eventCode)->first();
            $defaultEventId = $event ? $event->id : null;
        }

        $events = Event::where('status', 'open')->orderBy('date_start')->get();
        $provinces = Area::whereRaw('LENGTH(kode)<=5')->get();

        $professions = [
            'Mahasiswa',
            'Profesional',
            'Wirausaha',
            'Pegawai Pemerintah',
        ];

        $channels = [
            'IG UNLOCK',
            'IG Teman',
            'Whatsapp Message UNLOCK',
            'Whatsapp Message Teman',
            'Status Whatsapp Teman/Kolega',
            'Group Whatsapp UNLOCK',
            'Group Whatsapp Teman',
            'Email',
            'Website',
            'Universitas',
        ];

        return view('registration.create', compact('events', 'provinces', 'professions', 'channels', 'defaultEventId'));
    }

    public function getPackets(Event $event)
    {
        $packets = $event->packets()
            ->where('packets.is_active', true) // prefix table biar aman
            ->select([
                'packets.id',
                'packets.packet_name',
                'packets.price',
                'packets.description',
                'packets.requirements',
            ])
            ->get();

        return response()->json($packets);
    }

    public function getRegencies($provinceCode)
    {
        $regencies = Area::where('kode', 'like', "$provinceCode.%")
                            ->whereRaw('LENGTH(kode)=5')
                            ->get();

        return response()->json($regencies);
    }

    public function store(Request $request)
    {
        $request->validate([
            'event_id'  => 'required|exists:events,id',
            'packet_id' => 'required|exists:packets,id',
            'name' => 'required|string|max:255',
            'email'     => 'required|email',
            'whatsapp' => 'required|string|max:20',
            'gender' => 'required|in:male,female',
            'age' => 'required|integer|min:10|max:100',
            'province' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'profession' => 'required|string|max:255',
            'channel_information' => 'required|array',
            'channel_information.*' => 'required|max:255',
            'upload_proofs'   => 'nullable|array',
            'upload_proofs.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', 
        ],
        [
            'event_id.required' => 'Pilih event terlebih dahulu.',
            'packet_id.required' => 'Pilih paket terlebih dahulu.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp.max' => 'Nomor WhatsApp maksimal 20 karakter.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Jenis kelamin tidak valid.',
            'age.required' => 'Usia wajib diisi.',
            'age.integer' => 'Usia harus berupa angka.',
            'age.min' => 'Usia minimal 10 tahun.',
            'age.max' => 'Usia maksimal 100 tahun.',
            'province.required' => 'Provinsi wajib dipilih.',
            'province.max' => 'Provinsi maksimal 100 karakter.',
            'city.required' => 'Kota/Kabupaten wajib dipilih.',
            'city.max' => 'Kota/Kabupaten maksimal 100 karakter.',
            'profession.required' => 'Profesi wajib dipilih.',
            'profession.max' => 'Profesi maksimal 255 karakter.',
            'channel_information.required' => 'Pilih minimal satu sumber informasi.',
            'channel_information.array' => 'Sumber informasi harus berupa array.',
            'channel_information.min' => 'Pilih minimal satu sumber informasi.',
            'channel_information.*.string' => 'Sumber informasi harus berupa teks.',
            'channel_information.*.max' => 'Sumber informasi maksimal 255 karakter.',
            'upload_proofs' => 'Cek bukti persyaratan',
            'upload_proofs.*' => 'Setiap file maksimal 2MB',
        ]
        );

        // 2. AMBIL EVENT & PACKET
        $event  = Event::findOrFail($request->event_id);
        $packet = Packet::findOrFail($request->packet_id);

        // Validasi: paket harus milik event ini
        if (! $event->packets()->where('packets.id', $packet->id)->exists()) {
            return back()->withErrors('Paket tidak valid untuk event ini');
        }

        // 3. SANITASI WHATSAPP
        $whatsapp = $request->whatsapp;
        $whatsapp = preg_replace('/^0/', '', $whatsapp);
        $whatsapp = preg_replace('/^62/', '', $whatsapp);
        $whatsapp = ltrim($whatsapp, '+');
        $whatsapp = preg_replace('/[^0-9]/', '', $whatsapp);

        // 4. AMBIL NAMA PROVINSI DAN KABUPATEN/KOTA
        $provinceName = Area::where('kode', $request->province)->value('nama');
        $cityName     = Area::where('kode', $request->city)->value('nama');

        // Generate registration code
        $registrationCode = Registration::generateRegistrationCode($event);

        $isExisting = Registration::where('email', $request->email)
                        ->orWhere('whatsapp', $whatsapp)
                        ->exists();

        // 2. Tentukan nilai flag_sub berdasarkan hasil pengecekan
        $flagSub = $isExisting ? 'existing' : 'new';


        // 5. SIMPAN DATA REGISTRASI
        $registration = Registration::create([
            'event_id'    => $event->id,
            'packet_id'   => $packet->id,
            'user_id'     => Auth::user()->id,    
            'event_code'  => $event->event_code,
            'event_name'  => $event->event_title,
            'packet_name' => $packet->packet_name,
            'name'   => $request->name,
            'email'       => $request->email,
            'whatsapp'    => $whatsapp,
            'gender'      => $request->gender,
            'age'         => $request->age,
            'province'    => $provinceName,
            'city'        => $cityName,
            'profession'  => $request->profession,
            'channel_information' => $request->channel_information,
            'price'       => $packet->price,
            'payment_unique_code' => $event->payment_unique_code,
            'payment_status' => 'pending',
            'registration_status' => 'pending',
            'registration_code' => $registrationCode,
            'flag_sub'            => $flagSub,
        ]);

        // 5. PROSES UPLOAD PROOFS NON PAYMENT
        $requirements = $packet->requirements ?? [];
        
        // Ambil semua file bukti sekaligus
        // Hasilnya array: ['follow_ig' => FileObj, 'tag_friends' => FileObj]
        $uploadedProofs = $request->file('upload_proofs') ?? [];

        foreach ($requirements as $category => $isRequired) {
            // Skip jika requirement ini diset false (tidak wajib)
            if (!$isRequired) continue;

            // Cek apakah user mengupload file untuk kategori ini
            // Kita cek key array-nya (misal: 'follow_ig')
            if (!isset($uploadedProofs[$category])) {
                // Hapus data registrasi agar bersih (Rollback manual)
                $registration->delete(); 
                return back()->withErrors(['upload_' . $category => "Bukti {$category} wajib diupload."])->withInput();
            }

            $file = $uploadedProofs[$category];

            if (!$file || !$file->isValid()) {
                $registration->delete();
                return back()->withErrors(['upload_' . $category => "File {$category} tidak valid."])->withInput();
            }

            // Simpan File
            $manager = new ImageManager(new Driver());
            $image = $manager
                ->read($file)
                ->scaleDown(1024)
                ->toJpeg(65); 
            $path = "proofs/{$registration->event_code}/{$category}/" . uniqid() . '.jpg';
            Storage::disk('public')->put($path, $image);

            // Simpan ke Database
            $registration->uploadProofs()->create([
                'category' => $category,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'registration_code' => $registrationCode,
            ]);
        }

        // 6. REDIRECT based on price
        if ($packet->price > 0) {
            // Redirect to payment page for paid packages
            return redirect()->route('registration.payment', [
                'registration' => $registration->id,
                'event' => $event,
                'packet' => $packet
            ]);
        } else {
            // Update status pendaftaran gratis
            $registration->update(['payment_status' => 'success']);

            // KIRIM EMAIL KE PESERTA
            try {
                Mail::to($registration->email)->send(new RegistrationSuccessMail($registration));
            } catch (\Exception $e) {
                Log::error("Email pendaftaran gagal dikirim: " . $e->getMessage());
            }

            return redirect()->to(
                route('registration.status', ['status' => 'success']) .
                '?order_id=' . $registration->registration_code
            );
        }

    }

    public function status(Request $request)
    {
        if ($request->transaction_status) {
            $transactionStatus = $request->transaction_status ?? $request->status;

            if (in_array($transactionStatus, ['settlement', 'capture'])) {
                $status = 'success';
            } elseif ($transactionStatus === 'pending') {
                $status = 'pending';
            } else {
                $status = 'error';
            }
        } else {
            $status = $request->status;
        }

        $registration = null;
        if ($request->has('order_id')) {
            $registration = Registration::where('registration_code', $request->order_id)->first();
        }

        return view('registration.status', [
            'status' => $status,
            'registration' => $registration,
        ]);
    }

    public function payment(Registration $registration)
    {
        $event = $registration->event;
        $packet = $registration->packet;

        // Konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
        
        $params = [
            'transaction_details' => [
                'order_id' => $registration->registration_code,
                'gross_amount' => (int) $registration->price + $event->payment_unique_code ,
            ],
            'customer_details' => [
                'first_name' => $registration->name,
                'email' => $registration->email,
                'phone' => $registration->whatsapp,
            ],
            'item_details' => [
                [
                'id' => $packet->id,
                'price' => (int) $registration->price,
                'quantity' => 1,
                'name' => $packet->packet_name . '-' . $event->event_title,
                ],
                [
                'id' => 'unique_code',
                'price' => (int) $event->payment_unique_code,
                'quantity' => 1,
                'name' => 'Kode Unik Pembayaran',
                ]
            ],
            'callbacks' => [
                'finish'   => route('registration.status', ['status' => 'success']) . '?order_id=' . $registration->registration_code,
                'unfinish' => route('registration.status', ['status' => 'pending']) . '?order_id=' . $registration->registration_code,
                'error'    => route('registration.status', ['status' => 'error']) . '?order_id=' . $registration->registration_code,
            ],
        ];

        // Generate Snap Token Midtrans
        if (!empty($registration->snap_token)) {
            // Sudah ada token, pakai lama
            $snapToken = $registration->snap_token;

        } elseif ($registration->payment_status === 'pending') {
            // Pending tapi belum pernah generate token
            $snapToken = Snap::getSnapToken($params);
            $registration->update(['snap_token' => $snapToken]);

        } else {
            // Status lain (misal gagal, expired, baru)
            $snapToken = Snap::getSnapToken($params);
            $registration->update(['snap_token' => $snapToken, 'payment_status' => 'pending']);
        }

        // Kirim token ke view
        return view('registration.payment', compact('registration', 'event', 'packet', 'snapToken'));
    }

    public function attendance($registration_code)
    {
        // Cari data pendaftaran beserta relasi event-nya
        $registration = Registration::with('event')
            ->where('registration_code', $registration_code)
            ->firstOrFail();

        if (!$registration->event->is_attendance_open) {
            return redirect()->route('myevents.show', $registration->id)
                ->with('error', 'Mohon maaf, presensi untuk event ini belum dibuka atau sudah ditutup.');
        }

        // Proteksi: Hanya pendaftaran yang sudah diverifikasi yang bisa absen
        if ($registration->registration_status !== 'verified') {
            return redirect()->route('myevents.show', $registration->id)
                ->with('error', 'Status pendaftaran Anda belum diverifikasi atau ditolak.');
        }

        // Proteksi: Cek jika sudah pernah absen (opsional)
        if ($registration->is_attended) {
            return redirect()->route('myevents.show', $registration->id)
                ->with('info', 'Anda sudah melakukan pengisian absensi sebelumnya.');
        }

        return view('registration.attendance', [
            'registration' => $registration,
            'event' => $registration->event
        ]);
    }

    public function storeAttendance(Request $request, $id)
    {
        // 1. Validasi
        $request->validate([
            'attendance_proof' => 'required|image|max:2048',
        ]);

        $registration = Registration::findOrFail($id);

        // 2. Upload Gambar
        $fileName = null;
        if ($request->hasFile('attendance_proof')) {
            $file = $request->file('attendance_proof');
            $fileName = 'attendance_'. strtolower($registration->registration_code) . time() . '.jpg';
            $path = 'attendance_proofs/' . $fileName;

            // Inisialisasi ImageManager
            $manager = new ImageManager(new Driver());
            
            // Proses: Baca -> Scale Down -> Encode ke Jpeg (kualitas 65)
            $image = $manager
                ->read($file)
                ->scaleDown(800) // Saya sarankan 800px agar teks di screenshot tetap terbaca jelas
                ->toJpeg(65);

            // Simpan ke storage (public disk)
            Storage::disk('public')->put($path, (string) $image);
        }

        // 3. Update Data (Gabungkan checkbox & input lainnya)
        $feedbackData = [
            'selections' => $request->input('suggestions', []),
            'other' => $request->input('other_feedback'),
        ];

        $registration->update([
            'is_attended' => true,
            'attended_at' => now(),
            'attendance_proof' => $path,
            'rating' => $request->rating,
            'feedback' =>  $feedbackData,
            'next_theme_suggestion' => $request->next_theme_suggestion,
        ]);

        return redirect()->route('myevents.show', $registration->id)
            ->with('success', 'Absensi Anda berhasil dicatat!');
    }

    public function listAttendance()
    {
        $pendingAttendances = Registration::with('event')
            ->where('user_id', auth()->id())
            ->where('registration_status', 'verified')
            ->where('is_attended', false)
            ->get();

        return view('registration.attendance_list', compact('pendingAttendances'));
    }

}
