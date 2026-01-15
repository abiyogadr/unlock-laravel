@extends('layouts.admin')

@section('title', 'Unlock - Registrations')
@section('page-title', 'Manajemen Pendaftaran')

@section('admin-content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    
    {{-- Header & Filter --}}
    <div class="p-6 border-b border-gray-100 grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
        <h3 class="text-lg font-semibold md:col-span-4 text-gray-800">Ringkasan Pendaftaran per Event</h3>
        
        <div class="gap-2">
            <form action="{{ route('admin.registrations.index') }}" method="GET" class="w-full">
                <x-custom-select 
                    name="status" 
                    :value="request('status', 'all')" 
                    placeholder="Semua Status"
                    onchange="this.form.submit()"
                >
                    <x-custom-select-item val="all" label="Semua Status">Semua Status</x-custom-select-item>
                    <x-custom-select-item val="active" label="Active">
                        <span class="text-green-600">●</span> Active
                    </x-custom-select-item>
                    <x-custom-select-item val="inactive" label="Inactive">
                        <span class="text-gray-600">●</span> Inactive
                    </x-custom-select-item>
                </x-custom-select>
            </form>
        </div>
    </div>

    {{-- List Event --}}
    <div class="divide-y divide-gray-300">
        @forelse($events as $event)
        <div class="p-6 hover:bg-gray-50 transition-colors flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            
            {{-- Info Event (Kiri) --}}
            <div class="flex items-start gap-5 flex-1 min-w-0">
                
                {{-- 1. GAMBAR KV (Pengganti Tanggal) --}}
                <div class="shrink-0 w-24 h-24 sm:w-20 sm:h-20 rounded-xl overflow-hidden bg-gray-100 border border-gray-200 shadow-sm relative group">
                    @if($event->kv_path)
                        <img src="{{ Storage::url($event->kv_path) }}" 
                             alt="{{ $event->event_title }}" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                        {{-- Fallback jika tidak ada gambar --}}
                        <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-image text-2xl mb-1"></i>
                            <span class="text-[10px]">No Image</span>
                        </div>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <h4 class="text-lg font-bold text-gray-900 line-clamp-1" title="{{ $event->event_title }}">
                        {{ $event->event_title }}
                    </h4>
                    
                    {{-- 2. META DATA (Kode - Tanggal - Status) --}}
                    <div class="flex flex-wrap items-center gap-3 mt-2 text-sm text-gray-500">
                        {{-- Kode --}}
                        <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-xs font-bold text-gray-600 border border-gray-200">
                            {{ $event->event_code }}
                        </span>
                        
                        <span class="text-gray-300">&bull;</span>
                        
                        {{-- Tanggal (Dipindah ke sini) --}}
                        <span class="flex items-center text-gray-600 font-medium">
                            <i class="far fa-calendar-alt mr-1.5 text-xs"></i>
                            {{ $event->date_start->format('d M Y') }}
                        </span>

                        <span class="text-gray-300">&bull;</span>

                        {{-- Status --}}
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border 
                            {{ $event->status == 'open' ? 'bg-green-50 text-green-700 border-green-100' : 
                              ($event->status == 'draft' ? 'bg-yellow-50 text-yellow-700 border-yellow-100' : 'bg-gray-100 text-gray-600 border-gray-200') }}">
                            {{ ucfirst($event->status) }}
                        </span>
                    </div>
                    
                    {{-- Statistik Kecil --}}
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 mt-3 text-sm">
                        <div class="flex items-center text-gray-700 bg-blue-50 px-3 py-1 rounded-lg border border-blue-100">
                            <i class="fas fa-users text-blue-400 mr-2"></i>
                            <span class="font-bold mr-1">{{ $event->registrations_count }}</span> 
                            <span class="text-gray-500 text-xs">Peserta</span>
                        </div>
                        <div class="flex items-center text-gray-700" title="Estimasi Pendapatan (Verified Only)">
                            <i class="fas fa-coins text-gray-400 mr-2"></i>
                            <span class="font-bold text-green-600">Rp {{ number_format($event->total_revenue, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Button (Kanan) --}}
            <div class="shrink-0 self-end md:self-center w-full md:w-auto mt-2 md:mt-0">
                <a href="{{ route('admin.registrations.show_event', $event) }}" 
                   class="flex items-center justify-center w-full md:w-auto px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:text-primary hover:border-primary transition-all shadow-sm">
                    Lihat Pendaftar <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>

        </div>
        @empty
        <div class="p-12 text-center text-gray-500">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="far fa-folder-open text-3xl opacity-40"></i>
            </div>
            <p class="font-medium">Belum ada event yang tersedia.</p>
            <p class="text-sm mt-1">Buat event baru untuk mulai menerima pendaftaran.</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($events->hasPages())
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
        {{ $events->links() }}
    </div>
    @endif
</div>
@endsection
