@extends('layouts.app')

@section('body-class', 'bg-gray-50')

@section('content')
<div class="flex min-h-screen" x-data="{ sidebarOpen: false }">
    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-lg fixed h-full z-40 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out lg:z-0"
           :class="{ 'translate-x-0': sidebarOpen }"
           @click.away="sidebarOpen = false">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-xl font-bold text-gray-800">Admin Panel</h2>
        </div>
        
        <nav class="p-4 space-y-2 overflow-y-auto h-[calc(100vh-120px)]">
            <a href="{{ route('admin.dashboard') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-primary text-white shadow-md' : 'text-gray-700 hover:bg-gray-50 hover:text-primary hover:shadow-sm' }}">
                <i class="fas fa-tachometer-alt w-5"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="{{ route('admin.events.index') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ request()->routeIs('admin.events.*') ? 'bg-primary text-white shadow-md' : 'text-gray-700 hover:bg-gray-50 hover:text-primary hover:shadow-sm' }}">
                <i class="fas fa-calendar-alt w-5"></i>
                <span>Manage Events</span>
            </a>

            <a href="{{ route('admin.packets.index') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ request()->routeIs('admin.packets.*') ? 'bg-primary text-white shadow-md' : 'text-gray-700 hover:bg-gray-50 hover:text-primary hover:shadow-sm' }}">
                <i class="fas fa-calendar-alt w-5"></i>
                <span>Manage Packets</span>
            </a>
            
            <a href="{{ route('admin.speakers.index') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ request()->routeIs('admin.speakers.*') ? 'bg-primary text-white shadow-md' : 'text-gray-700 hover:bg-gray-50 hover:text-primary hover:shadow-sm' }}">
                <i class="fas fa-users w-5"></i>
                <span>Speakers</span>
            </a>
            
            <a href="{{ route('admin.registrations.index') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ request()->routeIs('admin.registrations.*') ? 'bg-primary text-white shadow-md' : 'text-gray-700 hover:bg-gray-50 hover:text-primary hover:shadow-sm' }}">
                <i class="fas fa-clipboard-list w-5"></i>
                <span>Registrations</span>
            </a>
        </nav>
    </aside>

    <!-- Overlay - Mobile Only -->
    <div x-show="sidebarOpen" 
         class="fixed inset-0 bg-black/50 z-30 lg:hidden transition-opacity duration-300"
         @click="sidebarOpen = false"></div>

    <!-- Main Content -->
    <main class="flex-1 transition-all duration-300 lg:ml-64 p-4 lg:p-6 min-h-screen relative z-10">

        <!-- Page Header -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-4">
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-800">@yield('page-title')</h1>
            @yield('page-actions')
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-xl flex items-start gap-3 shadow-sm">
                <i class="fas fa-check-circle text-lg mt-0.5 flex-shrink-0"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-700 rounded-xl flex items-start gap-3 shadow-sm">
                <i class="fas fa-exclamation-circle text-lg mt-0.5 flex-shrink-0"></i>
                {{ session('error') }}
            </div>
        @endif

        <!-- Main Admin Content -->
        @yield('admin-content')
    </main>

    <!-- Floating Toggle Button - Mobile Only -->
    <button  @click.stop="sidebarOpen = !sidebarOpen"
            class="fixed bottom-8 right-8 lg:hidden z-50 w-10 h-10 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 text-white rounded-3xl shadow-2xl hover:shadow-3xl hover:-translate-y-1 active:scale-95 transition-all duration-300 flex flex-col items-center justify-center gap-1 text-xs font-bold border-4 border-white/50 backdrop-blur-md ring-4 ring-white/20">
        <i class="fas text-lg" :class="{ 'fa-bars-staggered': !sidebarOpen, 'fa-times': sidebarOpen }"></i>
    </button>
</div>
@endsection
