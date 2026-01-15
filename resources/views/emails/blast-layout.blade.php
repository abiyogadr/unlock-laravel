<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        /* Base Reset untuk Email */
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; background-color: #f8fafc; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f8fafc; padding-bottom: 40px; }
        .main-content { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        
        /* Header dengan Gradient */
        .header { background: linear-gradient(to bottom right, #4c1d95, #6b21a8); padding: 25px 20px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -0.025em; color: #ffffff; }
        
        /* Area Konten Utama */
        .content { padding: 32px 24px; color: #334155; font-size: 16px; }

        /* OPSI 1: Paragraf Biasa (P) -> UNTUK TEKS RAPAT [web:7] */
        .content p { 
            margin: 0 0 2px 0 !important; 
            padding: 0; 
            line-height: 1.6;
        }

        /* OPSI 2: Heading 6 (H6) -> UNTUK TEKS RENGGANG [web:119] */
        .content h6 { 
            font-size: 16px !important; 
            font-weight: normal !important; 
            margin: 0 0 18px 0 !important; 
            color: #334155 !important;
            line-height: 1.6;
        }

        /* Headings (H1 - H5) dengan Spasi yang Bagus [web:116][web:123] */
        .content h1 { font-size: 28px; font-weight: 800; margin: 24px 0 12px 0 !important; color: #1e1b4b; line-height: 1.2; }
        .content h2 { font-size: 22px; font-weight: 700; margin: 20px 0 10px 0 !important; color: #1e1b4b; line-height: 1.2; }
        .content h3 { font-size: 19px; font-weight: 700; margin: 18px 0 8px 0 !important; color: #1e1b4b; }
        .content h4 { font-size: 17px; font-weight: 700; margin: 15px 0 6px 0 !important; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; }
        .content h5 { font-size: 16px; font-weight: 700; margin: 12px 0 4px 0 !important; color: #64748b; }

        /* List & Image Styling */
        .content img { max-width: 100%; height: auto; border-radius: 12px; margin: 20px 0; display: block; }
        .content ul, .content ol { padding-left: 20px; margin: 10px 0 20px 0; }
        .content li { margin-bottom: 6px; }
        
        /* Footer */
        .footer { text-align: center; padding: 30px 24px; font-size: 13px; color: #94a3b8; background-color: #fcfdfe; border-top: 1px solid #f1f5f9; }
        .social-link { color: #6366f1; text-decoration: none; font-weight: 600; margin: 0 10px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-content">
            <div class="header">
                <h1>Unlock Indonesia</h1>
            </div>

            <div class="content">
                <!-- Data dari Quill Editor dimasukkan ke sini -->
                {!! $body !!}
            </div>

            <div class="footer">
                <p style="margin-bottom: 12px;">Tetap terhubung dengan kami:</p>
                <p>
                    <a href="https://instagram.com/unlock.indonesia" class="social-link">Instagram</a>
                    <span style="color: #cbd5e1;">&bull;</span>
                    <a href="https://wa.me/6285176767623" class="social-link">WhatsApp Support</a>
                </p>
                <div style="height: 1px; background-color: #e2e8f0; margin: 25px 0;"></div>
                <p style="font-size: 11px; line-height: 1.4;">
                    &copy; {{ date('Y') }} Unlock Indonesia. Seluruh hak cipta dilindungi.<br>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
