@php
    // Tentukan file CSS berdasarkan template dari database
    $cssTemplate = 'resources/css/certificate_template' . $template . '.css';
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unlock Sertifikat - {{ $participant_name }}</title>
    
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    {{-- Fonts & Icons --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;900&family=Great+Vibes&family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    {{-- CSS Global (navbar, footer, container, dll) + CSS Sertifikat --}}
    @vite([$cssTemplate, 'resources/js/certificate.js'])
</head>
<body>

    {{-- Konten Utama --}}
    <main class="main-content">
        <div class="container" style="max-width: 1100px;">
            <div class="form-card" style="padding: 1rem;">
                
                <div class="main-layout">
                    
                    {{-- IMG HASIL RENDER --}}
                    <div id="cert-image-wrapper">
                        <div id="cert-loading">Memuat sertifikat...</div>
                    </div>

                    {{-- SUMBER SERTIFIKAT (TERSEMBUNYI) --}}
                    <div id="cert-source-wrapper">
                        <div id="cert-scale-container">
                            <div class="certificate-wrapper" id="certificate">
                                <div class="certificate-paper">
                                    
                                    {{-- Ornament --}}
                                    <div class="bg-texture"></div>
                                    <div class="wave-bg"></div>
                                    <div class="wave-bg-2"></div>
                                    <div class="shape tl-capsule"></div>
                                    <div class="shape tl-stripe"></div>
                                    <div class="shape tl-circle"></div>
                                    <div class="shape br-capsule"></div>
                                    <div class="shape br-stripe"></div>
                                    <div class="shape br-dots">
                                        <div class="dot"></div>
                                        <div class="dot"></div>
                                        <div class="dot"></div>
                                    </div>

                                    {{-- Konten Sertifikat --}}
                                    <div class="content-container">
                                        
                                        <div class="logo-area">
                                            <img src="{{ asset('assets/images/logo-unlock.png') }}" 
                                                 onerror="this.style.display='none'" 
                                                 alt="UNLOCK" 
                                                 class="logo-img">
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
                                                {{ $date_extra }}
                                            </div>
                                        </div>

                                        {{-- TANDA TANGAN --}}
                                         @if($sign_path) === 1
                                        <div class="signature-block">
                                            <img src="{{ asset('assets/images/' . $sign_path) }}" alt="{{ $speaker_name }}" class="sig-img">
                                            <div class="sig-line"></div>
                                            <span class="sig-name">{{ $speaker_name }}</span>
                                            <span class="sig-role">Pembicara</span>
                                        </div>
                                        @endif

                                        {{-- QR CODE + INFO --}}
                                        <div class="qr-block {{ empty($sign_path) ? 'qr-left' : '' }}">
                                            <div id="qrcode-container" data-qr-text="{{ $credential_url }}"></div>
                                            <div class="qr-text">
                                                Certificate ID: <strong>{{ $certificate_id }}</strong><br>
                                                Verify at: {{ $verify_url }}
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ACTION BAR --}}
                    <div class="action-bar">
                        
                        <div class="ab-section" id="section-url">
                            <div class="ab-label">Certificate URL</div>
                            <div class="url-group">
                                <input type="text" class="url-input h-uniform" value="{{ $credential_url }}" readonly>
                                <button class="btn-copy h-uniform" id="btnCopy">
                                    <i class="far fa-copy"></i> Copy
                                </button>
                            </div>
                        </div>

                        <div class="ab-section" id="section-download">
                            <div class="ab-label">Download</div>
                            <button class="btn-download h-uniform" id="downloadBtn">
                                <i class="fas fa-download"></i> Download
                            </button>
                        </div>

                    </div>
                
                </div>
            </div>
        </div>
    </main>

    {{-- Script eksternal (tidak dibundle Vite) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</body>
</html>
