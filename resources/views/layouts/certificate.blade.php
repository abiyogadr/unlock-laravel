<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unlock Sertifikat - {{ $participant_name ?? 'Verification' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    {{-- Fonts & Icons --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;900&family=Great+Vibes&family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" crossorigin="anonymous">

    {{-- LOAD VITE: Pastikan resources/css/app.css berisi @tailwind --}}
    @vite(['resources/css/app.css', 'resources/js/certificate.js'])
    
    @stack('template_css')
</head>
<body class="bg-slate-100 m-0 p-0 flex flex-col items-center min-h-screen font-sans">

    <main class="flex flex-col items-center w-full max-w-full px-2 sm:px-5 py-6 box-border">
        <div class="flex flex-col items-center w-full">
            
            {{-- 1. IMAGE PREVIEW (Elemen ini tetap tampil di Admin & User) --}}
            <div id="cert-image-wrapper" class="w-full max-w-[297mm] bg-white rounded-sm shadow-2xl overflow-hidden mb-4">
                <div id="cert-loading" class="p-10 text-center text-slate-500 font-sans">
                    {{ request()->routeIs('admin.*') ? 'Menyiapkan Preview...' : 'Memuat Sertifikat...' }}
                </div>
            </div>

            {{-- 2. ACTION BAR (Hanya tampil jika BUKAN route admin preview) --}}
            @if(!request()->routeIs('admin.*'))
                @include('certificates.partials.action-bar')
            @endif

            {{-- 3. SOURCE (Selalu tersembunyi, hanya sebagai sumber render) --}}
            <div id="cert-source-wrapper" class="absolute -left-[99999px] top-0 w-[1122.5px] h-[793.7px]">
                <div id="cert-scale-container" class="relative w-[1122.5px] h-[793.7px]">
                    <div class="absolute top-0 left-0 w-[297mm] h-[210mm] flex justify-center bg-white overflow-hidden shadow-2xl origin-top-left" id="certificate">
                        <div class="relative w-full h-full text-[#333]">
                            @yield('certificate_content')
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html-to-image/1.11.11/html-to-image.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" crossorigin="anonymous"></script>

{{-- Tambahkan di bagian paling bawah sebelum </body> --}}
<script>
    window.addEventListener('message', function(event) {
        if (event.data.type === 'UPDATE_SIGNATURE') {
            const sigImages = document.querySelectorAll('.sig-img');
            sigImages.forEach(img => {
                img.src = event.data.base64;
                img.style.display = 'block';
            });
            
            if (typeof renderCertificateToImage === 'function') {
                setTimeout(renderCertificateToImage, 500);
            }
        }
    });
</script>
</body>
</html>
