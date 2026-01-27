@extends('layouts.app')

@section('title', $event->event_title)

@section('content')
{{-- Hero Section with KV --}}
<section class="relative bg-gradient-to-br from-primary to-purple-900 text-white py-8 md:py-16 px-4 overflow-hidden">
    <div class="absolute inset-0">
        @if($event->kv_image)
            <img src="{{ asset('storage/'.$event->kv_image) }}" 
                 alt="{{ $event->event_title }}"
                 class="w-full h-full object-cover opacity-20">
        @else
            <div class="w-full h-full bg-gradient-to-br from-purple-700 to-purple-900"></div>
        @endif
        <div class="absolute inset-0 bg-black opacity-40"></div>
    </div>
    
    <div class="max-w-4xl mx-auto text-center relative z-10">
        <h1 class="text-3xl md:text-5xl font-extrabold mb-4 leading-tight tracking-tight bg-gradient-to-r from-white to-gray-200 bg-clip-text text-transparent">
            {{ $event->event_title }}
        </h1>
        
        <div class="flex flex-col md:flex-row items-center justify-center gap-4 md:gap-6 text-sm md:text-base font-light">
            <div class="flex items-center gap-2">
                <i class="fas fa-calendar-alt text-lg"></i>
                <span>{{ $event->date_start->format('d M Y') }}</span>
            </div>
            @if($event->date_end <> $event->date_end)
                <span class="hidden md:block">–</span>
                <div class="flex items-center gap-2">
                    <i class="fas fa-calendar-check text-lg"></i>
                    <span>{{ $event->date_end->format('d M Y') }}</span>
                </div>
            @endif
            <span class="hidden md:block">|</span>
            <div class="flex items-center gap-2">
                <i class="fas fa-clock text-lg"></i>
                <span>{{ $event->time_start }} – {{ $event->time_end }} WIB</span>
            </div>
        </div>

        {{-- Classification Badge --}}
        <div class="mt-6">
            <span class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider
                         {{ $event->classification == 'gratis' 
                             ? 'bg-green-100 text-green-700 border border-green-200' 
                             : 'bg-orange-100 text-orange-700 border border-orange-200' }}">
                <i class="fas fa-tag mr-1.5"></i>
                {{ ucfirst($event->classification) }}
            </span>
        </div>
    </div>
</section>

