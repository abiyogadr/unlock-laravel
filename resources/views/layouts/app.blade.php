<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="app-base" content="{{ rtrim(config('app.url'), '/') }}">
    <meta name="description" content="@yield('meta_description', 'Unlock Indonesia: Platform Webinar Bersertifikat. Tingkatkan skill profesional Anda bersama praktisi industri terbaik. Daftar sekarang untuk akses materi eksklusif dan e-sertifikat nasional!')">
    <title>@yield('title', 'Unlock - Webinar Event')</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> -->
    
    <!-- Vite + Tailwind CSS v4 -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles') 

</head>
<!-- <body class="font-[var(--font-poppins)] bg-gray-50 text-gray-800 leading-relaxed "> -->
<body class="font-[var(--font-poppins)] bg-gray-50 text-gray-800 leading-relaxed min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="sticky top-0 z-50 bg-white shadow-sm w-full">
        <!-- Pembungkus konten navbar agar sejajar dengan isi web -->
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 {{ request()->is('upanel*') ? 'max-w-full' : 'max-w-7xl' }} py-2 flex justify-between items-center">
            
            <!-- LOGO -->
            <a href="{{ url('/') }}" class="inline-block">
                <img src="{{ asset('assets/images/logo-unlock.png') }}"
                    alt="Unlock Logo"
                    class="h-10 cursor-pointer hover:opacity-90 transition">
            </a>

            <!-- MENU KANAN -->
            <div class="flex items-center gap-4">
                @guest
                    <a href="{{ route('login') }}" 
                    class="bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-secondary transition duration-300 shadow-sm">
                    Masuk
                    </a>
                @endguest

                @auth
                    <div class="relative group" x-data="{ open: false }">
                        <!-- Tombol Trigger Dropdown -->
                        <button @click="open = !open" @click.away="open = false" 
                                class="flex items-center gap-2 lg:px-3 py-1 rounded-lg hover:bg-gray-50 transition border border-transparent hover:border-gray-100">
                            
                            <div class="text-right">
                                {{-- Desktop --}}
                                <p class="text-sm font-semibold text-gray-700 hidden sm:block">
                                    {{ Str::limit(Auth::user()->name, 25) }}
                                </p>

                                {{-- Mobile --}}
                                <p class="text-sm font-semibold text-gray-700 block sm:hidden">
                                    {{ Str::before(Auth::user()->name, ' ') }}
                                </p>

                                <p class="text-xs text-gray-500">
                                    {{ auth()->user()->isAdmin() ? 'Admin' : 'Member' }}
                                </p>
                            </div>
                            
                            <!-- Avatar -->
                            @if(auth()->check() && auth()->user()->avatar)
                                <img src="/storage/{{ auth()->user()->avatar }}" 
                                    class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="h-9 w-9 rounded-full bg-primary/10 flex items-center justify-center text-primary border border-primary/20">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif

                            <i class="fas px-2 fa-chevron-down text-xs text-gray-400 transition-transform duration-200"
                            :class="{ 'rotate-180': open }"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" 
                            x-transition
                            class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 overflow-hidden z-50 origin-top-right"
                            style="display: none;">
                            
                            <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary transition">
                                <i class="fas fa-user-circle w-5 text-gray-400"></i> Profil
                            </a>

                            <a href="{{ route('myevents') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary transition">
                                <i class="fas fa-calendar-alt w-5 text-gray-400"></i> Event Saya
                            </a>

                            <a href="{{ route('registration.attendance.list') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary transition">
                                <i class="fas fa-book w-5 text-gray-400"></i> Presensi
                            </a>

                            @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary transition">
                                <i class="fas fa-user-shield w-5 text-gray-400"></i> Upanel
                            </a>
                            @endif
                            
                            <div class="border-t border-gray-100 my-1"></div>

                            <form action="{{ route('logout') }}" method="POST" class="block">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition flex items-center">
                                    <i class="fas fa-sign-out-alt w-5"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </nav>
    <main class="flex-1 w-full">
        @yield('content')
    </main>
    
    @if (auth()->check() && !request()->is('upanel*'))
    <footer class="bg-primary border-t border-gray-200 mt-auto">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl py-6">
            <!-- BAGIAN ATAS: GRID MENU -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-8 mb-6">
                <!-- Kolom Logo & Sosmed -->
                <div class="col-span-2 lg:col-span-1">
                    <img src="{{ asset('assets/images/logo-unlock.png') }}" alt="Logo" class="h-10 mb-4">
                    <p class="text-sm font-semibold text-white mb-3">Ikuti Kami</p>
                    <div class="flex gap-3">
                        <a href="https://instagram.com/unlock.indonesia" class="text-white text-xl"><i class="fab fa-instagram"></i></a>
                        <a href="https://youtube.com/@unlockyoutubechannel" class="text-white text-xl"><i class="fab fa-youtube"></i></a>
                        <a href="https://wa.me/6285176767623" class="text-white text-xl"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>

                <!-- Kolom Produk -->
                <div>
                    <h4 class="font-bold text-white mb-4">Produk</h4>
                    <ul class="space-y-2 text-sm text-white">
                        <!-- <li><a href="#" class="hover:text-orange-300">Instant Meeting</a></li> -->
                        <li><a href="{{ route('event.index') }}" class="hover:text-orange-300">Event</a></li>
                        <li><a href="{{ route('certificate') }}" class="hover:text-orange-300">Sertifikat Saya</a></li>
                    </ul>
                </div>

                <!-- Kolom Sumber Daya -->
                <!-- <div>
                    <h4 class="font-bold text-white mb-4">Sumber Daya</h4>
                    <ul class="space-y-2 text-sm text-white">
                        <li><a href="#" class="hover:text-orange-300">Berita</a></li>
                        <li><a href="#" class="hover:text-orange-300">Panduan Pengguna</a></li>
                    </ul>
                </div> -->

                <!-- Kolom Informasi -->
                <div>
                    <h4 class="font-bold text-white mb-4">Informasi</h4>
                    <ul class="space-y-2 text-sm text-white">
                        <li><a href="{{ route('about') }}" class="hover:text-orange-300">Tentang Kami</a></li>
                        <li><a href="{{ route('faq') }}" class="hover:text-orange-300">FAQ & Bantuan</a></li>
                        <li><a href="{{ route('policy') }}" class="hover:text-orange-300">Kebijakan Privasi</a></li>
                    </ul>
                </div>

                <!-- Kolom Hubungi Kami (Lebar 2 kolom di lg) -->
                <div class="col-span-2">
                    <h4 class="font-bold text-white mb-4">Hubungi Kami</h4>
                    <ul class="space-y-3 text-sm text-white">
                        <li class="flex gap-2">
                            <i class="fas fa-building mt-1 text-white"></i>
                            <span>Unlock Indonesia<br>Sidoarjo, Jawa Timur</span>
                        </li>
                        <li class="flex gap-2">
                            <i class="fas fa-phone mt-1 text-white"></i>
                            <span>+62 851 7676 7623</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- GARIS PEMISAH -->
            <div class="border-t border-gray-200 pt-6 text-center">
                <p class="text-sm text-white">
                    © 2026 Copyright by Unlock Indonesia. All rights reserved.
                </p>
            </div>
        </div>
    </footer>
    @endif
    <!-- FLOATING BUTTON WRAPPER -->
    @if(Route::is('home', 'about', 'promo.index'))
    <div style="position: fixed; top: 90%; right: 30px; z-index: 9999; transform: translateY(-50%);">
        
        <a href="{{ route('registration.create') }}" 
        class="flex items-center gap-3 px-8 py-3 
                bg-primary text-white font-semibold rounded-full 
                shadow-xl hover:shadow-2xl 
                hover:bg-secondary hover:scale-105 
                transition-all duration-300 ease-in-out">
            
            <span>Daftar Sekarang</span>
            <i class="fas fa-arrow-right text-sm"></i>
            
        </a>

    </div>
    @endif

    @stack('scripts')
</body>
</html>
