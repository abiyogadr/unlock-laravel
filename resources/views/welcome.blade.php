@extends('layouts.app')

@section('title', 'Unlock Indonesia - Webinar & Pelatihan')

@section('content')

    <!-- 1. Hero Section (Compact Slider) -->
    <section class="w-full bg-gradient-to-br from-primary to-purple-800 text-white py-12 md:py-18">
        <div class="container mx-auto px-4 max-w-7xl">
            <div class="swiper heroSwiper overflow-hidden">
                <div class="swiper-wrapper">
                    
                    {{-- Slide 1: Event Terbaru --}}
                    @if($events->first())
                    <div class="swiper-slide">
                        <div class="flex flex-col lg:flex-row items-center gap-2">
                            <div class="lg:w-7/12 text-center lg:text-left">
                                <h1 class="text-lg md:text-3xl font-bold mb-4 md:mb-10 opacity-90 leading-relaxed">
                                    Unlock.co.id menghadirkan webinar dan E-Course terbaik dengan praktisi industri.
                                </h1>
                                <p class="text-base md:text-lg font-light mb-6 opacity-90">
                                    Daftarkan dirimu segera di webinar terbaru kami dan tingkatkan skill profesionalmu bersama mentor berpengalaman.
                                </p>
                                <div class="flex flex-wrap justify-center lg:justify-start gap-3 mb-6">
                                    {{-- Tombol Daftar Sekarang --}}
                                    @php
                                        $latestEvent = $events->where('status', 'open')->first() 
                                                    ?? $events->sortByDesc('date_start')->first();
                                    @endphp
                                    <a href="{{ route('event.show', $latestEvent->event_code) }}"
                                    class="inline-flex items-center px-6 py-3 bg-secondary text-white text-sm font-bold rounded-xl shadow-lg hover:bg-orange-600 transition">
                                        Daftar Sekarang 
                                        <i class="fas fa-chevron-right ml-2 text-xs"></i>
                                    </a>

                                    {{-- Tombol Semua Jadwal --}}
                                    <a href="{{ route('event.index') }}"
                                    class="inline-flex items-center px-6 py-3 bg-secondary text-white text-sm font-bold rounded-xl shadow-lg hover:bg-orange-600 transition">
                                        Semua Jadwal 
                                        <i class="fas fa-chevron-right ml-2 text-xs"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="lg:w-5/12 w-full relative">
                                @if($latestEvent)
                                    <a href="{{ route('event.show', $latestEvent->event_code) }}" class="relative group">
                                        {{-- Decorative background elements --}}
                                        <div class="absolute -inset-1 bg-gradient-to-r from-secondary to-purple-500 rounded-3xl blur opacity-30 group-hover:opacity-50 transition duration-1000"></div>
                                        
                                        <div class="relative bg-white/5 backdrop-blur-xl p-3 rounded-[2rem] border border-white/20 shadow-2xl">
                                            <div class="aspect-video overflow-hidden rounded-[1.5rem] relative">
                                                <div class="absolute hidden sm:block top-3 right-3 flex justify-center px-2 py-2 text-xs font-semibold rounded-full bg-secondary/90">
                                                    <p class="text-white font-bold">Event Baru</p>
                                                </div>
                                                @if($latestEvent->kv_path)
                                                    <img src="{{ asset('storage/'.$latestEvent->kv_path) }}" 
                                                        alt="{{ $latestEvent->event_title }}"
                                                        class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full bg-white/10 flex items-center justify-center">
                                                        <i class="fas fa-image text-5xl text-white/20"></i>
                                                    </div>
                                                @endif

                                                {{-- Informasi Singkat di Atas Gambar --}}
                                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent flex flex-col justify-end p-4">
                                                    <h3 class="text-md md:text-xl font-bold text-white line-clamp-2">
                                                        {{ $latestEvent->event_title }}
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                @else
                                    {{-- Placeholder jika tidak ada event --}}
                                    <div class="relative bg-white/5 border border-white/20 rounded-[2rem] p-12 text-center backdrop-blur-sm">
                                        <i class="fas fa-laptop-code text-6xl text-white/20 mb-4"></i>
                                        <p class="text-white/60">Siapkan dirimu untuk webinar inspiratif berikutnya!</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Slide 2: Promo / Welcome Banner --}}
                    <!-- <div class="swiper-slide">
                        <div class="flex flex-col lg:flex-row items-center gap-8">
                            <div class="lg:w-7/12 text-center lg:text-left">
                                <h1 class="text-3xl md:text-4xl font-bold mb-4 leading-tight">
                                    Selamat Datang di Unlock Indonesia
                                </h1>
                                <p class="text-base md:text-lg font-light mb-8 opacity-90">
                                    Platform webinar & pelatihan online terbaik untuk membantu Anda membuka seluruh potensi yang dimiliki.
                                </p>
                                <a href="{{ route('registration.create') }}"
                                class="bg-white text-primary px-6 py-3 rounded-xl font-bold text-sm hover:bg-gray-100 transition shadow-lg inline-flex items-center">
                                    Gabung Sekarang <i class="fas fa-user-plus ml-2 text-xs"></i>
                                </a>
                            </div>
                            <div class="lg:w-4/12 flex justify-center">
                                {{-- Placeholder/Asset Image untuk Banner Umum --}}
                                <div class="w-full max-w-[320px] aspect-square rounded-2xl bg-white/10 border-4 border-white/10 flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-graduation-cap text-6xl text-white/30"></i>
                                </div>
                            </div>
                        </div>
                    </div> -->

                </div>

                {{-- Paginasi Bulat Bawah --}}
                <div class="hero-pagination !relative !mt-8 flex justify-center lg:justify-start"></div>
            </div>
        </div>
    </section>

    <!-- 2. Open Events Section (Full Width Background Gray) -->
    <section class="w-full py-8 md:py-16 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-4">
                Event yang Sedang Dibuka
            </h2>
            <p class="text-center text-gray-600 mb-12">
                Pilih webinar atau pelatihan yang sesuai dengan kebutuhanmu
            </p>

            @if($events->count())
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($events as $index => $event)
                        <article
                            class="group {{ $index >= 3 ? 'hidden md:block' : '' }} bg-white rounded-2xl border border-gray-100 shadow-sm
                                hover:shadow-xl hover:-translate-y-1 transition-all duration-300
                                overflow-hidden flex flex-col">

                            {{-- KV --}}
                            <div class="relative bg-gray-100 overflow-hidden">
                                @if($event->kv_path)
                                    <img src="{{ asset('storage/'.$event->kv_path) }}"
                                        alt="{{ $event->event_title }}"
                                        class="aspect-video object-cover transition-transform duration-500
                                                group-hover:scale-105">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center
                                                bg-gradient-to-br from-gray-50 to-gray-200">
                                        <i class="fas fa-image text-3xl text-gray-300 mb-2"></i>
                                        <span class="text-xs text-gray-400">Tidak ada gambar</span>
                                    </div>
                                @endif

                                {{-- Status badge on image --}}
                                <span class="absolute top-3 right-3 px-2 py-1 text-3xs font-semibold rounded-full
                                            {{ $event->status === 'open'
                                                ? 'bg-green-500/90 text-white'
                                                : 'bg-gray-500/90 text-white' }}">
                                    {{ strtoupper($event->status) }}
                                </span>
                            </div>

                            <div class="p-5 flex flex-col flex-grow">
                                <h3 class="text-base font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-primary transition">
                                    {{ $event->event_title }}
                                </h3>

                                <div class="text-xs text-gray-500 mb-3 space-y-1.5">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-alt w-4 mr-2"></i>
                                        <span>{{ $event->date_start->format('d M Y') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-clock w-4 mr-2"></i>
                                        <span>{{ $event->time_start }} – {{ $event->time_end }} WIB</span>
                                    </div>
                                </div>

                                <div class="text-xs text-gray-600 mb-5 flex items-start">
                                    <i class="fas fa-microphone w-4 mr-2 mt-0.5"></i>
                                    <span class="line-clamp-2">
                                        {{ $event->speakers->pluck('speaker_name')->implode(', ') }}
                                    </span>
                                </div>

                                <div class="mt-auto">
                                    <a href="{{ route('event.show', $event->event_code) }}"
                                    class="block w-full text-center text-sm font-semibold
                                            bg-primary text-white py-2.5 rounded-xl
                                            hover:bg-purple-900 transition">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="text-center text-gray-500 py-16">Belum ada event yang dibuka.</div>
            @endif
            <a href="{{ route('event.index') }}"
            class="bg-secondary text-white px-6 py-4 mt-12 rounded-xl font-bold text-sm
                    hover:bg-orange-600 transition shadow-lg
                    flex items-center justify-center
                    w-fit mx-auto">
                Semua Jadwal <i class="fas fa-chevron-right ml-2 text-xs"></i>
            </a>
        </div>
    </section>

    <!-- 3. Features Section (Full Width Background White) -->
    <section class="w-full py-8 md:py-16 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-6 md:mb-12">
                Mengapa Memilih Unlock Indonesia?
            </h2>
            <div class="grid md:grid-cols-3 gap-4 md:gap-8">
                <div class="text-center p-3 md:p-6 rounded-xl hover:shadow-lg transition duration-300 bg-white">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-video text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Webinar Berkualitas</h3>
                    <p class="text-gray-600">Pembicara ahli dan materi terkini untuk meningkatkan skill Anda secara signifikan.</p>
                </div>
                <div class="text-center p-6 rounded-xl hover:shadow-lg transition duration-300 bg-white">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-certificate text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Sertifikat Resmi</h3>
                    <p class="text-gray-600">Dapatkan sertifikat resmi yang dapat dijadikan portofolio profesional Anda.</p>
                </div>
                <div class="text-center p-6 rounded-xl hover:shadow-lg transition duration-300 bg-white">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Komunitas Aktif</h3>
                    <p class="text-gray-600">Bergabung dengan komunitas pembelajar yang saling support dan berkolaborasi.</p>
                </div>
            </div>
        </div>
    </section>
        <!-- Section: Topik Profesional & Relevansi Industri -->
    <section class="w-full py-8 md:py-16 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">
                    Topik Belajar yang Selalu Relevan & Dinamis
                </h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Kami memahami bahwa standar industri terus berkembang. Karena itu, kami menghadirkan materi yang tidak hanya mendalam secara teori, tetapi juga adaptif terhadap perubahan dunia profesional.
                </p>
            </div>

            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Image Side -->
                <div class="relative order-2 lg:order-1 px-4">
                    <div class="absolute -inset-x-[2px] top-0 bottom-0 bg-primary/5 rounded-2xl transform rotate-4"></div>
                    <img src="{{ asset('assets/images/brain.jpg') }}" 
                         alt="Pengembangan Profesional" 
                         class="relative rounded-2xl shadow-xl w-full h-[200px] md:h-[360px] object-cover">
                </div>

                <!-- Text Side -->
                <div class="space-y-6 order-2">
                    <h3 class="text-2xl font-bold text-gray-800 leading-tight">
                        Mulai dari Finansial hingga Ekspansi Keilmuan Lainnya
                    </h3>
                    <p class="text-gray-600 leading-relaxed">
                        Saat ini, kami berfokus pada penguatan kompetensi di bidang **Akuntansi, Perpajakan, dan Audit** untuk menjawab kebutuhan kepatuhan dan akurasi bisnis yang krusial. Namun, komitmen kami tidak berhenti di situ.
                    </p>

                    <ul class="space-y-4">
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center mr-3 mt-1">
                                <i class="fas fa-check-circle text-xs text-primary"></i>
                            </div>
                            <div>
                                <span class="font-semibold text-gray-800">Fondasi Keuangan Kokoh:</span>
                                <p class="text-gray-600 text-sm">Menyajikan update terbaru mengenai regulasi, standar pelaporan, dan kepatuhan pajak yang berlaku di Indonesia.</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center mr-3 mt-1">
                                <i class="fas fa-sync text-xs text-primary"></i>
                            </div>
                            <div>
                                <span class="font-semibold text-gray-800">Kurikulum yang Terus Bertumbuh:</span>
                                <p class="text-gray-600 text-sm">Secara bertahap memperluas cakupan topik ke bidang manajemen, teknologi, dan disiplin ilmu profesional lainnya.</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center mr-3 mt-1">
                                <i class="fas fa-chart-line text-xs text-primary"></i>
                            </div>
                            <div>
                                <span class="font-semibold text-gray-800">Navigasi Perubahan Dunia:</span>
                                <p class="text-gray-600 text-sm">Setiap topik dirancang untuk membekali Anda dengan *skill* yang relevan terhadap tren pasar global saat ini dan masa depan.</p>
                            </div>
                        </li>
                    </ul>

                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-sm italic text-gray-500">
                            "Misi kami adalah menjadi platform belajar satu pintu untuk seluruh kebutuhan pengembangan profesional Anda."
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="w-full py-8 md:py-16 bg-white">
        <div class="container mx-auto text-center px-4 sm:px-6 lg:px-8 max-w-7xl">
            <h2 class="text-3xl font-bold text-gray-800 mb-8">
                Hubungi Kami
            </h2>
            <p class="text-lg text-gray-600 mb-8">
                Tetap terhubung dengan Unlock Indonesia untuk update webinar terbaru dan tips belajar eksklusif.
            </p>
            <div class="flex flex-wrap justify-center gap-4 md:gap-12">
                <a href="https://instagram.com/unlock.indonesia" target="_blank"
                   class="flex items-center bg-white px-6 py-4 rounded-lg shadow-md hover:shadow-lg transition duration-300 hover:text-primary">
                    <i class="fab fa-instagram text-2xl mr-3 text-pink-600"></i>
                    <div class="text-left">
                        <div class="font-semibold">Instagram</div>
                        <div class="text-sm text-gray-500">@unlock.indonesia</div>
                    </div>
                </a>
                <a href="https://youtube.com/@unlockyoutubechannel" target="_blank"
                   class="flex items-center bg-white px-6 py-4 rounded-lg shadow-md hover:shadow-lg transition duration-300 hover:text-primary">
                    <i class="fab fa-youtube text-2xl mr-3 text-red-600"></i>
                    <div class="text-left">
                        <div class="font-semibold">YouTube</div>
                        <div class="text-sm text-gray-500">Unlock YouTube Channel</div>
                    </div>
                </a>
                <a href="https://wa.me/6285176767623" target="_blank"
                   class="flex items-center bg-white px-6 py-4 rounded-lg shadow-md hover:shadow-lg transition duration-300 hover:text-primary">
                    <i class="fab fa-whatsapp text-2xl mr-3 text-green-600"></i>
                    <div class="text-left">
                        <div class="font-semibold">WhatsApp</div>
                        <div class="text-sm text-gray-500">0851-7676-7623</div>
                    </div>
                </a>
            </div>
        </div>
    </section>

@endsection