{{-- Main Content --}}
<section class="py-8 md:py-16 px-4 bg-gray-50">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 md:p-10">
            
            {{-- KV CARD --}}
            <div class="mb-4 md:mb-10">
                <div class="bg-gray-50 border border-gray-100 rounded-2xl p-3">
                    @if($event->kv_path)
                        <a href="{{ asset('storage/'.$event->kv_path) }}"
                        target="_blank"
                        class="block overflow-hidden rounded-xl group bg-gray-100">
                            <div class="flex items-center justify-center w-full">
                                <img src="{{ asset('storage/'.$event->kv_path) }}"
                                    alt="{{ $event->event_title }}"
                                    class="w-full h-auto max-h-[450px] md:max-h-[600px] object-contain 
                                            transition-transform duration-500 group-hover:scale-[1.01]">
                            </div>
                        </a>
                        <p class="mt-2 text-[11px] text-gray-500 text-right italic">
                            Klik gambar untuk memperbesar
                        </p>
                    @else
                        <div class="h-56 md:h-64 flex flex-col items-center justify-center
                                    rounded-xl bg-gradient-to-br from-gray-50 to-gray-200">
                            <i class="fas fa-image text-4xl text-gray-300 mb-2"></i>
                            <p class="text-xs text-gray-500">
                                KV belum tersedia untuk event ini.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Speakers Section --}}
            <div class="mb-4 md:mb-10">
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-600 mb-4 flex items-center">
                        <i class="fas fa-microphone mr-2 text-primary"></i>
                        Speaker
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 space-y-2">
                        @foreach($event->speakers as $speaker)
                            <div class="border-b border-gray-200 md:pb-2 last:border-b-0">
                                <div class="flex items-center mb-1 md:mb-2">
                                    <i class="fas fa-user-circle text-primary text-lg mr-3"></i>
                                    <h4 class="font-semibold text-gray-800 text-sm md:text-base">
                                        {{ $speaker->speaker_name }}
                                    </h4>
                                </div>
                                @if($speaker->position)
                                    <p class="text-2xs md:text-xs text-gray-600 mb-1 ml-7">
                                        <i class="fas fa-briefcase mr-1 text-gray-400"></i>
                                        {{ $speaker->position }} @if($speaker->company) at {{ $speaker->company }} @endif
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Event Details --}}
            <div class="grid md:grid-cols-2 gap-4 md:gap-8 mb-4 md:mb-10">
                {{-- Date & Time --}}
                <div class="bg-gray-50 rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-gray-600 mb-3 flex items-center">
                        <i class="fas fa-calendar-day mr-2 text-primary"></i>
                        Jadwal Event
                    </h3>
                    <div class="space-y-2 text-sm text-gray-700">
                        <div class="flex justify-between">
                            <span class="font-medium">Tanggal Mulai:</span>
                            <span>{{ $event->date_start->format('d M Y') }}</span>
                        </div>
                        @if($event->date_end)
                            <div class="flex justify-between">
                                <span class="font-medium">Tanggal Selesai:</span>
                                <span>{{ $event->date_end->format('d M Y') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="font-medium">Waktu:</span>
                            <span>{{ $event->time_start }} – {{ $event->time_end }} WIB</span>
                        </div>
                    </div>
                </div>

                {{-- Classification & Status --}}
                <div class="bg-gray-50 rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-gray-600 mb-3 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-primary"></i>
                        Detail Event
                    </h3>
                    <div class="space-y-2 text-sm text-gray-700">
                        <div class="flex justify-between">
                            <span class="font-medium">Klasifikasi:</span>
                            <span class="capitalize">{{ $event->classification }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Status:</span>
                            <span class="capitalize font-semibold 
                                         {{ $event->status == 'open' ? 'text-green-600' : 'text-gray-600' }}">
                                {{ $event->status }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Kode Event:</span>
                            <span class="font-mono text-xs">{{ $event->event_code }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Description --}}
            @if($event->description)
                <div class="mb-10">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-align-left text-primary mr-3"></i>
                        Deskripsi Event
                    </h2>
                    <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
                        {!! nl2br(e($event->description)) !!}
                    </div>
                </div>
            @endif

            {{-- Registration CTA --}}
            <div class="text-center bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-4 md:p-8 border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-2">
                    Siap untuk mengembangkan skill?
                </h3>
                <p class="text-sm text-gray-600 mb-6 max-w-md mx-auto">
                    Daftar sekarang dan dapatkan akses ke materi eksklusif serta sertifikat resmi.
                </p>
                <a href="{{ route('registration.create', ['event_code' => $event->event_code]) }}"
                   class="inline-flex items-center justify-center gap-2
                          bg-secondary hover:bg-orange-600 text-white 
                          px-8 py-3.5 rounded-xl font-semibold text-base
                          transition-all duration-300 shadow-lg hover:shadow-xl
                          transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-orange-300">
                    <i class="fas fa-user-plus"></i>
                    Daftar Sekarang
                </a>
            </div>

        </div>
    </div>
</section>

{{-- Contact Section --}}
<section class="py-16 px-4 bg-white">
    <div class="max-w-4xl mx-auto text-center">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">
            Ada pertanyaan?
        </h2>
        <p class="text-gray-600 mb-8">
            Hubungi kami untuk informasi lebih lanjut tentang event ini.
        </p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="https://wa.me/6285176767623" target="_blank"
               class="inline-flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-5 py-3
                      hover:bg-green-50 hover:border-green-200 hover:text-green-600 transition">
                <i class="fab fa-whatsapp text-2xl text-green-600"></i>
                <div class="text-left">
                    <div class="text-sm font-semibold">WhatsApp</div>
                    <div class="text-xs text-gray-500">0851-7676-7623</div>
                </div>
            </a>
            <a href="https://instagram.com/unlock.indonesia" target="_blank"
               class="inline-flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-5 py-3
                      hover:bg-pink-50 hover:border-pink-200 hover:text-pink-600 transition">
                <i class="fab fa-instagram text-2xl text-pink-600"></i>
                <div class="text-left">
                    <div class="text-sm font-semibold">Instagram</div>
                    <div class="text-xs text-gray-500">@unlock.indonesia</div>
                </div>
            </a>
        </div>
    </div>
</section>
@endsection
