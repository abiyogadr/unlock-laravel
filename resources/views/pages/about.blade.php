@extends('layouts.app')

@section('title', 'Unlock - Tentang Kami')

@section('content')

    {{-- Hero Section --}}
    <section class="w-full bg-gradient-to-br from-primary to-purple-800 text-white py-20">
        <div class="container mx-auto px-4 text-center max-w-7xl">
            <h1 class="text-3xl md:text-5xl font-bold mb-6">Buka Seluruh Potensimu</h1>
            <p class="text-lg md:text-xl opacity-90 max-w-4xl mx-auto font-light leading-relaxed">
                Unlock.co.id adalah penyedia webinar dan pelatihan online terbaik. Kami ada untuk membantumu membuka seluruh potensi yang kamu punya.
            </p>
        </div>
    </section>

    {{-- Story Section --}}
    <section class="w-full py-20 bg-white">
        <div class="container mx-auto px-4 max-w-7xl">
            <div class="grid md:grid-cols-1 gap-12 items-center">
                <div>
                    <span class="text-primary font-bold tracking-widest text-2xl uppercase">Perjalanan Kami</span>
                    <!-- <h2 class="text-3xl font-bold text-gray-900 mt-2 mb-6">Sejak 2020 Menjadi Mitra Belajar Indonesia</h2> -->
                    <div class="space-y-4 text-gray-600 leading-relaxed text-lg mt-4">
                        <p>
                            Unlock Indonesia berdiri sejak 2020 sebagai mitra belajar bagi mahasiswa dan profesional di seluruh Indonesia. Kami percaya bahwa akses terhadap pembelajaran berkualitas harus mudah, relevan, dan berdampak langsung pada karier.
                        </p>
                        <p>
                            Dengan dukungan praktisi industri, akademisi, serta kolaborasi dengan kampus dan institusi profesional, Unlock terus berinovasi menghadirkan program belajar yang adaptif terhadap perkembangan dunia kerja.
                        </p>
                    </div>
                </div>
                <!-- <div class="relative">
                    <div class="absolute -top-4 -left-4 w-24 h-24 bg-primary/10 rounded-full -z-10"></div>
                    <img src="{{ asset('assets/images/about-illustration.svg') }}" 
                         alt="About Unlock" 
                         class="rounded-3xl shadow-2xl border border-gray-100 w-full object-cover">
                    <div class="absolute -bottom-6 -right-6 bg-secondary p-6 rounded-2xl shadow-xl text-white hidden md:block">
                        <div class="text-3xl font-bold">50+</div>
                        <div class="text-xs uppercase tracking-wider opacity-80">Partner Institusi</div>
                    </div>
                </div> -->
            </div>
        </div>
    </section>

    {{-- Vision & Mission --}}
    <section class="w-full py-20 bg-gray-50">
        <div class="container mx-auto px-4 max-w-7xl">
            <div class="grid md:grid-cols-3 gap-8">
                {{-- Visi --}}
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 transition-transform hover:-translate-y-2">
                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mb-6 text-primary text-2xl">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Visi Kami</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Menjadi platform akselerasi karir dan pengembangan diri nomor satu di Indonesia yang inklusif bagi semua kalangan.
                    </p>
                </div>

                {{-- Misi --}}
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 transition-transform hover:-translate-y-2">
                    <div class="w-14 h-14 bg-purple-50 rounded-2xl flex items-center justify-center mb-6 text-purple-600 text-2xl">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Misi Kami</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Menyediakan kurikulum berbasis industri yang praktis, aplikatif, dan dapat diakses kapan saja dan di mana saja.
                    </p>
                </div>

                {{-- Nilai --}}
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 transition-transform hover:-translate-y-2">
                    <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center mb-6 text-secondary text-2xl">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Nilai Kami</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Berinovasi secara konsisten dan mengutamakan keberhasilan serta pertumbuhan karir setiap member kami.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats Section --}}
    <!-- <section class="w-full py-16 bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 max-w-5xl">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-4xl font-bold text-primary">2020</div>
                    <div class="text-gray-500 text-sm mt-1">Tahun Berdiri</div>
                </div>
                <div>
                    <div class="text-4xl font-bold text-primary">100+</div>
                    <div class="text-gray-500 text-sm mt-1">Webinar Sukses</div>
                </div>
                <div>
                    <div class="text-4xl font-bold text-primary">10rb+</div>
                    <div class="text-gray-500 text-sm mt-1">Alumni Aktif</div>
                </div>
                <div>
                    <div class="text-4xl font-bold text-primary">4.9/5</div>
                    <div class="text-gray-500 text-sm mt-1">Kepuasan Member</div>
                </div>
            </div>
        </div>
    </section> -->

@endsection
