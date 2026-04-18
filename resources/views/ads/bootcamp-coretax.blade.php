{{--
    ADS LANDING PAGE: BOOTCAMP CORETAX
    Route: /ad/bootcamp-coretax
    Shell Blade â€” semua konten ada di resources/js/ads/BootcampCoretax.vue
--}}

@php
    $whatsappNumber = '6285176767623';
    $whatsappUrl    = 'https://wa.me/' . $whatsappNumber . '?text=' . urlencode('Halo, saya ingin daftar Bootcamp Coretax. Boleh minta info lengkapnya?');
    $pixelId        = config('services.meta.pixel_id');
    $event          = \App\Models\Event::where('event_code', 'bootcamp-coretax')->first();
    $authUser       = auth()->user();
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- SEO --}}
    <title>BOOTCAMP CORETAX | Kupas Tuntas SPT Tahunan OP &amp; Badan | Unlock</title>
    <meta name="description" content="Bootcamp online live Coretax. Kupas tuntas SPT Tahunan Orang Pribadi dan Badan era Coretax dari nol sampai submit. Mulai Rp125.000.">

    {{-- Open Graph --}}
    <meta property="og:title" content="BOOTCAMP CORETAX | Kupas Tuntas SPT Tahunan OP & Badan">
    <meta property="og:description" content="Bootcamp online live dan praktik SPT OP & Badan era Coretax bersama mentor profesional. Mulai Rp125.000 saja!">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/ad/bootcamp-coretax') }}">
    <meta property="og:locale" content="id_ID">

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    {{-- CSRF token untuk axios AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Config untuk Vue component --}}
    <script>
        window.__PAGE_CONFIG__ = {
            registrationUrl: '{{ url('/event/bootcamp-coretax') }}',
            whatsappUrl:     '{{ $whatsappUrl }}',
            pixelId:         '{{ $pixelId }}',  
            eventId:         193,
            packetId:        107,
            @if($authUser)
            user: {
                name:       @json($authUser->name),
                email:      @json($authUser->email),
                whatsapp:   @json($authUser->phone ? '0'.$authUser->phone : ''),
                gender:     @json($authUser->gender ?? ''),
                age:        {{ $authUser->birth_date ? \Carbon\Carbon::parse($authUser->birth_date)->age : 'null' }},
                profession: @json($authUser->job ?? ''),
                province:   @json($authUser->province ?? ''),
                city:       @json($authUser->city ?? '')
            },
            @else
            user: null,
            @endif
        };
    </script>

    {{-- Meta Pixel init (tetap di Blade agar PageView terpantau sebelum Vue load) --}}
    @if($pixelId)
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}
        (window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init','{{ $pixelId }}');
        fbq('track','PageView');
        fbq('track','ViewContent',{content_name:'Bootcamp Coretax',content_category:'Bootcamp',value:125000,currency:'IDR'});
    </script>
    <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1" alt=""></noscript>
    @endif

    @vite(['resources/css/app.css', 'resources/js/ads/bootcamp-coretax.js'])
</head>

<body class="bg-white text-gray-800 font-[var(--font-sans)] antialiased overflow-x-hidden">
    <div id="app-coretax"></div>
</body>
</html>
