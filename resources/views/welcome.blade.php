@extends('layouts.app')

@section('title', 'Unlock Indonesia - Webinar & Pelatihan')

@section('content')

    <!-- 1. Hero Section (Compact Slider) -->
    <section class="w-full bg-primary text-white pt-8 md:pt-10 pb-6 px-2 sm:px-0">
        <div class="container mx-auto px-2 sm:px-4 max-w-7xl">
            <div class="relative w-full">
                <div class="swiper heroSwiper overflow-hidden relative group]">
                    <div class="swiper-wrapper">
                        <!-- @php
                            $latestEvent = $events->where('status', 'open')->first() ?? $events->sortByDesc('date_start')->first();
                            $latestEventUrl = $latestEvent ? route('event.show', $latestEvent->event_code) : route('event.index');
                        @endphp

                        @if($events->first())

                        {{-- Slide 1: Desktop existing layout --}}
                        <div class="swiper-slide hidden md:block">
                            <div class="flex flex-col md:flex-row items-center gap-2">
                                <div class="lg:w-7/12 text-center lg:text-left">
                                    <h1 class="text-lg lg:text-2xl font-bold mb-2 lg:mb-10 opacity-90 leading-relaxed">
                                        Unlock.co.id menghadirkan webinar dan E-Course terbaik dengan praktisi industri.
                                    </h1>
                                    <p class="text-base md:text-lg font-light mb-2 md:mb-6 opacity-90">
                                        Daftarkan dirimu segera di webinar terbaru kami dan tingkatkan skill profesionalmu bersama mentor berpengalaman.
                                    </p>
                                    <div class="flex flex-wrap justify-center lg:justify-start gap-3 mb-6">
                                        <a href="{{ $latestEventUrl }}" class="inline-flex items-center px-6 py-3 bg-secondary text-white text-sm font-bold rounded-xl shadow-lg hover:bg-orange-600 transition">
                                            Daftar Sekarang
                                            <i class="fas fa-chevron-right ml-2 text-xs"></i>
                                        </a>
                                        <a href="{{ route('event.index') }}" class="inline-flex items-center px-6 py-3 bg-secondary text-white text-sm font-bold rounded-xl shadow-lg hover:bg-orange-600 transition">
                                            Semua Jadwal
                                            <i class="fas fa-chevron-right ml-2 text-xs"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="lg:w-5/12 w-[70%] relative hidden md:block">
                                    @if($latestEvent)
                                        <a href="{{ $latestEventUrl }}" class="relative group">
                                            <div class="absolute -inset-1 bg-gradient-to-r from-secondary to-purple-500 rounded-3xl blur opacity-30 group-hover:opacity-50 transition duration-1000"></div>
                                            <div class="relative bg-white/5 backdrop-blur-xl p-3 rounded-[2rem] border border-white/20 shadow-2xl">
                                                <div class="aspect-video overflow-hidden rounded-[1.5rem] relative">
                                                    <div class="absolute hidden md:block top-3 right-3 flex justify-center px-2 py-2 text-xs font-semibold rounded-full bg-secondary/90">
                                                        <p class="text-white font-bold">Event Baru</p>
                                                    </div>
                                                    @if($latestEvent->kv_path)
                                                        <img src="{{ asset('storage/'.$latestEvent->kv_path) }}" alt="{{ $latestEvent->event_title }}" class="w-full h-full object-cover">
                                                    @else
                                                        <div class="w-full h-full bg-white/10 flex items-center justify-center">
                                                            <i class="fas fa-image text-5xl text-white/20"></i>
                                                        </div>
                                                    @endif
                                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent flex flex-col justify-end p-4">
                                                        <h3 class="text-sm md:text-md lg:text-xl font-bold text-white line-clamp-2">
                                                            {{ $latestEvent->event_title }}
                                                        </h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    @else
                                        <div class="relative bg-white/5 border border-white/20 rounded-[2rem] p-12 text-center backdrop-blur-sm">
                                            <i class="fas fa-laptop-code text-6xl text-white/20 mb-4"></i>
                                            <p class="text-white/60">Siapkan dirimu untuk webinar inspiratif berikutnya!</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif -->

                        {{-- Slide 1: Unlock --}}
                        <div class="swiper-slide">
                            <a href="{{ route('registration.create') }}" class="flex items-center justify-center w-full">
                                <div class="w-full sm:aspect-[4/1] relative overflow-hidden">
                                    <img src="{{ asset('assets/images/promo/ka8jj25Jbnsk17hhaN.png') }}" alt="Unlock Indonesia" class="w-full h-full object-cover">
                                </div>
                            </a>
                        </div>

                        {{-- Slide 2: Promo Image --}}
                        <div class="swiper-slide">
                            <a href="{{ route('ecourse.catalog') }}" class="flex items-center justify-center w-full">
                                <div class="w-full sm:aspect-[4/1] relative overflow-hidden shadow-md border border-white/20">
                                    <img src="{{ asset('assets/images/promo/Kdnd7H4jad25Ndk.png') }}" alt="Diskon 20% Ecourse Package" class="w-full h-full object-cover">
                                </div>
                            </a>
                        </div>

                    </div>

                    {{-- Paginasi Bulat Bawah --}}
                    <div class="hero-pagination !relative !mt-4 flex justify-center lg:justify-start"></div>
                </div>
            </div>
        </div>
    </section>
    <style>
        .hero-pagination.swiper-pagination-bullets {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
        }

        .hero-pagination.swiper-pagination-bullets .swiper-pagination-bullet {
            width: 2.15rem;
            height: 0.45rem;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.24) !important;
            opacity: 1;
            transition: width 0.25s ease, background-color 0.25s ease;
        }

        .hero-pagination.swiper-pagination-bullets .swiper-pagination-bullet.swiper-pagination-bullet-active {
            width: 3rem;
            background: rgba(255, 255, 255, 0.98) !important;
        }
    </style>


    <!-- 1.5 E-Course Categories (Hero Style Background) -->
    <section class="w-full bg-gradient-to-b from-primary to-purple-800 text-white py-6 md:py-8 px-2 sm:px-0">
        <div class="container mx-auto px-4 max-w-7xl">
            <div class="flex flex-col md:flex-row items-center gap-3 md:gap-6">
                <div class="lg:w-4/12">
                    <h2 class="text-xl md:text-2xl font-bold mb-4">Unlock E-Course</h2>
                    <p class="text-xs md:text-sm text-white/90 mb-2">Pelajari topik-topik profesional secara mendalam melalui E-Course kami. Pilih kategori untuk menemukan kursus yang sesuai dengan kebutuhanmu.</p>
                </div>
                <div class="lg:w-8/12 w-full">
                    <div class="grid grid-cols-3 xl:grid-cols-5 gap-3">
                            <a href="{{ route('ecourse.catalog') }}" class="flex items-center gap-2 sm:gap-3 p-1.5 sm:p-3 bg-white/10 hover:bg-white/20 rounded-lg transition" aria-label="Semua Kategori">
                                <div class="min-w-10 min-h-10 rounded-md bg-white/10 flex items-center justify-center">
                                    <i class="fas fa-th-large text-white"></i>
                                </div>
                                <div class="text-left">
                                    <div class="font-semibold text-xs sm:text-sm">Semua Kategori</div>
                                </div>
                            </a>
                        @foreach($categories as $cat)
                            <a href="{{ route('ecourse.catalog', ['category' => $cat->slug]) }}" class="flex items-center gap-2 sm:gap-3 p-1.5 sm:p-3 bg-white/10 hover:bg-white/20 rounded-lg transition">
                                <div class="min-w-10 min-h-10 rounded-md bg-white/10 flex items-center justify-center">
                                    <i class="{{ $cat->icon }} text-white text-xl"></i>
                                </div>
                                <div class="text-left">
                                    <div class="font-semibold text-xs sm:text-sm">{{ $cat->name }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Open Events Section (Full Width Background Gray) -->
    <section class="w-full py-8 md:py-16 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
            <h2 class="text-2xl md:text-3xl font-bold text-center text-gray-800 mb-4">
                Event yang Sedang Dibuka
            </h2>
            <p class="text-center text-gray-600 mb-6 md:mb-12">
                Pilih webinar atau pelatihan yang sesuai dengan kebutuhanmu
            </p>

            @if($events->count())
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($events as $index => $event)
                        <x-event-card :event="$event" class="{{ $index >= 3 ? 'hidden md:block' : '' }}" />
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
