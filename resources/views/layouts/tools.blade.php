<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unlock Tools Engine</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
    <style>
        /* Reset & Base Style */
        .content, .ql-editor, .preview-content {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1e293b;
        }

        /* Opsi 1: Paragraf Biasa (P) -> UNTUK TEKS RAPAT */
        /* Cocok untuk: Salam penutup, Nama Tim, Tanda tangan */
        .content p, .ql-editor p, .preview-content p {
            margin: 0 0 2px 0 !important;
            padding: 0;
            font-size: 16px;
            font-weight: normal;
        }

        /* Opsi 2: Heading 6 (H6) -> UNTUK TEKS RENGGANG */
        /* Saya buat H6 agar font-nya sama dengan p, tapi margin bawahnya lebar (24px) */
        .content h6, .ql-editor h6, .preview-content h6 {
            font-size: 16px !important;
            font-weight: normal !important; /* Menghilangkan tebal agar jadi teks biasa */
            margin: 0 0 18px 0 !important; /* Jarak antar paragraf renggang */
            color: #1e293b !important;
            display: block;
        }

        /* Headings Utama (H1 - H5) */
        .content h1, .ql-editor h1 { font-size: 28px; font-weight: 800; margin: 24px 0 12px 0 !important; color: #1e1b4b; }
        .content h2, .ql-editor h2 { font-size: 22px; font-weight: 700; margin: 20px 0 8px 0 !important; color: #1e1b4b; }
        .content h3, .ql-editor h3 { font-size: 19px; font-weight: 600; margin: 16px 0 6px 0 !important; color: #334155; }
        .content h4, .ql-editor h4 { font-size: 17px; font-weight: 700; margin: 14px 0 4px 0 !important; color: #475569; text-transform: uppercase; }
        .content h5, .ql-editor h5 { font-size: 16px; font-weight: 700; margin: 12px 0 4px 0 !important; color: #64748b; }

        /* Media & Lists */
        .content img { max-width: 100%; height: auto; border-radius: 12px; margin: 16px 0; display: block; }
        .ql-button-emailButton {
            width: 28px;
            height: 28px;
            background-image: url("data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEyIDEyTDcgMTZWMThMMTIgMjJMMTcgMThWMThMMTIgMTZaIiBmaWxsPSIjNjM2NmYxIi8+Cjwvc3ZnPgo=");
            background-repeat: no-repeat;
            background-position: center;
        }
        .ql-button-emailButton:hover {
            background-color: #e0e7ff !important;
        }
    </style>

</head>
<body class="bg-slate-50 text-slate-900" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200 transition-transform duration-300 ease-in-out">
        <div class="h-full flex flex-col">
            <!-- Logo Area -->
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-700 to-orange-500 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-purple-200">U</div>
                    <span class="font-extrabold text-xl tracking-tight text-purple-800 uppercase">Unlock <span class="text-orange-500">Tools</span></span>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                <p class="px-4 text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Marketing Tools</p>
                <a href="{{ route('tools.email-blast.index') }}" class="flex items-center gap-3 px-4 py-3 bg-purple-50 text-purple-700 rounded-xl font-semibold transition-all group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Email Blast Engine
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    WhatsApp Automator
                </a>

                <p class="px-4 pt-6 text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Management</p>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    History & Analytics
                </a>
            </nav>

            <!-- Bottom Profile -->
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-3 px-2">
                    <!-- Icon User / Avatar -->
                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-slate-200 to-slate-300 flex items-center justify-center text-slate-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    
                    <div>
                        <p class="text-sm font-bold text-slate-800 leading-tight">
                            {{ auth()->user()->name ?? 'Admin Unlock' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="lg:ml-72 min-h-screen flex flex-col">
        <!-- Header -->
        <header class="sticky top-0 z-30 flex items-center px-6 py-4 bg-white/80 backdrop-blur-md border-b border-slate-200">
            <button @click="sidebarOpen = true" class="p-2 text-slate-500 hover:bg-slate-50 rounded-lg lg:hidden transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="block">
                <span class="text-slate-800 font-bold">@yield('page-title')</span>
            </div>
            <!-- <div class="flex items-center gap-4">
                <button class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-purple-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </button>
            </div> -->
        </header>

        <!-- Dynamic Content -->
        <main class="flex-1 p-6 lg:px-10">
            @yield('tool-content')
        </main>
    </div>

</body>
</html>
