<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use App\Models\Ecourse\CourseCertificate as EcourseCertificate;

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

        $certificateId = $validated['certificate_id'];

        if ($this->isEcourseCertificate($certificateId)) {
            $certificate = EcourseCertificate::where('certificate_number', $certificateId)->first();
        } else {
            $certificate = DB::table('certificates')
                ->where('cert_id', $certificateId)
                ->where('publish_date', '<=', now())
                ->first();
        }

        if (!$certificate) {
            return back()->withErrors(['certificate_id' => 'ID Sertifikat tidak ditemukan atau belum dipublikasi.'])->withInput();
        }

        return redirect()->route('certificate.view', ['id' => $certificateId]);
    }

    public function show($cert_id)
    {
        if ($this->isEcourseCertificate($cert_id)) {
            return $this->showEcourseCertificate($cert_id);
        }

        return $this->showEventCertificate($cert_id);
    }

    private function showEventCertificate($cert_id)
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

    private function showEcourseCertificate($cert_id)
    {
        // Fetch certificate without relying on removed template relation/table
        $certificate = EcourseCertificate::where('certificate_number', $cert_id)->first();

        if (!$certificate) {
            return redirect()->route('certificate')->withErrors('Sertifikat tidak valid.');
        }

        // Prefer dedicated ecourse blade template that matches event layout
        $viewPath = 'certificates.templates.template_ecourse1';
        if (! View::exists($viewPath)) {
            // Fallback to event template 1 if dedicated ecourse template is not present
            $viewPath = 'certificates.templates.template1';
        }

        $data = [
            'participant_name' => $certificate->user_name,
            'main_title' => $certificate->course_title,
            'sub_title' => '',
            'speaker_name' => '',
            'speaker_titles' => '',
            'event_date' => optional($certificate->issued_at)->format('d F Y'),
            'date_extra' => null,
            'certificate_id' => $certificate->certificate_number,
            'sign_path' => null,
            'has_sign' => false,
            'credential_url' => route('certificate.view', $certificate->certificate_number),
            'verify_url' => route('certificate'),
            'score' => $certificate->score,
        ];

        return view($viewPath, $data);
    }


    private function isEcourseCertificate(string $certificateId): bool
    {
        return Str::startsWith($certificateId, 'UNL-ECO');
    }

}
