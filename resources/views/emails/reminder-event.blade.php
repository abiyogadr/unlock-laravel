<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pendaftaran Unlock Indonesia</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #334155; margin: 0; padding: 0; background-color: #f8fafc; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f8fafc; padding-bottom: 40px; }
        .main-content { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; margin-top: 20px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(to bottom right, #4c1d95, #6b21a8); padding: 20px 20px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.025em; }
        .content { padding: 32px 24px; }
        .greeting { font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 16px; }
        .info-card { background-color: #f1f5f9; border-left: 4px solid #f97316; padding: 20px; border-radius: 8px; margin: 24px 0; }
        .info-table td { padding: 4px 0; font-size: 14px; }
        .label { color: #64748b; font-weight: 500; width: 120px; }
        .value { color: #1e293b; font-weight: 700; }
        .btn-container { text-align: center; margin-top: 32px; }
        .button { background-color: #f97316; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 12px; font-weight: 700; font-size: 14px; display: inline-block; box-shadow: 0 10px 15px -3px rgba(249, 115, 22, 0.3); margin: 0 4px 8px 0; }
        .zoom-btn { background-color: #2563eb !important; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3) !important; }
        .materi-btn { background-color: #059669 !important; box-shadow: 0 10px 15px -3px rgba(5, 150, 105, 0.3) !important; }
        .footer { text-align: center; padding: 24px; font-size: 12px; color: #94a3b8; }
        .social-link { color: #6366f1; text-decoration: none; margin: 0 8px; }
        .content p { margin: 0 0 16px 0; padding: 0; display: block;}
        .event-info { background-color: #ecfdf5; border-left: 4px solid #10b981; padding: 20px; border-radius: 8px; margin: 24px 0; }
        .access-grid { display: grid; grid-template-columns: 1fr; gap: 12px; margin: 24px 0; }
        @media (min-width: 480px) { .access-grid { grid-template-columns: 1fr 1fr; } }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1>Unlock Indonesia</h1>
            </div>

            <div class="content">
                <p class="greeting">Halo, {{ $reg->name }}!</p>
                <p>Pendaftaran Anda ke <strong>{{ $reg->event->event_title }}</strong> telah berhasil. Berikut informasi lengkap untuk mengikuti acara:</p>

                <!-- Info Registrasi -->
                <div class="info-card">
                    <table class="info-table" width="100%">
                        <tr>
                            <td class="label">Kode Registrasi</td>
                            <td class="value">: {{ $reg->registration_code }}</td>
                        </tr>
                        <tr>
                            <td class="label">Judul Event</td>
                            <td class="value">: {{ $reg->event->event_title }}</td>
                        </tr>
                        <tr>
                            <td class="label">Status</td>
                            <td class="value">: <span style="color: #10b981;">Berhasil (Lunas)</span></td>
                        </tr>
                        <tr>
                            <td class="label">Waktu Daftar</td>
                            <td class="value">: {{ $reg->created_at->format('d M Y, H:i') }} WIB</td>
                        </tr>
                    </table>
                </div>

                <!-- Info Event -->
                <div class="event-info">
                    <h3 style="margin: 0 0 12px 0; color: #065f46; font-size: 16px; font-weight: 700;">📅 Detail Acara</h3>
                    <table class="info-table" width="100%">
                        <tr>
                            <td class="label">Tanggal</td>
                            <td class="value">: {{ $reg->event->date_start->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="label">Waktu</td>
                            <td class="value">: {{ $reg->event->time_start }} - {{ $reg->event->time_end }} WIB</td>
                        </tr>
                    </table>
                </div>

                <!-- Akses Link Zoom & Materi -->
                @if($reg->event->link_zoom || $reg->event->link_materi)
                <div style="background-color: #f0f9ff; border-left: 4px solid #0ea5e9; padding: 20px; border-radius: 8px; margin: 24px 0;">
                    <h3 style="margin: 0 0 16px 0; color: #0369a1; font-size: 16px; font-weight: 700;">🔗 Akses Acara</h3>
                    <div class="access-grid">
                        @if($reg->event->link_zoom)
                        <a href="{{ $reg->event->link_zoom }}" target="_blank" class="button zoom-btn">
                            <i style="font-style: normal;">📹</i> Gabung Zoom Meeting
                        </a>
                        @endif
                        
                        @if($reg->event->link_materi)
                        <a href="{{ $reg->event->link_materi }}" target="_blank" class="button materi-btn">
                            <i style="font-style: normal;">📚</i> Virtual BG & Materi
                        </a>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Google Calendar -->
                <div style="margin: 30px 0; border: 1px dashed #cbd5e1; padding: 20px; border-radius: 12px; text-align: center;">
                    <a href="{{ $calendarUrl }}" target="_blank" 
                       style="background-color: #ffffff; color: #4c1d95; border: 2px solid #4c1d95; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 700; font-size: 14px; display: inline-block;">
                        <img src="https://www.gstatic.com/calendar/images/dynamiclogo_2020q4/calendar_31_2x.png" width="18" style="vertical-align: middle; margin-right: 8px;">
                        Tambahkan ke Google Calendar
                    </a>
                </div>

                <p style="font-size: 14px; color: #64748b;">Simpan email ini untuk referensi. Jika ada kendala akses, hubungi tim support kami.</p>

                <div class="btn-container">
                    <a href="{{ route('event.index') }}" class="button">Lihat Event Lainnya</a>
                </div>
            </div>

            <div class="footer">
                <p>Tetap terhubung dengan kami:</p>
                <p>
                    <a href="https://instagram.com/unlock.indonesia" class="social-link">Instagram</a> • 
                    <a href="https://wa.me/6285176767623" class="social-link">WhatsApp Support</a>
                </p>
                <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
                <p>&copy; {{ date('Y') }} Unlock Indonesia. Seluruh hak cipta dilindungi.</p>
            </div>
        </div>
    </div>
</body>
</html>
