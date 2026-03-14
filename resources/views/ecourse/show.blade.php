@extends('ecourse.layouts.app')

@section('title', $course->title . ' - Unlock E-Course')

@section('breadcrumb')
    <li><i class="fas fa-chevron-right text-[10px] mx-1"></i></li>
    <li><a href="{{ route('ecourse.catalog') }}" class="hover:text-primary transition">Catalog</a></li>
    <li><i class="fas fa-chevron-right text-[10px] mx-1"></i></li>
    <li class="text-gray-800 font-medium">{{ $course->title }}</li>
@endsection

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 max-w-8xl"> {{-- Menggunakan max-w-6xl untuk tampilan lebih rapat --}}
    <div class="grid lg:grid-cols-3 gap-8 mb-12"> {{-- Mengubah menjadi 3 kolom: 2 untuk konten, 1 untuk sidebar --}}
        <!-- Kolom Kiri (Main Content): span 2 kolom -->
        <div class="lg:col-span-2 space-y-10"> {{-- Meningkatkan space-y untuk jarak antar bagian --}}
            {{-- Thumbnail gambar utama diletakkan di bawah detail kursus seperti di MySkill --}}
            <section>
                <div class="aspect-video bg-gray-100 rounded-2xl overflow-hidden shadow-lg">
                    <img
                        src="{{ $course->thumbnail_url }}"
                        alt="{{ $course->title }}"
                        class="w-full h-full object-cover"
                        onerror="this.src='{{ asset('assets/images/course-placeholder.jpg') }}'"
                    >
                </div>
            </section>

            <!-- Hero Section: Title, Meta, Speaker, Short Description -->
            <div class="space-y-6">
                {{-- Level, Status, dan Kategori --}}
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 bg-primary/10 text-primary text-xs font-bold rounded-full uppercase tracking-wide">
                        {{ $course->level_label }}
                    </span>
                    <span class="px-3 py-1 bg-primary/10 text-primary text-xs font-bold rounded-full">
                        {{ $course->is_free ? 'GRATIS' : 'Berbayar' }}
                    </span>
                    @if($course->categories->count() > 0)
                        {{-- Hanya tampilkan satu kategori utama untuk minimalis --}}
                        @foreach($course->categories->take(1) as $category) 
                            <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full">
                                {{ $category->name }}
                            </span>
                        @endforeach
                    @endif
                </div>

                <h1 class="text-2xl lg:text-3xl font-extrabold text-gray-900 leading-tight">
                    {{ $course->title }}
                </h1>
                
                @if($course->short_description)
                    <p class="text-lg text-gray-700 leading-relaxed">{{ $course->short_description }}</p>
                @endif

                <!-- Speaker -->
                @if($course->speaker)
                    <div class="flex items-center pt-4"> {{-- Padding top untuk memisahkan dari deskripsi --}}
                        <div>
                            <div class="font-semibold text-gray-800 mb-2">
                                {{ $course->speaker->prefix_title }} 
                                {{ $course->speaker->speaker_name }} 
                                {{ $course->speaker->suffix_title }}
                            </div>
                            <div class="text-sm text-gray-600 flex items-center">
                                <i class="fas fa-briefcase text-primary mr-1"></i> {{-- Warna ikon --}}
                                {{ $course->speaker->job ?? 'Pembicara Profesional' }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Course Description -->
            <section>
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Tentang Kelas Ini</h2>
                <div class="prose prose-lg max-w-none text-gray-700">
                    {!! $course->description !!}
                </div>
            </section>

            <!-- Learning Objectives (Asumsi ada properti 'objectives' di Course model, berbentuk array) -->
            {{-- Perbaiki akses ke properti objectives, seharusnya langsung dari $course --}}
            @if(isset($course->objectives) && is_array($course->objectives) && count($course->objectives) > 0) 
                <section>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Apa yang Akan Kamu Pelajari</h2>
                    <ul class="space-y-3">
                        @foreach($course->objectives as $objective) {{-- Ubah dari $course->modules->objectives --}}
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-primary mt-1 mr-3 flex-shrink-0 text-lg"></i> {{-- Ikon check dengan warna primary --}}
                                <span class="text-gray-700 text-lg">{{ $objective }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

        </div>

        <!-- Kolom Kanan (Sidebar): Action Card + Modules -->
        <div class="lg:col-span-1">
            <div class="lg:sticky lg:top-8 space-y-8"> {{-- Membuat konten sidebar sticky pada desktop --}}

                <!-- Action/Enroll Card -->
                <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                    <!-- Price -->
                    @if(!$course->is_free)
                        <div class="mb-4">
                            <span class="text-sm font-semibold text-gray-600 block mb-1">Harga Kelas</span>
                            <span class="text-4xl font-extrabold text-primary">Rp {{ number_format($course->price, 0, ',', '.') }}</span>
                        </div>
                    @else
                        <div class="mb-4">
                            <span class="text-sm font-semibold text-gray-600 block mb-1">Harga Kelas</span>
                            <span class="text-4xl font-extrabold text-primary">GRATIS</span>
                        </div>
                    @endif

                    <!-- Enroll/Continue Button -->
                    @auth
                        @php
                            $userProgress = auth()->user()->ecourses()
                                ->where('course_id', $course->id)
                                ->first()?->progress ?? 0;
                            // Pastikan $firstModuleSlug terdefinisi dengan aman
                            $firstModuleSlug = $course->modules->first()->slug ?? null;
                        @endphp
                        
                        @if($firstModuleSlug) {{-- Hanya tampilkan tombol jika ada modul --}}
                            @if($userProgress > 0)
                                <a href="{{ route('ecourse.player', [$course->slug, $firstModuleSlug]) }}" 
                                class="block w-full bg-primary text-white py-4 px-6 rounded-2xl font-bold text-lg text-center hover:bg-purple-800 transition-colors duration-300">
                                    <i class="fas fa-play mr-3"></i>
                                    Lanjutkan Belajar ({{ number_format($userProgress, 0) }}%)
                                </a>
                            @else
                                <a href="{{ route('ecourse.player', [$course->slug, $firstModuleSlug]) }}" 
                                class="block w-full bg-primary text-white py-4 px-6 rounded-2xl font-bold text-lg text-center hover:bg-purple-800 transition-colors duration-300">
                                    <i class="fas fa-arrow-right mr-3"></i>
                                    {{ $course->is_free ? 'Mulai Kelas' : 'Daftar Kelas' }}
                                </a>
                            @endif
                        @else
                             <p class="text-center text-gray-500 py-4">Belum ada modul tersedia.</p>
                        @endif
                    @else
                        <a href="{{ route('login') }}" 
                           class="block w-full bg-primary text-white py-4 px-6 rounded-2xl font-bold text-lg text-center hover:bg-purple-800 transition-colors duration-300">
                            <i class="fas fa-sign-in-alt mr-3"></i>
                            Login untuk Mulai Belajar
                        </a>
                    @endauth

                    <!-- Quick Info - Dipindahkan ke sini -->
                    <div class="grid grid-cols-1 gap-4 mt-6 border-t border-gray-100 pt-6">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-clock text-primary"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-800">{{ $course->formatted_duration }}</div>
                                <div class="text-sm text-gray-600">Durasi Total</div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-{{ $course->modules->count() > 10 ? 'layers' : 'layer-group' }} text-primary"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-800">{{ $course->modules->count() }} Modul</div>
                                <div class="text-sm text-gray-600">Materi Lengkap</div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-certificate text-primary"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-800">Sertifikat</div>
                                <div class="text-sm text-gray-600">Resmi & Digital</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daftar Modul (Kurikulum) -->
                <section class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-xl font-bold text-gray-800 mb-2 flex items-center">
                            <i class="fas fa-list mr-3 text-primary"></i>
                            Kurikulum Kelas ({{ $course->modules->count() }})
                        </h2>
                        <p class="text-gray-600 text-sm">Pelajari setiap bagian secara berurutan</p>
                    </div>
                    
                    <div class="divide-y divide-gray-100">
                        @auth
                            @php
                                // Ambil data user course untuk cek status modul
                                $userCourseData = auth()->user()->ecourses()
                                    ->where('course_id', $course->id)
                                    ->first();
                                
                                // Build module status map dengan progress
                                $moduleStatus = [];
                                if ($userCourseData) {
                                    $userCourseModules = $userCourseData->userCourseModules()
                                        ->get()
                                        ->keyBy('course_module_id');
                                    
                                    foreach ($course->modules as $mod) {
                                        $ucm = $userCourseModules[$mod->id] ?? null;
                                        $moduleStatus[$mod->id] = [
                                            'is_completed' => $ucm?->is_completed ?? false,
                                            'progress' => $ucm?->progress ?? 0,
                                        ];
                                    }
                                }
                            @endphp
                        @endauth

                        @foreach($course->modules as $index => $module)
                            @php
                                $isCompleted = false;
                                $progress = 0;
                                if (Auth::check() && isset($moduleStatus[$module->id])) {
                                    $isCompleted = $moduleStatus[$module->id]['is_completed'];
                                    $progress = $moduleStatus[$module->id]['progress'];
                                }
                            @endphp
                            <a href="{{ route('ecourse.player', [$course->slug, $module->slug]) }}" 
                               class="block px-6 py-4 hover:bg-gray-50 transition-colors flex items-start group">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center mr-4 flex-shrink-0 {{ $isCompleted ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600 group-hover:bg-primary/20 group-hover:text-primary' }}">
                                    @if($isCompleted)
                                        <i class="fas fa-check text-lg font-bold"></i>
                                    @else
                                        <span class="text-lg font-bold">{{ $index + 1 }}</span>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-800 group-hover:text-primary transition-colors">{{ $module->title }}</h4>
                                    <p class="text-sm text-gray-600">{{ $module->formatted_duration ?? 'Durasi tidak diketahui' }}</p>
                                    
                                    <!-- Progress Bar untuk modul yang in progress -->
                                    @if(!$isCompleted && $progress > 0 && Auth::check())
                                        <div class="mt-2 bg-gray-200 rounded-full h-2 overflow-hidden">
                                            <div class="bg-primary h-full transition-all duration-300" style="width: {{ $progress }}%"></div>
                                        </div>
                                        <p class="text-xs text-primary font-semibold mt-1">{{ round($progress) }}% selesai</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 mt-2">
                                    @if($isCompleted)
                                        <span class="text-xs font-semibold text-green-600 bg-green-100 px-3 py-1 rounded-full">Selesai</span>
                                    @elseif($progress > 0 && Auth::check())
                                        <span class="text-xs font-semibold text-primary bg-primary/10 px-3 py-1 rounded-full">{{ round($progress) }}%</span>
                                    @endif
                                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-primary ml-2 transition-colors"></i>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            </div> {{-- End sticky div --}}
        </div>
    </div>

    <!-- CTA Final (Simple and Clean) -->
    <div class="text-center py-16 bg-gray-50 rounded-2xl mt-12">
        <h3 class="text-3xl font-bold text-gray-800 mb-6">Siap untuk Menguasai Skill Ini?</h3>
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center max-w-lg mx-auto">
            @auth
                <a href="{{ route('ecourse.player', [$course->slug, $firstModuleSlug ?? '#']) }}" 
                   class="flex-1 bg-primary text-white py-4 px-8 rounded-2xl font-bold text-lg hover:bg-purple-800 transition-colors duration-300 flex items-center justify-center">
                    <i class="fas fa-rocket mr-3"></i>
                    {{ $userProgress > 0 ? 'Lanjutkan Kelas' : 'Mulai Sekarang' }}
                </a>
            @else
                <a href="{{ route('login') }}" class="flex-1 bg-white text-primary py-4 px-8 rounded-2xl font-bold text-lg hover:bg-gray-100 transition-colors duration-300 border-2 border-primary">
                    <i class="fas fa-sign-in-alt mr-3"></i>
                    Login untuk Akses
                </a>
            @endauth
        </div>
    </div>
</div>
@endsection
