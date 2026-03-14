<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="app-base" content="{{ rtrim(config('app.url'), '/') }}">
    <meta name="description" content="@yield('meta_description', 'Unlock Indonesia: Platform Webinar Bersertifikat.')">
    <title>@yield('title', 'E-Course - Unlock Indonesia')</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans+Flex:opsz,wght@8..144,100..1000&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans" style="font-family: 'Google Sans Flex', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;">

    {{-- Sidebar --}}
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-br from-primary to-purple-800 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 shadow-2xl">
        <div class="flex flex-col h-full">
            <div class="p-6 border-b border-white/10 flex items-center justify-between">
                <a href="{{ route('ecourse.dashboard') }}" class="flex items-center text-white font-bold text-xl">
                    <i class="fas fa-graduation-cap mr-3"></i>
                    <span>Unlock E-Course</span>
                </a>
                {{-- Close Button for Mobile --}}
                <button class="lg:hidden text-white/50 hover:text-white" onclick="toggleSidebar()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <nav class="flex-1 p-4 overflow-y-auto">
                <ul class="space-y-1.5">
                    <li>
                        <a href="{{ route('ecourse.dashboard') }}" class="flex items-center p-3 text-white/80 hover:text-white hover:bg-white/10 rounded-xl transition {{ Route::is('ecourse.dashboard') ? 'bg-white/20 text-white' : '' }}">
                            <i class="fas fa-home w-5 mr-3"></i>
                            <span class="text-sm font-medium">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('ecourse.catalog') }}" class="flex items-center p-3 text-white/80 hover:text-white hover:bg-white/10 rounded-xl transition {{ Route::is('ecourse.catalog.*') ? 'bg-white/20 text-white' : '' }}">
                            <i class="fas fa-book w-5 mr-3"></i>
                            <span class="text-sm font-medium">Catalog E-Course</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('ecourse.my-journey') }}" class="flex items-center p-3 text-white/80 hover:text-white hover:bg-white/10 rounded-xl transition {{ Route::is('ecourse.catalog.*') ? 'bg-white/20 text-white' : '' }}">
                            <i class="fas fa-graduation-cap w-5 mr-3"></i>
                            <span class="text-sm font-medium">My Journey</span>
                        </a>
                    </li>
                    {{-- ... Menu lainnya ... --}}
                </ul>
            </nav>

            <div class="p-4 border-t border-white/10 bg-black/10">
                <div class="flex items-center">
                    <img src="/storage/{{ auth()->user()->avatar }}" class="w-9 h-9 rounded-full border-2 border-white/20 object-cover">
                    <div class="ml-3 text-white overflow-hidden">
                        <p class="font-semibold text-xs truncate">{{ Auth::user()->name }}</p>
                        <p class="text-[10px] text-white/50">{{ auth()->user()->isAdmin() ? 'Admin' : 'Member' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    {{-- Main Content Area --}}
    <div class="lg:ml-64 flex flex-col min-h-screen">
        
        {{-- Header Top Bar --}}
        <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-gray-100 px-4 sm:px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    {{-- Burger Button --}}
                    <button onclick="toggleSidebar()" class="lg:hidden mr-4 p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                    
                    {{-- Page Title --}}
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 tracking-tight">@yield('page_title', 'Dashboard')</h2>
                        {{-- Breadcrumb minimalis di bawah judul --}}
                        <nav class="flex text-[10px] text-gray-400 font-medium uppercase tracking-wider mt-0.5">
                            <ol class="flex items-center space-x-2">
                                <li><a href="{{ route('ecourse.dashboard') }}" class="hover:text-primary">Home</a></li>
                                @yield('breadcrumb')
                            </ol>
                        </nav>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <div class="relative" x-data="notificationDropdown()" @init="init()">
                        <button @click="toggle()" class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-primary transition relative cursor-pointer hover:scale-110 duration-200">
                            <i class="far fa-bell"></i>
                            <span x-show="hasNotifications" class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                        </button>
                        
                        <!-- Notifikasi Dropdown -->
                        <div x-show="open" x-cloak @click.away="open = false" class="absolute -right-20 mt-3 w-96 bg-white rounded-xl shadow-lg z-50 border border-gray-100 overflow-hidden" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95">
                            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-sm text-gray-800">Notifikasi</h3>
                                    <p class="text-xs text-gray-500">E-Course yang belum selesai</p>
                                </div>
                                <i class="fas fa-graduation-cap text-primary/20 text-lg"></i>
                            </div>
                            
                            <div class="max-h-96 overflow-y-auto">
                                <!-- Loading State -->
                                <div x-show="loading" class="p-4 text-center text-gray-400 text-sm">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>Memuat...
                                </div>

                                <!-- Empty State -->
                                <div x-show="!loading && courses.length === 0" class="p-8 text-center">
                                    <div class="w-12 h-12 bg-gray-100 rounded-full mx-auto flex items-center justify-center mb-3">
                                        <i class="fas fa-check-circle text-primary text-xl"></i>
                                    </div>
                                    <p class="text-gray-500 text-sm font-medium">Semua selesai!</p>
                                    <p class="text-gray-400 text-xs mt-1">Tidak ada e-course yang menunggu</p>
                                </div>

                                <!-- Courses Items -->
                                <template x-for="course in courses" :key="course.id">
                                    <a :href="course.url" class="flex items-center gap-3 p-3 hover:bg-gray-50 transition-colors duration-200 last:border-b-0 group">
                                        <div class="relative w-14 h-14 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg flex-shrink-0 overflow-hidden">
                                            <img x-show="course.thumbnail" :src="'../storage/' + course.thumbnail" :alt="course.title" class="w-full h-full object-cover">
                                            <i x-show="!course.thumbnail" class="fas fa-book text-gray-300 m-4"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-sm text-gray-800 truncate group-hover:text-primary transition-colors duration-200" x-text="course.title"></p>
                                            <div class="flex items-center gap-2 mt-1.5">
                                                <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                                                    <div class="bg-gradient-to-r from-primary to-purple-500 h-1.5 rounded-full transition-all duration-500" :style="`width: ${course.progress}%`"></div>
                                                </div>
                                                <span class="text-xs font-semibold text-primary" x-text="`${course.progress}%`"></span>
                                            </div>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="h-6 w-px bg-gray-200"></div>
                    <span class="text-xs font-semibold text-gray-500">{{ now()->format('d M Y') }}</span>
                </div>
            </div>
        </header>

        {{-- Main Content --}}
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>
    </div>

    {{-- Overlay for mobile --}}
    <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/40 z-40 hidden lg:hidden backdrop-blur-sm transition-opacity"></div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // Alpine.js Component untuk Notification
        function notificationDropdown() {
            return {
                open: false,
                loading: true,
                courses: [],
                hasNotifications: false,

                toggle() {
                    // Hanya toggle, tidak fetch ulang
                    this.open = !this.open;
                },

                async loadNotifications() {
                    this.loading = true;
                    try {
                        const response = await fetch('{{ route("ecourse.notifications.recent") }}');
                        const result = await response.json();

                        if (result.success && Array.isArray(result.data)) {
                            // Force update dengan meng-assign ulang array
                            this.courses = [...result.data];
                            this.hasNotifications = result.count > 0;
                        } else {
                            this.courses = [];
                            this.hasNotifications = false;
                        }
                    } catch (error) {
                        console.error('Error loading notifications:', error);
                        this.courses = [];
                        this.hasNotifications = false;
                    } finally {
                        this.loading = false;
                    }
                },

                init() {
                    // Load notifikasi SEKALI saat halaman dimuat
                    this.loadNotifications();
                }
            };
        }
    </script>
    @stack('scripts')
</body>
</html>
