@props([
    'event',
    'badgePosition' => 'right',
    'mediaClass' => 'aspect-video',
    'imageClass' => 'w-full h-full object-cover transition-transform duration-500 group-hover:scale-105',
])

@php
    $badgePositionClass = $badgePosition === 'left' ? 'left-3' : 'right-3';
@endphp

<article {{ $attributes->merge(['class' => 'group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col']) }}>
    <div class="relative bg-gray-100 overflow-hidden {{ $mediaClass }}">
        @if($event->kv_path)
            <img src="{{ asset('storage/'.$event->kv_path) }}"
                alt="{{ $event->event_title }}"
                class="{{ $imageClass }}">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-gray-50 to-gray-200">
                <i class="fas fa-image text-3xl text-gray-300 mb-2"></i>
                <span class="text-xs text-gray-400">Tidak ada gambar</span>
            </div>
        @endif

        <span class="absolute top-3 {{ $badgePositionClass }} px-2 py-1 text-[11px] font-semibold rounded-full
                    {{ $event->status === 'open' ? 'bg-green-500/90 text-white' : 'bg-gray-500/90 text-white' }}">
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
            </span>
        </div>

        <div class="mt-auto">
            <a href="{{ route('event.show', $event->event_code) }}"
               class="block w-full text-center text-sm font-semibold bg-primary text-white py-2.5 rounded-xl hover:bg-purple-900 transition">
                Lihat Detail
            </a>
        </div>
    </div>
</article>