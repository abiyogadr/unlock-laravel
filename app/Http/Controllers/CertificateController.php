<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class CertificateController extends Controller
{
    public function index()
    {
        return view('certificates.certificate');
    }
    
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'certificate_id' => 'required|string|max:50',
        ], [
            'certificate_id.required' => 'ID Sertifikat wajib diisi.'
        ]);

        $certificate = DB::table('certificates')
            ->where('cert_id', $validated['certificate_id'])
            ->where('publish_date', '<=', now())
            ->first();

        if (!$certificate) {
            return back()->withErrors(['certificate_id' => 'ID Sertifikat tidak ditemukan atau belum dipublikasi.'])->withInput();
        }

    return redirect()->route('certificate.view', ['id' => $certificate->cert_id]);
    }

    public function show($cert_id)
    {
        $certificate = DB::table('certificates')
            ->where('cert_id', $cert_id)
            ->where('publish_date', '<=', now())
            ->first();

        if (!$certificate) {
            return redirect()->route('certificate')->withErrors('Sertifikat tidak valid.');
        }

        // 1. Tentukan template secara dinamis
        // Jika di DB hanya simpan angka '1', '2', '3', pastikan formatnya jadi 'template1', dst.
        $templateNum = $certificate->template ?: '1';
        $viewPath = "certificates.templates.template{$templateNum}";

        // Fallback jika file template tidak ditemukan
        if (!View::exists($viewPath)) {
            $viewPath = 'certificates.templates.template1';
        }

        // 2. Siapkan data untuk template
        $data = [
            'participant_name' => $certificate->name,
            'webinar_name'     => 'Webinar Unlock',
            'main_title'       => $certificate->event_title,
            'sub_title'        => $certificate->event_subtitle,
            'speaker_name'     => $certificate->speaker,
            'speaker_titles'   => $certificate->speaker_title,
            'event_date'       => $certificate->date,
            'date_extra'       => $certificate->date_extra,
            'certificate_id'   => $certificate->cert_id,
            'sign_path'        => $certificate->sign_path,
            
            // 3. Tambahkan variabel has_sign
            // Jika tabel certificates punya kolom has_sign, gunakan itu. 
            // Jika tidak, anggap true jika sign_path tidak kosong.
            'has_sign'         => isset($certificate->has_sign) 
                                    ? (bool) $certificate->has_sign 
                                    : !empty($certificate->sign_path),

            'credential_url'   => route('certificate.view', $certificate->cert_id),
            'verify_url'       => route('certificate'),
        ];

        return view($viewPath, $data);
    }

}
