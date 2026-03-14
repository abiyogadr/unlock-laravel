@props(['course', 'progress' => null])

<article {{ $attributes->merge(['class' => 'group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col']) }}>
    {{-- Thumbnail --}}
    <div class="relative h-44 bg-gray-100 overflow-hidden">
        @if($course->thumbnail_path)
            <img src="{{ asset('storage/'.$course->thumbnail_path) }}" 
                 alt="{{ $course->title }}" 
                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                 onerror="this.src='{{ asset('assets/images/course-placeholder.jpg') }}'">

        @else
            <img src="{{ asset('assets/images/course-placeholder.jpg') }}" 
                 alt="Course placeholder" 
                 class="w-full h-full object-cover">
        @endif

        {{-- Price/Status Badge --}}
        <span class="absolute top-3 right-3 px-2.5 py-1 text-[11px] font-bold rounded-full shadow-sm
            {{ $course->is_free ? 'bg-green-500/90 text-white' : 'bg-secondary text-white' }}">
            {{ $course->is_free ? 'GRATIS' : 'Rp ' . number_format($course->price, 0, ',', '.') }}
        </span>

        {{-- Level Badge --}}
        <span class="absolute top-3 left-3 px-2.5 py-1 text-[10px] font-semibold rounded-full bg-black/70 text-white backdrop-blur-sm">
            {{ strtoupper($course->level_label) }}
        </span>
    </div>

    {{-- Content --}}
    <div class="p-5 flex flex-col flex-grow">
        <div class="min-h-[3rem] mb-2">
            <h3 class="text-base font-semibold text-gray-900 line-clamp-2 group-hover:text-primary transition">
                {{ $course->title }}
            </h3>
        </div>

        <div class="text-xs text-gray-600 mb-3 space-y-1.5 flex-grow">
            <div class="flex items-center">
                <i class="fas fa-clock w-4 mr-2"></i>
                <span>{{ $course->formatted_duration }}</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-graduation-cap w-4 mr-2"></i>
                <span>E-Course</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-microphone w-4 mr-2"></i>
                <span class="truncate">{{ $course->speaker->speaker_name ?? 'Speaker belum ditentukan' }}</span>
            </div>
        </div>

        {{-- Progress Bar --}}
        @if($progress && $progress > 0)
            <div class="px-5 pb-2">
                <div class="flex justify-between text-[10px] font-bold text-gray-400 uppercase mb-1">
                    <span>Progress</span>
                    <span>{{ number_format($progress) }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5">
                    <div class="bg-primary h-1.5 rounded-full" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        @endif

        <div class="mt-auto">
            <a href="{{ route('ecourse.show', $course->slug) }}" 
               class="block w-full text-center text-sm font-semibold bg-primary text-white py-2.5 rounded-xl hover:bg-purple-900 transition">
                Lihat Detail
            </a>
        </div>
    </div>
</article>
