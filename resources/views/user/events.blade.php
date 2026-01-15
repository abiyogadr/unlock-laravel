@extends('layouts.app')

@section('title', 'Unlock - Riwayat Event')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header Section --}}
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Event Saya</h1>
            <p class="text-gray-500 text-sm mt-1">Daftar webinar dan event yang telah Anda ikuti.</p>
        </div>
        <a href="{{ route('registration.create') }}" 
           class="inline-flex items-center justify-center px-5 py-2.5 border border-transparent text-sm font-semibold rounded-xl text-white bg-primary hover:bg-primary/90 shadow-lg shadow-primary/30 transition-all duration-300 transform hover:-translate-y-1">
            <i class="fas fa-plus mr-2"></i> Daftar Event Baru
        </a>
    </div>

    {{-- Alert Sukses (Jika ada action cancel/dll) --}}
    @if (session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-xl shadow-sm" x-data="{ show: true }" x-show="show">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                </div>
                <button @click="show = false" class="text-green-500 hover:text-green-700"><i class="fas fa-times"></i></button>
            </div>
        </div>
    @endif

    {{-- LIST EVENT (Fixed Layout) --}}
@if($registrations->count() > 0)
    <div class="grid grid-cols-1 gap-6">
        @foreach($registrations as $reg)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xl shadow-gray-200/40 overflow-hidden hover:shadow-2xl hover:shadow-gray-200/60 transition-all duration-300 group">
                <div class="p-6 sm:p-8 flex flex-row gap-6"> 
                    {{-- Ubah flex-col md:flex-row jadi flex-row agar di HP tanggal tetap di kiri (opsional, tapi lebih rapi untuk alignment status) --}}
                    {{-- Jika ingin di HP tanggal diatas, kembalikan ke flex-col md:flex-row --}}
                    
                    {{-- Date Badge (Left Side) --}}
                    {{-- Hapus 'flex-col' di mobile jika ingin layout samping-sampingan terus --}}
                    <div class="shrink-0 flex flex-col items-center justify-center gap-1">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-primary/5 text-primary flex flex-col items-center justify-center border border-primary/10 group-hover:bg-primary group-hover:text-white transition-colors duration-300">
                            <span class="text-xs sm:text-sm font-bold uppercase tracking-wider">{{ $reg->event->date_start->format('M') }}</span>
                            <span class="text-2xl sm:text-3xl font-extrabold">{{ $reg->event->date_start->format('d') }}</span>
                        </div>
                        {{-- Year (Moved below box for cleaner alignment) --}}
                        <span class="text-gray-500 font-medium text-xs sm:text-sm">{{ $reg->event->date_start->format('Y') }}</span>
                    </div>

                    {{-- Event Details --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-col h-full justify-between">
                            <div>
                                {{-- LOGIC STATUS BADGE (Dipindah ke Atas) --}}
                                @php
                                    if ($reg->payment_status === 'pending') {
                                        $currentStatus = 'pending_payment';
                                        $label = 'Menunggu Pembayaran';
                                        $color = 'bg-yellow-50 text-yellow-700 border-yellow-200';
                                    } elseif ($reg->payment_status === 'failed' || $reg->payment_status === 'success' && $reg->registration_status === 'rejected') {
                                        $currentStatus = 'failed';
                                        $label = 'Ditolak';
                                        $color = 'bg-red-50 text-red-700 border-red-200';
                                    } elseif ($reg->payment_status === 'success' && $reg->registration_status === 'pending') {
                                        $currentStatus = 'pending_verification';
                                        $label = 'Menunggu Verifikasi';
                                        $color = 'bg-blue-50 text-blue-700 border-blue-200';
                                    } elseif ($reg->payment_status === 'success' && $reg->registration_status === 'verified') {
                                        $currentStatus = 'success';
                                        $label = 'Terdaftar';
                                        $color = 'bg-green-50 text-green-700 border-green-200';
                                    } else {
                                        $currentStatus = 'unknown';
                                        $label = ucfirst($reg->payment_status ?? 'Unknown');
                                        $color = 'bg-gray-50 text-gray-600 border-gray-200';
                                    }
                                @endphp

                                {{-- SECTION 1: Status Badge (Sejajar dengan Tanggal) --}}
                                <div class="mb-3 flex justify-end gap-2">
                                    @if($reg->is_attended === true)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium border bg-green-50 text-green-700 border-green-200">
                                        Sudah Presensi
                                    </span>
                                    @elseif ($reg->is_attended === false && $reg->payment_status === 'success' && $reg->registration_status === 'verified')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium border bg-yellow-50 text-yellow-700 border-yellow-200">
                                        Belum Presensi
                                    </span>
                                    @endif
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium border {{ $color }}">
                                        {{ $label }}
                                    </span>
                                </div>

                                {{-- SECTION 2: Title (Full Width - Tidak lagi Grid) --}}
                                <h3 class="text-lg sm:text-xl font-bold leading-tight mb-3">
                                    <a href="{{ route('myevents.show', $reg->uuid ?? $reg->id) }}"
                                       class="text-gray-900 hover:text-primary transition-colors line-clamp-2">
                                        {{ $reg->event->event_title }}
                                    </a>
                                </h3>
                                <h6 class="font-semibold leading-tight mb-3">
                                    <a href="{{ route('myevents.show', $reg->uuid ?? $reg->id) }}"
                                       class="text-gray-900 hover:text-primary transition-colors line-clamp-2">
                                        {{ $reg->registration_code }}
                                    </a>
                                </h6>
                                
                                {{-- SECTION 3: Meta Info --}}
                                <div class="flex flex-wrap gap-y-2 gap-x-5 text-sm text-gray-500 mb-4">
                                    <div class="flex items-center">
                                        <i class="far fa-clock text-gray-400 mr-2"></i>
                                        {{ $reg->event->time_start }} WIB
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-video text-gray-400 mr-2"></i>
                                        Zoom Meeting
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-ticket-alt text-gray-400 mr-2"></i>
                                        {{ $reg->packet->packet_name ?? 'Regular' }}
                                    </div>
                                </div>
                            </div>

                            {{-- Action Footer --}}
                            <div class="pt-4 border-t border-gray-50 flex flex-wrap items-center gap-3 mt-auto">
                                @if($currentStatus == 'success')
                                    <a href="{{ $reg->event->link_zoom ?? '#' }}" target="_blank" 
                                       class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 text-sm font-semibold rounded-lg hover:bg-blue-100 transition-colors">
                                        <i class="fas fa-link mr-2"></i> Link Zoom
                                    </a>
                                    <!-- <a href="#" class="inline-flex items-center px-4 py-2 bg-green-50 text-green-700 text-sm font-semibold rounded-lg hover:bg-green-100 transition-colors">
                                        <i class="fab fa-whatsapp mr-2"></i> Grup WA
                                    </a> -->
                                    @if($reg->certificate)
                                        <a href="{{ route('certificate.view', $reg->certificate->cert_id) }}" 
                                        target="_blank"
                                        class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 text-sm font-semibold rounded-lg hover:bg-blue-100 transition-colors">
                                            <i class="fas fa-certificate mr-2"></i> Lihat Sertifikat
                                        </a>
                                    @else
                                        <span class="inline-flex items-center px-4 py-2 bg-gray-50 text-gray-400 text-sm font-semibold rounded-lg cursor-not-allowed">
                                            <i class="fas fa-clock mr-2"></i> Belum Terbit
                                        </span>
                                    @endif
                                @elseif($currentStatus == 'pending_verification')
                                    <p class="text-xs text-gray-500 italic flex items-center">
                                        <i class="fas fa-info-circle mr-1.5"></i> Admin sedang memverifikasi data Anda.
                                    </p>
                                @endif

                                @if($currentStatus === 'pending_payment')
                                    <a href="{{ route('registration.payment', [
                                        'registration' => $reg->id,
                                        'event' => $reg->event->id,
                                        'packet' => $reg->packet->id ?? null
                                    ]) }}" 
                                    class="ml-auto text-sm font-medium text-gray-500 hover:text-primary transition-colors flex items-center">
                                        Lanjutkan Pembayaran 
                                        <i class="fas fa-arrow-right ml-1.5 text-xs"></i>
                                    </a>
                                @else
                                    <a href="{{ route('myevents.show', $reg->uuid ?? $reg->id) }}" 
                                       class="ml-auto text-sm font-medium text-gray-500 hover:text-primary transition-colors flex items-center">
                                        Lihat Detail <i class="fas fa-arrow-right ml-1.5 text-xs"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-8">
        {{ $registrations->links() }}
    </div>

    @else
        {{-- EMPTY STATE --}}
        <div class="text-center py-16 bg-white rounded-2xl border border-dashed border-gray-200">
            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="far fa-calendar-times text-3xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">Belum ada event</h3>
            <p class="text-gray-500 text-sm mb-6">Anda belum mendaftar ke webinar manapun.</p>
            <a href="{{ route('registration.create') }}" class="inline-flex items-center px-6 py-3 bg-primary text-white font-semibold rounded-xl hover:bg-primary/90 shadow-md transition-all">
                Daftar Sekarang
            </a>
        </div>
    @endif

</div>
@endsection
