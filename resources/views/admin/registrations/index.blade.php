@extends('layouts.admin')

@section('title', 'Unlock - Registrations')
@section('page-title', 'Manajemen Pendaftaran')

@section('admin-content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    
    {{-- Header & Filter --}}
    <div class="p-4 md:p-5 border-b border-gray-100 grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        <h3 class="text-base font-semibold md:col-span-4 text-gray-800">Ringkasan Pendaftaran per Event</h3>
        
        <div>
            <form action="{{ route('admin.registrations.index') }}" method="GET" class="w-full" data-admin-filter-form>
                <div class="flex items-end gap-2">
                <x-custom-select 
                    name="status" 
                    :value="request('status', 'all')" 
                    placeholder="Semua Status"
                    class="flex-1 min-w-0"
                >
                    <x-custom-select-item val="all" label="Semua Status">Semua Status</x-custom-select-item>
                    <x-custom-select-item val="open" label="Open">
                        <span class="text-green-600">●</span> Open
                    </x-custom-select-item>
                    <x-custom-select-item val="close" label="Close">
                        <span class="text-gray-600">●</span> Close
                    </x-custom-select-item>
                </x-custom-select>

                </div>
            </form>
        </div>
    </div>

    {{-- List Event --}}
    <div class="divide-y divide-gray-200" data-admin-filter-results>
        @forelse($events as $event)
        <div class="p-4 md:p-5 hover:bg-gray-50 transition-colors flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            
            {{-- Info Event (Kiri) --}}
            <div class="flex items-start gap-4 flex-1 min-w-0">
                
                {{-- 1. GAMBAR KV (Pengganti Tanggal) --}}
                <div class="shrink-0 w-20 h-20 sm:w-18 sm:h-18 rounded-xl overflow-hidden bg-gray-100 border border-gray-200 shadow-sm relative group">
                    @if($event->kv_path)
                        <img src="{{ Storage::url($event->kv_path) }}" 
                             alt="{{ $event->event_title }}" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                        {{-- Fallback jika tidak ada gambar --}}
                        <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-image text-xl mb-1"></i>
                            <span class="text-[9px]">No Image</span>
                        </div>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <h4 class="text-base font-bold text-gray-900 line-clamp-1" title="{{ $event->event_title }}">
                        {{ $event->event_title }}
                    </h4>
                    
                    {{-- 2. META DATA (Kode - Tanggal - Status) --}}
                    <div class="flex flex-wrap items-center gap-2 mt-1.5 text-xs text-gray-500">
                        {{-- Kode --}}
                        <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-[11px] font-bold text-gray-600 border border-gray-200">
                            {{ $event->event_code }}
                        </span>
                        
                        <span class="text-gray-300">&bull;</span>
                        
                        {{-- Tanggal (Dipindah ke sini) --}}
                        <span class="flex items-center text-gray-600 font-medium">
                            <i class="far fa-calendar-alt mr-1 text-[10px]"></i>
                            {{ $event->date_start->format('d M Y') }}
                        </span>

                        <span class="text-gray-300">&bull;</span>

                        {{-- Status --}}
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold border 
                            {{ $event->status == 'open' ? 'bg-green-50 text-green-700 border-green-100' : 
                              ($event->status == 'draft' ? 'bg-yellow-50 text-yellow-700 border-yellow-100' : 'bg-gray-100 text-gray-600 border-gray-200') }}">
                            {{ ucfirst($event->status) }}
                        </span>

                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $event->is_attendance_open ? 'bg-blue-50 text-blue-700 border-blue-100' : 'bg-red-50 text-red-700 border-red-100' }}">
                            Presensi {{ $event->is_attendance_open ? 'Open' : 'Closed' }}
                        </span>
                    </div>
                    
                    {{-- Statistik Kecil --}}
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-2 mt-2 text-xs">
                        <div class="flex items-center text-gray-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
                            <i class="fas fa-users text-blue-400 mr-1.5 text-xs"></i>
                            <span class="font-bold mr-1 text-sm">{{ $event->registrations_count }}</span> 
                            <span class="text-gray-500 text-[11px]">Peserta</span>
                        </div>
                        <div class="flex items-center text-gray-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100">
                            <i class="fas fa-circle-check text-emerald-400 mr-1.5 text-xs"></i>
                            <span class="font-bold mr-1 text-sm text-emerald-700">{{ $event->paid_registrations_count ?? 0 }}</span>
                            <span class="text-gray-500 text-[11px]">Paid</span>
                        </div>
                        <div class="flex items-center text-gray-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-100">
                            <i class="fas fa-clock text-amber-400 mr-1.5 text-xs"></i>
                            <span class="font-bold mr-1 text-sm text-amber-700">{{ $event->pending_registrations_count ?? 0 }}</span>
                            <span class="text-gray-500 text-[11px]">Pending</span>
                        </div>
                        <div class="flex items-center text-gray-700" title="Estimasi Pendapatan (Verified Only)">
                            <i class="fas fa-coins text-gray-400 mr-1.5 text-xs"></i>
                            <span class="font-bold text-green-600 text-sm">Rp {{ number_format($event->total_revenue, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Button (Kanan) --}}
            <div class="shrink-0 self-end md:self-center w-full md:w-auto mt-1 md:mt-0">
                <a href="{{ route('admin.registrations.show_event', $event) }}" 
                   class="flex items-center justify-center w-full md:w-auto px-4 py-2 bg-white border border-gray-300 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-primary hover:border-primary transition-all shadow-sm">
                    Lihat Pendaftar <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>

        </div>
        @empty
        <div class="p-10 text-center text-gray-500">
            <div class="w-14 h-14 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="far fa-folder-open text-2xl opacity-40"></i>
            </div>
            <p class="font-medium">Belum ada event yang tersedia.</p>
            <p class="text-xs mt-1">Buat event baru untuk mulai menerima pendaftaran.</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="px-4 py-3 border-t border-gray-100 bg-gray-50" data-admin-pagination @unless($events->hasPages()) hidden @endunless>
        @if($events->hasPages())
            {{ $events->links() }}
        @endif
    </div>
</div>

<script>
(() => {
    const form = document.querySelector('[data-admin-filter-form]');
    const results = document.querySelector('[data-admin-filter-results]');
    const resetButton = document.querySelector('[data-admin-filter-reset]');

    if (!form || !results) {
        return;
    }

    const buildUrl = () => {
        const params = new URLSearchParams();
        const formData = new FormData(form);

        for (const [key, value] of formData.entries()) {
            const normalized = String(value ?? '').trim();

            if (normalized !== '' && normalized !== 'all') {
                params.set(key, value);
            }
        }

        return `${window.location.pathname}${params.toString() ? `?${params.toString()}` : ''}`;
    };

    const hasActiveFilters = () => {
        const formData = new FormData(form);

        for (const [key, value] of formData.entries()) {
            const normalized = String(value ?? '').trim();

            if (normalized !== '' && normalized !== 'all') {
                return true;
            }
        }

        return false;
    };

    const syncActionButton = () => {
        if (!resetButton) {
            return;
        }

        const active = hasActiveFilters();

        resetButton.disabled = !active;
        resetButton.className = `mt-2 w-full py-2 px-3 rounded-lg text-sm font-medium transition text-center flex items-center justify-center gap-2 border ${active ? 'bg-red-50 text-red-600 border-red-100 hover:bg-red-100 cursor-pointer' : 'bg-gray-50 text-gray-300 border-gray-100 cursor-not-allowed'}`;
        resetButton.textContent = active ? 'Reset' : 'Auto Filter';
    };

    const replaceResults = async (url) => {
        const response = await fetch(url, {
            headers: {
                'Accept': 'text/html',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error('Gagal memuat data.');
        }

        const html = await response.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const nextResults = doc.querySelector('[data-admin-filter-results]');
        const nextPagination = doc.querySelector('[data-admin-pagination]');
        const pagination = document.querySelector('[data-admin-pagination]');

        if (nextResults) {
            results.innerHTML = nextResults.innerHTML;
        }

        if (pagination && nextPagination) {
            pagination.innerHTML = nextPagination.innerHTML;
            pagination.hidden = !nextPagination.innerHTML.trim();
        }

        window.history.replaceState({}, '', url);
        syncActionButton();
    };

    const resetFilters = () => {
        const statusSelect = form.querySelector('[name="status"]');

        if (statusSelect) {
            statusSelect.value = 'all';
        }

        window.dispatchEvent(new CustomEvent('admin-filters-reset'));
        syncActionButton();
        replaceResults(window.location.pathname).catch((error) => console.error(error));
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        replaceResults(buildUrl()).catch((error) => console.error(error));
    });

    form.addEventListener('change', () => {
        syncActionButton();
        replaceResults(buildUrl()).catch((error) => console.error(error));
    });

    if (resetButton) {
        resetButton.addEventListener('click', resetFilters);
    }

    syncActionButton();

    results.addEventListener('click', (event) => {
        const paginationLink = event.target.closest('[data-admin-pagination] a');

        if (!paginationLink) {
            return;
        }

        event.preventDefault();
        replaceResults(paginationLink.href).catch((error) => console.error(error));
    });
})();
</script>
@endsection
