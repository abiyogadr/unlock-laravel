@php
    // Berikan default value jika variabel tidak terkirim dari controller
    $currentTemplate = $template ?? '1';
    $has_sign = $has_sign ?? false;
    $cssTemplate = 'resources/css/certificate_template' . $currentTemplate . '.css';
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unlock Sertifikat - {{ $participant_name ?? 'Preview' }}</title>
    
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    {{-- Fonts & Icons --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;900&family=Great+Vibes&family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    {{-- CSS Global + CSS Sertifikat --}}
    @vite([$cssTemplate, 'resources/js/certificate.js'])

    <style>
        /* PAKSA TAMPIL: Untuk mode preview di dalam iframe admin */
        #cert-source-wrapper { 
            display: block !important; 
            visibility: visible !important; 
            opacity: 1 !important; 
            position: relative !important;
            left: 0 !important;
        }
        #cert-image-wrapper { display: none !important; } 
        
        /* Ribbon Preview */
        .preview-ribbon {
            position: fixed; top: 20px; right: -35px; background: #f59e0b; color: white;
            padding: 5px 40px; font-weight: 900; transform: rotate(45deg); z-index: 9999;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2); font-size: 12px;
        }

        /* Responsive Scale untuk Iframe */
        body { background-color: #f8fafc; overflow-x: hidden; }
        .certificate-wrapper { transform-origin: top center; }
    </style>
</head>
<body>
    <div class="preview-ribbon">PREVIEW</div>

    <main class="main-content">
        <div class="container" style="max-width: 100%; padding: 0;">
            <div class="main-layout">
                
                <div id="cert-source-wrapper">
                    <div id="cert-scale-container">
                        <div class="certificate-wrapper" id="certificate">
                            <div class="certificate-paper">
                                
                                {{-- Ornament Background --}}
                                <div class="bg-texture"></div>
                                <div class="wave-bg"></div>
                                <div class="wave-bg-2"></div>
                                <div class="shape tl-capsule"></div>
                                <div class="shape tl-stripe"></div>
                                <div class="shape tl-circle"></div>
                                <div class="shape br-capsule"></div>
                                <div class="shape br-stripe"></div>
                                <div class="shape br-dots">
                                    <div class="dot"></div><div class="dot"></div><div class="dot"></div>
                                </div>

                                {{-- Konten Sertifikat --}}
                                <div class="content-container">
                                    <div class="logo-area">
                                        <img src="{{ asset('assets/images/logo-unlock.png') }}" onerror="this.style.display='none'" class="logo-img">
                                    </div>
                                    
                                    <div class="top-section">
                                        <h1 class="cert-header">Certificate of Appreciation</h1>
                                        <div class="presented-label">Presented to:</div>
                                        <div class="recipient-name" id="participant_name">{{ $participant_name }}</div>
                                    </div>

                                    <div class="webinar-info">
                                        <div class="webinar-badge">{{ $webinar_name }}</div>
                                        <div class="main-title">{{ $main_title }}</div>
                                        <div class="sub-title">{{ $sub_title }}</div>
                                        
                                        <div class="presenter-box">
                                            <div class="presenter-intro">Dengan pemateri:</div>
                                            <div class="presenter-name">{{ $speaker_name }}</div>
                                            <div class="presenter-titles">{{ $speaker_titles }}</div>
                                        </div>

                                        <div class="event-info">
                                            Pada: <strong>{{ $event_date }}</strong> <br>
                                            <span style="color: #ff6407; font-style: italic;">{{ $date_extra }}</span>
                                        </div>
                                    </div>

                                    {{-- TANDA TANGAN: Hanya tampil jika toggle has_sign aktif --}}
                                    @if($has_sign)
                                    <div class="signature-block">
                                        @if($sign_path)
                                            <img src="{{ asset('storage/' . $sign_path) }}" alt="{{ $speaker_name }}" class="sig-img">
                                        @else
                                            <div style="height: 70px;"></div> {{-- Spacer jika path kosong --}}
                                        @endif
                                        <div class="sig-line"></div>
                                        <span class="sig-name">{{ $speaker_name }}</span>
                                        <span class="sig-role">{{ $speaker_titles ? 'Pembicara' : '' }}</span>
                                    </div>
                                    @endif

                                    {{-- QR CODE --}}
                                    <div class="qr-block {{ !$has_sign ? 'qr-left' : '' }}">
                                        <div id="qrcode-container" data-qr-text="{{ $credential_url }}"></div>
                                        <div class="qr-text">
                                            Certificate ID: <strong>{{ $certificate_id }}</strong><br>
                                            Verify at: {{ $verify_url }}
                                        </div>
                                    </div>
                                </div> {{-- End Content Container --}}
                            </div> {{-- End Certificate Paper --}}
                        </div> {{-- End Certificate Wrapper --}}
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // Fungsi Scale Otomatis agar muat di Iframe Admin
        function autoScale() {
            const wrapper = document.querySelector('.certificate-wrapper');
            const container = document.body;
            if (!wrapper) return;
            
            // Ukuran asli sertifikat A4 Landscape dalam pixel (kira-kira)
            const nativeWidth = 1122; 
            const scale = container.offsetWidth / nativeWidth;
            
            wrapper.style.transform = `scale(${scale * 0.95})`; // 0.95 untuk memberi sedikit padding
        }

        window.onload = function() {
            // 1. Generate QR Code
            var qrContainer = document.getElementById("qrcode-container");
            if(qrContainer) {
                new QRCode(qrContainer, {
                    text: qrContainer.getAttribute("data-qr-text"),
                    width: 75,
                    height: 75,
                    correctLevel : QRCode.CorrectLevel.H
                });
            }

            // 2. Jalankan Auto Scale
            autoScale();
        };

        window.onresize = autoScale;
    </script>
</body>
</html>
