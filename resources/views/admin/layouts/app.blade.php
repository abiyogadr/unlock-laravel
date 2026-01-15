<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Admin Unlock - @yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="h-full bg-slate-100 font-sans antialiased">

<div class="min-h-screen flex">

    {{-- Sidebar --}}
    <aside class="w-64 bg-gradient-to-b from-purple-900 to-purple-700 text-white flex flex-col">
        <div class="h-16 flex items-center px-5 border-b border-purple-600/40">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <img src="{{ asset('assets/images/logo-unlock.png') }}" alt="Unlock" class="h-9">
                <div>
                    <div class="text-sm font-semibold tracking-widest uppercase">Unlock</div>
                    <div class="text-[11px] text-purple-200">Admin Panel</div>
                </div>
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 space-y-1">
            <div class="px-4 text-[11px] font-semibold uppercase tracking-wider text-purple-200/80 mb-1">
                Manajemen
            </div>

            <a href="{{ route('admin.events.index') }}"
               class="flex items-center px-4 py-2 text-sm font-medium
                      hover:bg-purple-800/80 transition
                      {{ request()->routeIs('admin.events.*') ? 'bg-purple-800 text-white' : 'text-purple-100/90' }}">
                <i class="fa-solid fa-calendar-days w-5 mr-3"></i>
                Event
            </a>

            <a href="{{ route('admin.packets.index') }}"
               class="flex items-center px-4 py-2 text-sm font-medium
                      hover:bg-purple-800/80 transition
                      {{ request()->routeIs('admin.packets.*') ? 'bg-purple-800 text-white' : 'text-purple-100/90' }}">
                <i class="fa-solid fa-box-open w-5 mr-3"></i>
                Paket
            </a>

            <a href="{{ route('admin.event-packets.index') }}"
               class="flex items-center px-4 py-2 text-sm font-medium
                      hover:bg-purple-800/80 transition
                      {{ request()->routeIs('admin.event-packets.*') ? 'bg-purple-800 text-white' : 'text-purple-100/90' }}">
                <i class="fa-solid fa-layer-group w-5 mr-3"></i>
                Relasi Event–Paket
            </a>

            <a href="{{ route('admin.participants.index') }}"
               class="flex items-center px-4 py-2 text-sm font-medium
                      hover:bg-purple-800/80 transition
                      {{ request()->routeIs('admin.participants.*') ? 'bg-purple-800 text-white' : 'text-purple-100/90' }}">
                <i class="fa-solid fa-users w-5 mr-3"></i>
                Peserta
            </a>
        </nav>

        <div class="px-4 py-3 border-t border-purple-600/40 text-[11px] text-purple-200 flex items-center justify-between">
            <span>&copy; {{ date('Y') }} Unlock</span>
            <a href="{{ route('logout') }}" class="hover:text-white text-xs"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa-solid fa-right-from-bracket mr-1"></i> Logout
            </a>
            <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                @csrf
            </form>
        </div>
    </aside>

    {{-- Main area --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Topbar --}}
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6">
            <div>
                <h1 class="text-lg font-semibold text-slate-800">@yield('page_title', 'Dashboard')</h1>
                <p class="text-xs text-slate-500">@yield('page_subtitle')</p>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-xs text-slate-500 uppercase tracking-wide">Admin</span>
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-orange-400 flex items-center justify-center text-xs font-semibold text-white">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </main>
    </div>
</div>

</body>
</html>
