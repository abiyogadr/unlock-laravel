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
        .button { background-color: #f97316; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 12px; font-weight: 700; font-size: 14px; display: inline-block; box-shadow: 0 10px 15px -3px rgba(249, 115, 22, 0.3); }
        .footer { text-align: center; padding: 24px; font-size: 12px; color: #94a3b8; }
        .social-link { color: #6366f1; text-decoration: none; margin: 0 8px; }
        .content p { margin: 0 0 16px 0; padding: 0; display: block;}
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-content">
            <!-- Header dengan Gradient khas Unlock Indonesia -->
            <div class="header">
                <h1>Unlock Indonesia</h1>
                <!-- <p style="opacity: 0.9; font-size: 14px; margin-top: 8px;">Webinar & Pelatihan Online Terbaik</p> -->
            </div>

            <div class="content">
                <p class="greeting">Halo, {{ $registration->name }}!</p>
                <p>Pendaftaran Anda telah berhasil. Selamat bergabung di komunitas pembelajar Unlock Indonesia. Kami siap membantu Anda meningkatkan skill profesional bersama mentor berpengalaman.</p>

                <div class="info-card">
                    <table class="info-table" width="100%">
                        <tr>
                            <td class="label">Kode Registrasi</td>
                            <td class="value">: {{ $registration->registration_code }}</td>
                        </tr>
                        <tr>
                            <td class="label">Judul Event</td>
                            <td class="value">: {{ $registration->event_name }}</td>
                        </tr>
                        <tr>
                            <td class="label">Status</td>
                            <td class="value">: <span style="color: #10b981;">Berhasil (Lunas)</span></td>
                        </tr>
                        <tr>
                            <td class="label">Waktu Daftar</td>
                            <td class="value">: {{ $registration->created_at->format('d M Y, H:i') }} WIB</td>
                        </tr>
                    </table>
                </div>
                <div style="margin: 30px 0; border: 1px dashed #cbd5e1; padding: 20px; border-radius: 12px; text-align: center;">
                    <!-- <p style="margin-top: 0; font-weight: 600; font-size: 14px;">Ingin diingatkan saat acara dimulai?</p> -->
                    
                    <a href="{{ $calendarUrl }}" target="_blank" 
                    style="background-color: #ffffff; color: #4c1d95; border: 2px solid #4c1d95; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; font-size: 13px; display: inline-block;">
                        <img src="https://www.gstatic.com/calendar/images/dynamiclogo_2020q4/calendar_31_2x.png" width="16" style="vertical-align: middle; margin-right: 8px;">
                        Tambahkan ke Google Calendar
                    </a>
                </div>

                <p style="font-size: 14px; color: #64748b;">Informasi mengenai akses webinar (Zoom link) atau materi pelatihan akan kami kirimkan melalui email kembali atau WhatsApp menjelang hari pelaksanaan acara.</p>

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
