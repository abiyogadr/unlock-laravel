@extends('layouts.app')

@section('title', 'Detail Pendaftaran - ' . $registration->event->event_title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Pesan Sukses --}}
    @if(session('success'))
        <div class="mb-6 flex items-center p-4 bg-green-50 border-l-4 border-green-500 rounded-r-2xl shadow-sm animate-fade-in-down">
            <div class="flex-shrink-0 w-10 h-10 bg-green-500/10 rounded-full flex items-center justify-center text-green-600">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-bold text-green-800">Berhasil!</p>
                <p class="text-xs text-green-700">{{ session('success') }}</p>
            </div>
            <button type="button" class="ml-auto text-green-400 hover:text-green-600 transition-colors" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- 2. Pesan Info (PENTING: Agar info absensi muncul) --}}
    @if(session('info'))
        <div class="mb-6 flex items-center p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-2xl shadow-sm animate-fade-in-down">
            <div class="flex-shrink-0 w-10 h-10 bg-blue-500/10 rounded-full flex items-center justify-center text-blue-600">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-bold text-blue-800">Informasi</p>
                <p class="text-xs text-blue-700">{{ session('info') }}</p>
            </div>
            <button type="button" class="ml-auto text-blue-400 hover:text-blue-600 transition-colors" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- 3. Pesan Error --}}
    @if(session('error') || $errors->any())
        <div class="mb-6 flex items-center p-4 bg-red-50 border-l-4 border-red-500 rounded-r-2xl shadow-sm animate-fade-in-down">
            <div class="flex-shrink-0 w-10 h-10 bg-red-500/10 rounded-full flex items-center justify-center text-red-600">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-bold text-red-800">Ups, terjadi kesalahan!</p>
                <p class="text-xs text-red-700">
                    {{ session('error') ?? 'Mohon periksa kembali formulir Anda.' }}
                </p>
            </div>
            <button type="button" class="ml-auto text-red-400 hover:text-red-600 transition-colors" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- Breadcrumb / Back Button --}}
    <div class="mb-6">
        <a href="@if(auth()->user()->isAdmin()) {{ route('admin.registrations.show_event', $registration->event->id) }} @else {{ route('myevents') }} @endif" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-primary transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Event Saya
        </a>
    </div>

    <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 overflow-hidden border border-gray-100">
        
        {{-- HEADER: Status Banner --}}
        @php
            $statusColors = [
                'pending' => 'bg-yellow-50 text-yellow-700 border-b border-yellow-100',
                'verified' => 'bg-green-50 text-green-700 border-b border-green-100',
                'cancelled' => 'bg-red-50 text-red-700 border-b border-red-100',
                'rejected' => 'bg-red-50 text-red-700 border-b border-red-100',
            ];
            $statusIcon = [
                'pending' => 'fas fa-clock',
                'verified' => 'fas fa-check-circle',
                'cancelled' => 'fas fa-times-circle',
                'rejected' => 'fas fa-times-circle',
            ];
            $statusLabel = [
                'pending' => 'Menunggu Verifikasi',
                'verified' => 'Pendaftaran Diterima',
                'cancelled' => 'Pendaftaran Ditolak',
                'rejected' => 'Pendaftaran Ditolak',
            ];
            $status = $registration->registration_status ?? 'pending';
        @endphp

        <div class="px-6 py-4 {{ $statusColors[$status] }} flex flex-col sm:flex-row justify-between items-center gap-4 text-center sm:text-left">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/60 flex items-center justify-center text-lg shadow-sm">
                    <i class="{{ $statusIcon[$status] }}"></i>
                </div>
                <div>
                    <h2 class="font-bold text-lg">
                        {{ $statusLabel[$status] }}
                    </h2>
                    <p class="text-xs opacity-80">
                        No. Reg: #{{ $registration->registration_code ?? $registration->id }}
                    </p>
                </div>
            </div>

            <div class="flex gap-2">
                {{-- USER: tombol Zoom & Absensi jika verified --}}
                @if($status === 'verified')
                    <a href="{{ $registration->event->link_zoom ?? '#' }}"
                    target="_blank"
                    class="px-4 py-2 bg-white text-green-700 text-sm font-bold rounded-lg shadow-sm hover:shadow-md transition-all">
                        <i class="fas fa-video mr-1"></i> Join Zoom
                    </a>
                    <a href="{{ $registration->event->link_materi ?? '#' }}"
                    target="_blank"
                    class="px-4 py-2 bg-white text-green-700 text-sm font-bold rounded-lg shadow-sm hover:shadow-md transition-all">
                        <i class="fas fa-link mr-1"></i> VB & Materi
                    </a>
                @endif

                {{-- ADMIN: tombol verifikasi jika pending --}}
                @if(auth()->check() && auth()->user()->isAdmin() && $status === 'pending')
                    <div class="flex gap-2">
                        {{-- VERIFIKASI --}}
                        <form action="{{ route('admin.registrations.verify', $registration->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button class="px-4 py-2 bg-green-600 text-white text-sm font-bold rounded-lg hover:bg-green-700">
                                <i class="fas fa-check mr-1"></i> Verifikasi
                            </button>
                        </form>

                        {{-- TOLAK --}}
                        <form action="{{ route('admin.registrations.reject', $registration->id) }}" method="POST"
                            onsubmit="return confirm('Yakin tolak registrasi ini?')">
                            @csrf
                            @method('PATCH')
                            <button class="px-4 py-2 bg-red-600 text-white text-sm font-bold rounded-lg hover:bg-red-700">
                                <i class="fas fa-times mr-1"></i> Tolak
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <div class="p-6 sm:p-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                
                {{-- KOLOM KIRI: Informasi Event (2/3 width) --}}
                <div class="md:col-span-2 space-y-4">
                    
                    {{-- 1. Judul Event --}}
                    <div>
                        <span class="inline-block px-3 py-1 bg-primary/10 text-primary text-xs font-bold uppercase tracking-wider rounded-full mb-3">
                            Webinar
                        </span>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 leading-tight">
                            {{ $registration->event->event_title }}
                        </h1>
                        <div class="mt-4 flex flex-wrap gap-4 text-sm text-gray-600">
                            <div class="flex items-center">
                                <i class="far fa-calendar-alt text-primary mr-2 text-lg"></i>
                                {{ $registration->event->date_start->format('d F Y') }}
                            </div>
                            <div class="flex items-center">
                                <i class="far fa-clock text-primary mr-2 text-lg"></i>
                                {{ $registration->event->time_start }} WIB
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 my-6"></div>

                    {{-- 2. Detail Peserta --}}
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-user-circle text-gray-400 mr-2"></i> Data Peserta
                        </h3>
                        <div class="bg-gray-50 rounded-2xl p-5 grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-sm">
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Nama Lengkap</label>
                                <p class="font-medium text-gray-900">{{ $registration->name }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Email</label>
                                <p class="font-medium text-gray-900">{{ $registration->email }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">WhatsApp</label>
                                <p class="font-medium text-gray-900">{{ $registration->whatsapp }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Profesi</label>
                                <p class="font-medium text-gray-900">{{ $registration->profession }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Domisili</label>
                                <p class="font-medium text-gray-900">{{ $registration->city ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Detail Paket --}}
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-ticket-alt text-gray-400 mr-2"></i> Paket Dipilih
                        </h3>
                        <div class="border border-primary/20 bg-primary/5 rounded-2xl p-5">
                            <div>
                                <p class="text-primary font-bold text-lg">{{ $registration->packet->packet_name }}</p>
                                <a class="text-l font-bold text-orange-500">Harga : </a>
                                <a class="text-l font-bold text-orange-500">
                                    @if($registration->packet->price == 0)
                                        Gratis
                                    @else
                                        {{ number_format($registration->packet->price, 0, ',', '.') }}
                                    @endif
                                </a>
                                <p class="text-gray-500 text-xs mt-1">
                                    <!-- {{ Str::limit($registration->packet->description, 60) }} -->
                                    {!! $registration->packet->description !!}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KOLOM KANAN: Bukti Tugas / Sidebar (1/3 width) --}}
                <div class="space-y-6">
                    
                    {{-- QR Code (Opsional, hiasan visual tiket) --}}
                    <div class="bg-gray-900 text-white rounded-2xl p-6 text-center shadow-lg relative overflow-hidden group">
                        <!-- Dekorasi Background -->
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full blur-xl group-hover:bg-white/20 transition-all"></div>
                        
                        <p class="text-xs text-gray-400 uppercase tracking-widest mb-4">Presensi</p>
                        
                        <!-- Tombol Presensi -->
                         @if($status != 'verified')
                         <a class="w-full bg-white text-gray-900 py-3 px-6 rounded-xl font-bold flex items-center justify-center gap-2 shadow-md mb-4">
                            <i class="fas fa-warning text-xl"></i>
                            <span>Belum Terverifikasi</span>
                        </a>
                         @elseif($registration->is_attended === false)
                        <a href="{{ route('registration.attendance', $registration->registration_code) }}" 
                        class="w-full bg-white text-gray-900 py-3 px-6 rounded-xl font-bold flex items-center justify-center gap-2 hover:bg-gray-200 hover:scale-[1.02] transition-all shadow-md mb-4">
                            <i class="fas fa-fingerprint text-xl"></i>
                            <span>Klik untuk Presensi</span>
                        </a>
                        @else
                        <a class="w-full bg-white text-gray-900 py-3 px-6 rounded-xl font-bold flex items-center justify-center gap-2 shadow-md mb-4">
                            <i class="fas fa-fingerprint text-xl"></i>
                            <span>Sudah Presensi</span>
                        </a>
                        @endif
                        
                        <!-- Kode Registrasi -->
                        <div class="pt-2 border-t border-white/10">
                            <p class="text-[10px] text-gray-500 uppercase mb-1">Kode Registrasi</p>
                            <p class="font-mono text-lg font-bold tracking-widest text-green-400">
                                {{ $registration->registration_code }}
                            </p>
                        </div>
                    </div>

                    {{-- Bukti Upload --}}
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 mb-3 uppercase tracking-wide border-b pb-2">Bukti Tugas / Pembayaran</h3>
                        
                        @if($registration->uploadproofs && count($registration->uploadproofs) > 0)
                            <div class="space-y-3">
                                @foreach($registration->uploadproofs as $proof)
                                    <div class="group relative bg-gray-50 rounded-xl border border-gray-100 overflow-hidden hover:shadow-md transition-all">
                                        <a href="{{ Storage::url($proof->file_path) }}" target="_blank" class="flex items-center p-3">
                                            <div class="w-10 h-10 rounded-lg bg-gray-200 flex-shrink-0 overflow-hidden">
                                                <img src="{{ Storage::url($proof->file_path) }}" class="w-full h-full object-cover">
                                            </div>
                                            <div class="ml-3 min-w-0">
                                                {{-- Mapping Label Tugas agar lebih cantik --}}
                                                @php
                                                    $labels = [
                                                        'follow_ig' => 'Follow Instagram',
                                                        'follow_yt' => 'Subscribe YouTube',
                                                        'follow_tt' => 'Follow Tiktok',
                                                        'tag_friends' => 'Tag Teman',
                                                        'repost_story' => 'Repost Story',
                                                        'repost_groups' => 'Repost Grup',
                                                        'repost_wa_story' => 'Repost WA Story',
                                                        'transfer' => 'Bukti Transfer'
                                                    ];
                                                    $cleanType = str_replace(['upload_', 'proof_'], '', $proof->category);
                                                @endphp
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ $labels[$cleanType] ?? ucfirst($cleanType) }}</p>
                                                <p class="text-xs text-blue-500 group-hover:underline">Lihat Gambar</p>
                                            </div>
                                            <div class="ml-auto">
                                                <i class="fas fa-external-link-alt text-gray-300 group-hover:text-gray-500"></i>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                                <p class="text-xs text-gray-400 italic">Tidak ada bukti yang diupload</p>
                            </div>
                        @endif
                    </div>

                    {{-- Help Center --}}
                    <div class="bg-blue-50 rounded-xl p-4 text-center">
                        <p class="text-xs text-blue-600 font-semibold mb-2">Butuh Bantuan?</p>
                        <a href="https://wa.me/6285176767623" target="_blank" class="text-sm font-bold text-blue-700 hover:underline">
                            <i class="fab fa-whatsapp mr-1"></i> Hubungi Admin
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
