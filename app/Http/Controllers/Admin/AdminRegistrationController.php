<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\EventCertificate;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminRegistrationController extends Controller
{
    /**
     * Halaman Utama: Daftar Event dengan Ringkasan Statistik
     */
    public function index(Request $request)
    {
        $query = Event::withCount('registrations'); // Hitung jumlah pendaftar

        // Filter Status Event
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $events = $query->latest('date_start')->paginate(10);

        // Tambahkan data nominal pendapatan per event (Manual Calculation karena butuh relasi packet)
        // Jika database besar, sebaiknya pakai Raw SQL / Subquery biar ringan. 
        // Untuk skala kecil-menengah, loop ini masih oke.
        $events->getCollection()->transform(function ($event) {
            // Hitung total bayar dari pendaftar yang statusnya verified/paid
            $event->total_revenue = Registration::where('event_id', $event->id)
                ->where('registration_status', 'verified')
                ->where('payment_status', 'success')
                ->sum('price');
            return $event;
        });

        return view('admin.registrations.index', compact('events'));
    }

    /**
     * Halaman Detail: Daftar Peserta untuk 1 Event Tertentu
     */
    public function showEventRegistrants(Request $request, Event $event)
    {
        // Gunakan 'registration_status' agar konsisten dengan kolom di DB Anda
        $status = $request->get('status', 'all');
        $search = $request->get('search');

        $query = Registration::with(['user', 'packet'])
            ->where('event_id', $event->id);

        // Filter Status Pendaftaran
        if ($status !== 'all') {
            $query->where('registration_status', $status);
        }

        // Pencarian Peserta (Nama, Email, atau Kode)
        if ($request->filled('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%") // Gunakan 'name' sesuai Blade Anda
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('registration_code', 'like', "%{$search}%");
            });
        }

        $registrations = $query->latest()->paginate(20)->withQueryString();

        // Statistik untuk Dashboard Atas
        $stats = [
            'total' => Registration::where('event_id', $event->id)->count(),
            'pending' => Registration::where('event_id', $event->id)->where('registration_status', 'pending')->count(),
            'verified' => Registration::where('event_id', $event->id)->where('registration_status', 'verified')->count(),
            'revenue' => Registration::where('event_id', $event->id)
                ->where('registration_status', 'verified')
                ->where('payment_status', 'success')
                ->sum('price')
        ];

        return view('admin.registrations.show_event', compact('event', 'registrations', 'stats'));
    }

    public function verify(Registration $registration)
    {
        $registration->update([
            'registration_status' => 'verified',
        ]);

        return back();
    }

    public function reject(Registration $registration)
    {
        $registration->update([
            'registration_status' => 'rejected',
        ]);

        return back();
    }
   
    // Ambil data untuk modal massal
    public function listForCertificate($eventId)
    {
        $participants = Registration::where('event_id', $eventId)
            ->where('registration_status', 'verified')
            ->with('packet')
            ->orderBy('id', 'desc') 
            ->get()
            ->sortByDesc([
                // Urutkan berdasarkan ID (terbaru)
                ['id', 'desc'],
                // Urutkan berdasarkan nama paket (Z-A)
                fn ($a, $b) => ($a->packet->packet_name ?? '') <=> ($b->packet->packet_name ?? ''),
                // Urutkan yang sudah absen (1) berada di atas yang belum (0)
                ['is_attended', 'desc'],
            ])
            ->map(function ($reg) {
                $isPaid = $reg->packet->price > 0;
                $hasCert = !is_null($reg->certificate);
                return [
                    'id' => $reg->id,
                    'name' => $reg->name,
                    'packet_type' => $reg->packet->packet_name,
                    'attended' => (bool)$reg->is_attended,
                    'has_certificate' => $hasCert,
                    // Aturan centang otomatis Anda
                    'selected' => ($isPaid || (!$isPaid && $reg->is_attended)) && !$hasCert,
                ];
            });

        return response()->json($participants);
    }

    // Generate Tunggal
    public function generateSingleCertificate(Registration $registration)
    {
        // 1. Ambil data template sertifikat dari tabel EventCertificate berdasarkan event_id
        $template = EventCertificate::where('event_id', $registration->event_id)->first();

        if (!$template) {
            return redirect()->back()->with('error', 'Template sertifikat untuk event ini belum diatur.');
        }

        // 2. Logika pembuatan cert_id (Mengganti 'REG' menjadi 'UNL' dari registration_code)
        // Contoh: REG-2024-001 menjadi UNL-2024-001
        $certId = str_replace('REG-UNL', 'UNL-', $registration->registration_code);

        $publishDate = $registration->price == 0 ? now()->addDays(7) : now();

        // 3. Simpan atau Update ke tabel Certificate
        Certificate::updateOrCreate(
            ['event_id' => $registration->event_id, 'name' => $registration->name],
            [
                'cert_id'        => $certId,
                'name'           => $registration->name,
                'event_id'       => $registration->event_id,
                'registration_id'=> $registration->id,
                'user_id'       => $registration->user_id,
                'event_code'     => $registration->event_code,
                'event_title'    => $template->event_title,
                'event_subtitle' => $template->event_subtitle,
                'speaker'        => $template->speaker,
                'speaker_title'  => $template->speaker_title,
                'date'           => $template->date_string,
                'date_extra'     => $template->date_extra,
                'has_sign'       => $template->has_sign,
                'sign_path'      => $template->sign_path,
                'template'       => $template->template,
                'packet'         => $registration->packet_name,
                'publish_date'   => $publishDate,
            ]
        );

        return redirect()->back()->with('success', 'Sertifikat berhasil dibuat dan data telah disimpan.');
    }
    // Generate Massal
    public function generateMassCertificate(Request $request)
    {   
        $ids = $request->input('ids', []);
        
        $processedCount = 0;

        foreach ($ids as $id) {
            $registration = Registration::find($id);
            
            if ($registration) {
                $template = EventCertificate::where('event_id', $registration->event_id)->first();

                if (!$template) continue;

                $certId = str_replace('REG-UNL', 'UNL-', $registration->registration_code);

                $publishDate = $registration->price == 0 ? now()->addDays(7) : now();

                Certificate::updateOrCreate(
                    [
                        'registration_id' => $registration->id
                    ],
                    [
                        'cert_id'         => $certId,
                        'name'            => $registration->name,
                        'event_id'        => $registration->event_id,
                        'user_id'         => $registration->user_id,
                        'event_code'      => $registration->event_code,
                        'event_title'     => $template->event_title,
                        'event_subtitle'  => $template->event_subtitle,
                        'speaker'         => $template->speaker,
                        'speaker_title'   => $template->speaker_title,
                        'date'            => $template->date_string,
                        'date_extra'      => $template->date_extra,
                        'has_sign'        => $template->has_sign,
                        'sign_path'       => $template->sign_path,
                        'template'        => $template->template,
                        'packet'          => $registration->packet_name,
                        'publish_date'    => $publishDate,
                    ]
                );

                $processedCount++;
            }
        }

        return response()->json([
            'message' => $processedCount . ' sertifikat berhasil diproses dan disimpan.'
        ]);
    }

    public function setupCertificate(Event $event)
    {
        $setup = $event->certificateTemplate;
        $speaker = $event->speakers()->first(); 
        return view('admin.events.certificate_setup', compact('event', 'setup', 'speaker'));
    }

    public function exportCsv($event_id)
    {
        $event = Event::findOrFail($event_id);
        $fileName = 'registrations_' . Str::slug($event->event_code) . '_' . date('Ymd') . '.csv';

        $registrations = Registration::where('event_id', $event_id)->get();

        $headers = [
            "Content-type"        => "text/csv; charset=utf-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'registration_code', 'event_code', 'event_name', 'packet_name', 'name', 'email', 'whatsapp', 
            'gender', 'age', 'province', 'city', 'profession', 'channel_information', 'price', 
            'registration_status', 'is_attended', 'attended_at', 'payment', 'payment_status', 
            'paid_at', 'flag_sub', 'rating', 'feedback', 'next_theme_suggestion', 'timestamp'
        ];

        $callback = function() use($registrations, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($registrations as $reg) {
    
                $channelString = is_array($reg->channel_information) 
                    ? implode(', ', $reg->channel_information) 
                    : (string) $reg->channel_information;

                $attended = $reg->is_attended ? 'Ya' : 'Tidak';
                $gender = $reg->gender === 'male' ? 'Laki-laki' : 'Perempuan';

                // Handle feedback as array (already casted by model)
                $feedbackString = '';
                if (is_array($reg->feedback) && isset($reg->feedback['selections'])) {
                    $feedbackString = implode('|', $reg->feedback['selections']);
                    if (!empty($reg->feedback['other'])) {
                        $feedbackString .= '|' . $reg->feedback['other'];
                    }
                } elseif (is_string($reg->feedback)) {
                    // Fallback if somehow it's a string
                    $feedbackData = json_decode($reg->feedback, true);
                    if (is_array($feedbackData) && isset($feedbackData['selections'])) {
                        $feedbackString = implode('|', $feedbackData['selections']);
                        if (!empty($feedbackData['other'])) {
                            $feedbackString .= '|' . $feedbackData['other'];
                        }
                    } else {
                        $feedbackString = $reg->feedback;
                    }
                } else {
                    $feedbackString = (string) $reg->feedback;
                }

                fputcsv($file, [
                    $reg->registration_code, $reg->event_code, $reg->event_name, $reg->packet_name,
                    $reg->name, $reg->email, $reg->whatsapp, $gender, $reg->age,
                    $reg->province, $reg->city, $reg->profession, $channelString,
                    $reg->price, $reg->registration_status, $attended, $reg->attended_at,
                    $reg->payment, $reg->payment_status, $reg->paid_at, $reg->flag_sub,
                    $reg->rating, $feedbackString, $reg->next_theme_suggestion, $reg->created_at
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function previewCertificate(Request $request, Event $event)
    {
        $setup = $event->certificateTemplate;
        $displayPath = $setup->sign_path ?? null;

        // 1. Siapkan data yang WAJIB ada di DB agar tidak error 1364 (General Error)
        // Ambil dari request, jika kosong ambil dari DB lama, jika masih kosong pakai default
        $baseData = [
            'event_title'    => $request->event_title ?? ($setup->event_title ?? $event->event_title),
            'event_subtitle' => $request->event_subtitle ?? ($setup->event_subtitle ?? ''),
            'speaker'        => $request->speaker ?? ($setup->speaker ?? 'Narasumber'),
            'speaker_title'  => $request->speaker_title ?? ($setup->speaker_title ?? ''),
            'date_string'    => $request->date_string ?? ($setup->date_string ?? now()->format('d F Y')),
            'date_extra'     => $request->date_extra ?? ($setup->date_extra ?? ''),
            'template'       => $request->template ?? ($setup->template ?? '1'),
            'has_sign'       => $request->has('has_sign'),
        ];

        // 2. Handle Upload Tanda Tangan Sementara
        if ($request->hasFile('sign_image')) {
            // Hapus file temp lama jika ada
            if ($setup && $setup->temp_sign_path) {
                Storage::disk('public')->delete($setup->temp_sign_path);
            }
            
            $path = $request->file('sign_image')->store('certificates/signs/tmp', 'public');
            
            // Tambahkan path temp ke data yang akan disimpan
            $baseData['temp_sign_path'] = $path;
            $displayPath = $path;
        } elseif ($setup && $setup->temp_sign_path) {
            $displayPath = $setup->temp_sign_path;
        }

        // 3. Simpan SEKALIGUS semua kolom wajib agar DB tidak protes
        $event->certificateTemplate()->updateOrCreate(
            ['event_id' => $event->id],
            $baseData
        );

        // 4. Tentukan Template View
        $templateId = $baseData['template'];
        $viewPath = "certificates.templates.template{$templateId}";

        if (!view()->exists($viewPath)) {
            $viewPath = 'certificates.templates.template1';
        }

        // 5. Kirim data lengkap ke View
        return view($viewPath, [
            'participant_name' => '[NAMA PESERTA CONTOH]',
            'webinar_name'     => 'Webinar Unlock',
            'main_title'       => $baseData['event_title'],
            'sub_title'        => $baseData['event_subtitle'],
            'speaker_name'     => $baseData['speaker'],
            'speaker_titles'   => $baseData['speaker_title'],
            'event_date'       => $baseData['date_string'],
            'date_extra'       => $baseData['date_extra'],
            'certificate_id'   => 'PREVIEW-001',
            'sign_path'        => $displayPath,
            'has_sign'         => $baseData['has_sign'],
            'credential_url'   => url('/preview/url'),
            'verify_url'       => route('certificate'),
            'is_preview'       => true,
        ]);
    }

    public function storeCertificateSetup(Request $request, Event $event)
    {
        $request->validate([
            'event_title' => 'required|string|max:255',
            'speaker'     => 'required|string|max:255',
            'sign_image'  => 'nullable|image|mimes:png|max:1024',
            'template'    => 'required|in:1,2,3',
        ]);

        $existingSetup = $event->certificateTemplate;
        
        $data = [
            'event_title'    => $request->event_title,
            'event_subtitle' => $request->event_subtitle,
            'speaker'        => $request->speaker,
            'speaker_title'  => $request->speaker_title,
            'date_string'    => $request->date_string,
            'date_extra'     => $request->date_extra,
            'template'       => $request->template,
            'has_sign'       => $request->has('has_sign'),
        ];

        // LOGIKA TANDA TANGAN
        if ($request->delete_sign === '1') {
            // Hapus semua file (permanen & temp)
            if ($existingSetup) {
                if ($existingSetup->sign_path) Storage::disk('public')->delete($existingSetup->sign_path);
                if ($existingSetup->temp_sign_path) Storage::disk('public')->delete($existingSetup->temp_sign_path);
            }
            $data['sign_path'] = null;
            $data['temp_sign_path'] = null;
        } 
        elseif ($request->hasFile('sign_image')) {
            // Jika user upload langsung saat klik Save (jarang terjadi jika preview jalan, tapi tetap dihandle)
            if ($existingSetup && $existingSetup->sign_path) {
                Storage::disk('public')->delete($existingSetup->sign_path);
            }
            $data['sign_path'] = $request->file('sign_image')->store('certificates/signs', 'public');
            $data['temp_sign_path'] = null;
        } 
        elseif ($existingSetup && $existingSetup->temp_sign_path) {
            // PINDAHKAN DARI TEMP KE PERMANEN
            $tempPath = $existingSetup->temp_sign_path;
            $permanentPath = str_replace('tmp/', '', $tempPath);

            // Hapus file permanen lama jika ada
            if ($existingSetup->sign_path) {
                Storage::disk('public')->delete($existingSetup->sign_path);
            }

            // Pindahkan file fisik
            if (Storage::disk('public')->exists($tempPath)) {
                Storage::disk('public')->move($tempPath, $permanentPath);
                $data['sign_path'] = $permanentPath;
            }
            
            $data['temp_sign_path'] = null;
        }

        $event->certificateTemplate()->updateOrCreate(
            ['event_id' => $event->id],
            $data
        );

        return redirect()->back()->with('success', 'Konfigurasi sertifikat berhasil disimpan!');
    }

}
