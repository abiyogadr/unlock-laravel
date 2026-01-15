@extends('layouts.app')

@section('title', 'Unlock - Jadwal')

@section('content')
{{-- Hero --}}
<section class="w-full bg-gradient-to-br from-primary to-purple-800 text-white py-20">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl text-center">
        <h1 class="text-4xl md:text-5xl font-extrabold mb-3 tracking-tight">
            Event & Webinar Unlock
        </h1>
        <p class="text-base md:text-lg opacity-90 max-w-2xl mx-auto">
            Pilih event terbaik untuk meningkatkan skill dan potensimu.
        </p>
    </div>
</section>

<section class="w-full py-12 bg-gray-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl space-y-10">

        {{-- Filter Card -- Auto submit --}}
        <form id="filter-form" method="GET"
            class="bg-white rounded-2xl shadow-xl border border-gray-100 p-5 md:p-6
                    grid md:grid-cols-5 gap-4 items-end">

            {{-- Search (debounced) --}}
            <x-input-field 
                label="Cari Event"
                name="q" 
                icon="fas fa-search" 
                class="md:col-span-2"
                placeholder="Cari..."
            />

            {{-- Status --}}
            <x-custom-select name="status" label="Status" :value="request('status')" placeholder="Semua Status" onchange="this.form.submit()">
                <x-custom-select-item val="" label="Semua Status">Semua Status</x-custom-select-item>
                <x-custom-select-item val="open" label="Open">
                    <span class="text-green-600">●</span> Open
                </x-custom-select-item>
                <x-custom-select-item val="close" label="Close">
                    <span class="text-gray-600">●</span> Close
                </x-custom-select-item>
            </x-custom-select>

            {{-- Filter Tahun --}}
            <x-custom-select 
                name="year" 
                label="Tahun" 
                :value="request('year')" 
                placeholder="Semua Tahun" 
                onchange="this.form.submit()"
            >
                <x-custom-select-item val="" label="Semua Tahun">
                    Semua Tahun
                </x-custom-select-item>
                
                @for($y = now()->year; $y >= 2022; $y--)
                    <x-custom-select-item :val="$y" :label="$y">
                        {{ $y }}
                    </x-custom-select-item>
                @endfor
            </x-custom-select>

            {{-- Filter Bulan --}}
            <x-custom-select 
                name="month" 
                label="Bulan" 
                :value="request('month')" 
                placeholder="Semua Bulan" 
                onchange="this.form.submit()"
            >
                <x-custom-select-item val="" label="Semua Bulan">
                    Semua Bulan
                </x-custom-select-item>

                @for($m = 1; $m <= 12; $m++)
                    @php 
                        $monthName = DateTime::createFromFormat('!m', $m)->format('F'); 
                    @endphp
                    <x-custom-select-item :val="$m" :label="$monthName">
                        {{ $monthName }}
                    </x-custom-select-item>
                @endfor
            </x-custom-select>

            {{-- Loading indicator (hidden by default) --}}
            <div id="loading" class="hidden md:col-span-5 text-center text-gray-500 text-sm mt-2">
                <i class="fas fa-spinner fa-spin mr-2"></i> Memuat...
            </div>
        </form>

        {{-- Event Cards --}}
        <div id="events-container" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
            @forelse($events as $event)
                <article
                    class="group bg-white rounded-2xl border border-gray-100 shadow-sm
                        hover:shadow-xl hover:-translate-y-1 transition-all duration-300
                        overflow-hidden flex flex-col">

                    {{-- KV --}}
                    <div class="relative h-44 bg-gray-100 overflow-hidden">
                        @if($event->kv_path)
                            <img src="{{ asset('storage/'.$event->kv_path) }}"
                                alt="{{ $event->event_title }}"
                                class="w-full h-full object-cover transition-transform duration-500
                                        group-hover:scale-105">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center
                                        bg-gradient-to-br from-gray-50 to-gray-200">
                                <i class="fas fa-image text-3xl text-gray-300 mb-2"></i>
                                <span class="text-xs text-gray-400">Tidak ada gambar</span>
                            </div>
                        @endif

                        {{-- Status badge on image --}}
                        <span class="absolute top-3 left-3 px-2.5 py-1 text-[11px] font-semibold rounded-full
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
                        </div>

                        <div class="mt-auto">
                            <a href="{{ route('event.show', $event->event_code) }}"
                            class="block w-full text-center text-sm font-semibold
                                    bg-secondary text-white py-2.5 rounded-xl
                                    hover:bg-orange-600 transition">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full text-center text-gray-500 py-16">
                    <div class="max-w-sm mx-auto bg-white rounded-2xl border border-gray-100 p-8 shadow-sm">
                        <i class="fas fa-search text-4xl mb-4 opacity-40"></i>
                        <p class="text-base font-semibold mb-1">Event tidak ditemukan</p>
                        <p class="text-xs text-gray-500">
                            Coba ubah kata kunci atau kombinasi filter yang digunakan.
                        </p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $events->links() }}
        </div>
    </div>
</section>

@push('scripts')
<script>
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    const filterForm = document.getElementById('filter-form');
    const searchInput = document.getElementById('search-input');
    const loadingIndicator = document.getElementById('loading');
    
    if (searchInput) {
        const debouncedSubmit = debounce(() => {
            loadingIndicator.classList.remove('hidden');
            filterForm.submit();
        }, 500);

        searchInput.addEventListener('input', debouncedSubmit);
    }

    const selectElements = filterForm.querySelectorAll('select');
    selectElements.forEach(select => {
        select.addEventListener('change', () => {
            loadingIndicator.classList.remove('hidden');
        });
    });

    window.addEventListener('pageshow', () => {
        loadingIndicator.classList.add('hidden');
    });
</script>
@endpush
@endsection
