<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CertificateTemplateSeeder extends Seeder
{
    public function run()
    {
        // Seeder disabled - certificate_templates table has been removed from the project.
        // Keeping this file as a no-op to avoid breaking legacy seeds.
        return;
            [
                'name' => 'Modern Webinar Template',
                'html_content' => '<div style="font-family: Inter, Arial, sans-serif; padding:60px; text-align:center; background:#fff; color:#111;">
  <div style="max-width:900px; margin:0 auto; border:6px solid #F3E8FF; border-radius:18px; padding:48px;">
    <h2 style="font-size:28px; color:#7C3AED; margin-bottom:8px;">Sertifikat Penyelesaian</h2>
    <h1 style="font-size:36px; margin:8px 0 16px 0; font-weight:800;">{course_title}</h1>
    <p style="font-size:16px; margin-bottom:24px;">Diberikan kepada</p>
    <p style="font-size:28px; font-weight:700; margin-bottom:12px;">{name}</p>
    <p style="font-size:14px; color:#6B7280;">Telah menyelesaikan kursus pada {completion_date}</p>
    <div style="margin-top:28px; font-size:12px; color:#9CA3AF;">No. Sertifikat: {serial_number}</div>
  </div>
</div>',
                'background_image' => null,
                'config' => json_encode(['font' => 'Inter', 'title_color' => '#7C3AED']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Classic Certificate Template',
                'html_content' => '<div style="font-family: Georgia, serif; padding:80px; background:#f9fafb; color:#111;">
  <div style="max-width:1000px; margin:0 auto; background:#fff; padding:40px 60px; border:1px solid #E5E7EB; box-shadow:0 6px 18px rgba(16,24,40,0.06);">
    <div style="display:flex; justify-content:space-between; align-items:center;">
      <div>
        <h1 style="font-size:30px; margin:0 0 8px 0;">{course_title}</h1>
        <p style="margin:0; color:#6B7280;">Sertifikat ini diberikan kepada</p>
        <p style="font-size:22px; font-weight:700; margin-top:10px;">{name}</p>
      </div>
      <div style="text-align:right; font-size:12px; color:#6B7280;">
        <div>Ditandatangani oleh</div>
        <div style="font-weight:700; margin-top:10px;">Tim Unlock</div>
      </div>
    </div>

    <div style="margin-top:36px; font-size:13px; color:#6B7280;">Tanggal: {completion_date} &nbsp; &nbsp; No: {serial_number}</div>
  </div>
</div>',
                'background_image' => null,
                'config' => json_encode(['font' => 'Georgia', 'style' => 'classic']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
