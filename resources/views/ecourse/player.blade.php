@extends('ecourse.layouts.app')

@section('title', $course->title . ' - Unlock E-Course')

@section('breadcrumb')
    <li><i class="fas fa-chevron-right text-[10px] mx-1"></i></li>
    <li><a href="{{ route('ecourse.catalog') }}" class="hover:text-primary transition">Catalog</a></li>
    <li><i class="fas fa-chevron-right text-[10px] mx-1"></i></li>
    <li class="text-gray-800 font-medium">{{ $course->title }}</li>
@endsection

@section('content')
    <div class="min-h-screen bg-black">
        <!-- Video Container -->
        <div class="relative aspect-video bg-black max-w-6xl mx-auto">
            @php
                $isYouTube = true;
                $youTubeId = null;

                // Periksa apakah video_path adalah URL YouTube
                if (Str::startsWith($module->video_path, [
                    'https://www.youtube.com/',
                    'https://youtube.com/',
                    'https://youtu.be/',
                    'http://www.youtube.com/',
                    'http://youtu.be/',
                    'https://youtube.com/live/',
                    'http://youtube.com/live/',
                ])) {
                    $isYouTube = true;
                    // Ekstrak ID video YouTube
                    // Regex diperbarui untuk menangani format /live/
                    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|live\/)|youtu\.be\/)([^"&?\/ ]{11})/i', $module->video_path, $matches);
                    $youTubeId = $matches[1] ?? null;
                }
            @endphp

            @if($isYouTube && $youTubeId)
                {{-- Tampilkan iframe YouTube jika itu adalah tautan YouTube --}}
                <div id="youtube-player" class="w-full h-full">
                    <iframe
                        id="youtube-iframe"
                        class="w-full h-full"
                        src="https://www.youtube.com/embed/{{ $youTubeId }}?enablejsapi=1&rel=0&modestbranding=1"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                    ></iframe>
                </div>
            @else
                {{-- Tampilkan elemen video HTML5 jika itu adalah file lokal --}}
                <video id="course-video" class="w-full h-full" controls>
                    <source src="{{ asset('storage/'.$module->video_path) }}" type="video/mp4">
                    Browser Anda tidak mendukung tag video.
                </video>

                <!-- Custom Controls Overlay (hanya untuk video lokal) -->
                <div id="video-controls" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-6 opacity-0 hover:opacity-100 transition-opacity duration-300">
                    <!-- Progress Bar -->
                    <div class="mb-4">
                        <input type="range" id="progress-bar" class="w-full h-2 bg-white/20 rounded-lg appearance-none cursor-pointer" min="0" max="100" value="0">
                    </div>

                    <div class="flex items-center justify-between text-white">
                        <div class="flex items-center space-x-4">
                            <button id="play-pause-btn" class="text-2xl hover:text-primary transition">
                                <i class="fas fa-play"></i>
                            </button>
                            <span id="time-display" class="text-sm">00:00 / 00:00</span>
                        </div>

                        <div class="flex items-center space-x-4">
                            <button id="speed-btn" class="px-3 py-1 text-sm bg-white/20 rounded-lg hover:bg-white/30 transition">
                                1x
                            </button>
                            <button id="fullscreen-btn" class="text-xl hover:text-primary transition">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Content Below Video -->
        <div class="bg-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 max-w-7xl">
                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Main Content -->
                    <div class="lg:col-span-2">
                        <!-- Course Header -->
                        <div class="mb-6">
                            <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $module->title }}</h1>
                            <p class="text-gray-600">{{ $course->title }} - Modul {{ $module->order_num }} dari {{ $course->modules->count() }}</p>
                        </div>

                        <!-- Module Tabs -->
                        <div class="border-b border-gray-200 mb-6">
                            <nav class="flex space-x-8">
                                <button class="tab-btn active py-4 text-primary font-semibold" data-tab="overview">
                                    Overview
                                </button>
                                <button class="tab-btn py-4 text-gray-500 hover:text-gray-700 font-semibold" data-tab="materials">
                                    Materi
                                </button>
                                <button class="tab-btn py-4 text-gray-500 hover:text-gray-700 font-semibold" data-tab="discussion">
                                    Diskusi
                                </button>
                            </nav>
                        </div>

                        <!-- Tab Content -->
                        <div id="overview-tab" class="tab-content">
                            <div class="prose max-w-none">
                                {!! $module->description !!}
                            </div>

                            <!-- Learning Objectives -->
                            @if($module->objectives)
                                <div class="mt-8">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Tujuan Pembelajaran</h3>
                                    <ul class="space-y-2">
                                        @foreach($module->objectives as $objective)
                                            <li class="flex items-start">
                                                <i class="fas fa-check-circle text-primary mt-1 mr-3"></i>
                                                <span class="text-gray-700">{{ $objective }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Action Buttons -->
                            <div class="mt-8 flex gap-3">
                                @if($nextModule)
                                    <a href="{{ route('ecourse.player', [$course->slug, $nextModule->slug]) }}" class="flex-1 px-6 py-3 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition flex items-center justify-center">
                                        <i class="fas fa-arrow-right mr-2"></i>
                                        Modul Berikutnya
                                    </a>
                                @else
                                    <button disabled class="flex-1 px-6 py-3 bg-gray-200 text-gray-400 rounded-lg font-semibold cursor-not-allowed flex items-center justify-center">
                                        <i class="fas fa-check-double mr-2"></i>
                                        Kursus Selesai
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div id="materials-tab" class="tab-content hidden">
                            <div class="space-y-4">
                                @foreach($module->materials as $material)
                                    <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                        <i class="fas fa-file-{{ $material->type }} text-2xl text-primary mr-4"></i>
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-800">{{ $material->title }}</h4>
                                            {{-- Menggunakan file_size sesuai dengan definisi di migrasi --}}
                                            <p class="text-sm text-gray-500">{{ $material->file_size }}</p> 
                                        </div>
                                        <a href="{{ asset('storage/'.$material->file_path) }}" download class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-purple-900 transition">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div id="discussion-tab" class="tab-content hidden">
                            <div class="space-y-6">
                                <!-- Comment Form -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <textarea placeholder="Tanyakan sesuatu tentang modul ini..." class="w-full p-3 border border-gray-200 rounded-lg resize-none" rows="3"></textarea>
                                    <button class="mt-2 px-6 py-2 bg-primary text-white rounded-lg hover:bg-purple-900 transition font-semibold">
                                        Kirim Pertanyaan
                                    </button>
                                </div>

                                {{-- Relasi 'comments' tidak dimuat/didefinisikan di CourseModule model.
                                     Anda perlu mendefinisikan relasi ini di model CourseModule
                                     dan memuatnya di controller jika ingin menampilkannya. --}}
                                {{-- @foreach($module->comments as $comment)
                                    <div class="border-b border-gray-100 pb-4">
                                        <div class="flex items-start">
                                            <img src="{{ $comment->user->avatar }}" alt="{{ $comment->user->name }}" class="w-10 h-10 rounded-full mr-3">
                                            <div class="flex-1">
                                                <div class="flex items-center mb-1">
                                                    <h4 class="font-semibold text-gray-800 mr-2">{{ $comment->user->name }}</h4>
                                                    <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                                </div>
                                                <p class="text-gray-700">{{ $comment->content }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach --}}
                                <p class="text-gray-500 text-center">Fitur diskusi belum tersedia untuk modul ini.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-1">
                        <!-- Module List -->
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Daftar Modul</h3>
                            <ul class="space-y-2 module-list">
                                @foreach($modules as $item)
                                    @php
                                        $mod = $item['module'];
                                        $progress = $item['progress'];
                                        $isCompleted = $item['is_completed'];
                                    @endphp
                                    <li>
                                        <a href="{{ route('ecourse.player', ['course' => $course->slug, 'module' => $mod->slug]) }}" 
                                           class="flex items-center p-3 rounded-lg {{ $mod->id === $module->id ? 'bg-primary text-white' : 'bg-gray-50 text-gray-700 hover:bg-gray-100' }} transition"
                                           data-module-id="{{ $mod->id }}">
                                            <!-- Status Icon -->
                                            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center mr-3 module-icon {{ $mod->id === $module->id ? 'bg-white/20' : 'bg-gray-200' }}">
                                                @if($isCompleted)
                                                    <i class="fas fa-check text-xs font-bold {{ $mod->id === $module->id ? 'text-white' : 'text-green-500' }}"></i>
                                                @else
                                                    <span class="text-xs font-bold">{{ $mod->order_num }}</span>
                                                @endif
                                            </div>
                                            
                                            <!-- Module Info -->
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <p class="font-semibold text-sm">{{ $mod->title }}</p>
                                                    <!-- Progress Badge -->
                                                    @if(!$isCompleted && $progress > 0)
                                                        <span class="text-xs font-bold {{ $mod->id === $module->id ? 'text-white/80' : 'text-primary' }}">{{ round($progress) }}%</span>
                                                    @elseif($isCompleted)
                                                        <span class="text-xs font-bold {{ $mod->id === $module->id ? 'text-white/80' : 'text-green-600' }}">Selesai</span>
                                                    @endif
                                                </div>
                                                <p class="text-xs {{ $mod->id === $module->id ? 'text-white/70' : 'text-gray-500' }}">{{ $mod->formatted_duration }}</p>
                                            </div>
                                        </a>

                                        <!-- Progress Bar untuk modul yang in progress -->
                                        @if(!$isCompleted && $progress > 0)
                                            <div class="px-3 py-2 module-progress-bar">
                                                <div class="bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                                    <div class="bg-primary h-full transition-all duration-300" style="width: {{ $progress }}%"></div>
                                                </div>
                                            </div>
                                            <p class="text-xs text-primary font-semibold px-3 pb-2 module-progress-text">{{ round($progress) }}% selesai</p>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- Course Progress -->
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Progress Kursus</h3>
                            <div class="mb-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-2">
                                    <span>Selesai</span>
                                    {{-- Menggunakan progress dari $userCourse --}}
                                    <span>{{ number_format($userCourse->progress, 0) }}%</span> 
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    {{-- Menggunakan progress dari $userCourse --}}
                                    <div class="bg-primary h-3 rounded-full transition-all duration-300" style="width: {{ $userCourse->progress }}%"></div>
                                </div>
                            </div>
                            {{-- Menggunakan completed_modules dari $userCourse dan total modul dari $course --}}
                            <p class="text-sm text-gray-500">{{ $userCourse->completed_modules }}/{{ $course->modules->count() }} modul selesai</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ===== SHARED FUNCTIONS (untuk YouTube dan Local Video) =====
        
        // Function to update module sidebar status to completed
        function updateModuleSidebarStatus() {
            const currentModuleId = {{ $module->id }};
            
            // Find module item in sidebar
            const moduleItems = document.querySelectorAll('.module-list a');
            
            moduleItems.forEach(item => {
                const modId = item.getAttribute('data-module-id');
                
                if (parseInt(modId) === currentModuleId) {
                    // Update icon circle to checkmark
                    const iconCircle = item.querySelector('.module-icon');
                    if (iconCircle) {
                        iconCircle.innerHTML = '<i class="fas fa-check text-xs font-bold"></i>';
                        iconCircle.className = 'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center mr-3 module-icon bg-white/20 text-white';
                    }
                    
                    // Update badge to show "Selesai" - find span with percentage
                    const badgeSpan = item.querySelector('.flex-1 .flex span:last-of-type');
                    if (badgeSpan) {
                        badgeSpan.textContent = 'Selesai';
                        badgeSpan.className = 'text-xs font-bold text-white/80';
                    }
                    
                    // Remove progress bar and percentage if exists (they are siblings, not children)
                    const progressBar = item.parentElement.querySelector('.module-progress-bar');
                    if (progressBar) {
                        progressBar.remove();
                    }
                    const progressText = item.parentElement.querySelector('.module-progress-text');
                    if (progressText) {
                        progressText.remove();
                    }
                }
            });
        }

        // Show notification helper
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white font-semibold z-50 ${
                type === 'success' ? 'bg-green-500' : type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
            }`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // ===== YOUTUBE VIDEO PLAYER =====
        @if($isYouTube && $youTubeId)
            let youtubePlayer;
            let lastProgress = 0;
            let lastSeekTime = 0;
            let youtubeModuleCompleted = false;

            // Load YouTube IFrame API
            const tag = document.createElement('script');
            tag.src = "https://www.youtube.com/iframe_api";
            const firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

            // YouTube API ready callback
            window.onYouTubeIframeAPIReady = function() {
                youtubePlayer = new YT.Player('youtube-iframe', {
                    events: {
                        'onReady': onYoutubePlayerReady,
                        'onStateChange': onYoutubePlayerStateChange
                    }
                });
            };

            function onYoutubePlayerReady(event) {
                console.log('YouTube Player Ready');
                
                // Resume dari progress sebelumnya HANYA jika belum completed
                // Jika completed, tampilkan preview/thumbnail awal
                const isModuleCompleted = {{ $isModuleCompleted ? 'true' : 'false' }};
                const resumeProgress = {{ $resumeProgress ?? 0 }};
                
                if (!isModuleCompleted && resumeProgress > 0) {
                    const duration = youtubePlayer.getDuration();
                    const resumeTime = (resumeProgress / 100) * duration;
                    youtubePlayer.seekTo(resumeTime, true);
                    
                    // Update lastSeekTime untuk avoid revert detection
                    lastSeekTime = resumeTime;
                }
            }

            function onYoutubePlayerStateChange(event) {
                if (event.data === YT.PlayerState.PLAYING) {
                    // Track progress every 5 seconds for YouTube
                    youtubeProgressInterval = setInterval(() => {
                        if (youtubePlayer.getPlayerState() === YT.PlayerState.PLAYING) {
                            trackYoutubeProgress();
                        }
                    }, 5000);
                } else if (event.data === YT.PlayerState.PAUSED || event.data === YT.PlayerState.ENDED) {
                    if (youtubeProgressInterval) clearInterval(youtubeProgressInterval);
                }

                // Auto-complete at 90%
                if (event.data === YT.PlayerState.ENDED) {
                    completeYoutubeModule();
                }
            }

            function trackYoutubeProgress() {
                const currentTime = youtubePlayer.getCurrentTime();
                const duration = youtubePlayer.getDuration();
                const progressPercentage = (currentTime / duration) * 100;

                // Only send every 30 seconds of progress change
                if (Math.abs(progressPercentage - lastProgress) >= 10) {
                    lastProgress = progressPercentage;

                    fetch('{{ route("ecourse.module.progress", $module) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            progress_percentage: progressPercentage
                        })
                    }).catch(error => {
                        console.error('Error updating YouTube progress:', error);
                    });
                }

                // Auto-complete at 90%
                if (progressPercentage >= 90 && !youtubeModuleCompleted) {
                    completeYoutubeModule();
                }
            }

            function completeYoutubeModule() {
                if (youtubeModuleCompleted) return;
                youtubeModuleCompleted = true;

                fetch('{{ route("ecourse.module.complete", $module) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                }).then(response => response.json())
                  .then(data => {
                    if (data.success) {
                        console.log(data.message);
                        showNotification('Modul telah diselesaikan.', 'success');
                        
                        // Update sidebar module status
                        updateModuleSidebarStatus();
                    }
                }).catch(error => {
                    console.error('Error completing YouTube module:', error);
                    youtubeModuleCompleted = false;
                });
            }

            // Override seeking untuk YouTube - Disable forward seek, allow backward
            const youtubeObserverInterval = setInterval(() => {
                if (!youtubePlayer || !youtubePlayer.getCurrentTime) {
                    return;
                }

                const currentTime = youtubePlayer.getCurrentTime();
                
                // Jika user mencoba forward seek lebih dari 5 detik, kembalikan ke posisi sebelumnya
                if (currentTime > lastSeekTime + 5) {
                    // User mencoba forward seek, kembalikan
                    youtubePlayer.seekTo(lastSeekTime, true);
                    showNotification('Anda tidak bisa melompat ke depan!', 'warning');
                    return;
                }

                // Update lastSeekTime untuk backward seek yang diizinkan
                if (currentTime < lastSeekTime) {
                    // Backward seek allowed
                    lastSeekTime = currentTime;
                } else if (currentTime === lastSeekTime) {
                    // No change
                } else {
                    // Update for next check
                    lastSeekTime = currentTime;
                }
            }, 500);

            // Limit playback speed to 1.25x for YouTube
            const youtubeSpeedInterval = setInterval(() => {
                if (!youtubePlayer || !youtubePlayer.getPlaybackRate) {
                    return;
                }

                const currentSpeed = youtubePlayer.getPlaybackRate();
                if (currentSpeed > 1.25) {
                    youtubePlayer.setPlaybackRate(1.25);
                    showNotification('Kecepatan maksimal 1.25x untuk video YouTube', 'warning');
                }
            }, 1000);

            // Show notification helper
            function showNotification(message, type = 'info') {
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white font-semibold z-50 ${
                    type === 'success' ? 'bg-green-500' : type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
                }`;
                notification.textContent = message;
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }

        // ===== LOCAL VIDEO PLAYER =====
        @else
            // Video Player Controls
            const video = document.getElementById('course-video');
            const playPauseBtn = document.getElementById('play-pause-btn');
            const progressBar = document.getElementById('progress-bar');
            const timeDisplay = document.getElementById('time-display');
            const fullscreenBtn = document.getElementById('fullscreen-btn');
            const speedBtn = document.getElementById('speed-btn');

            // Play/Pause functionality
            playPauseBtn.addEventListener('click', () => {
                if (video.paused) {
                    video.play();
                    playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
                } else {
                    video.pause();
                    playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
                }
            });

            // Update progress bar and time display
            video.addEventListener('timeupdate', () => {
                const progress = (video.currentTime / video.duration) * 100;
                progressBar.value = progress;
                
                const currentMinutes = Math.floor(video.currentTime / 60);
                const currentSeconds = Math.floor(video.currentTime % 60).toString().padStart(2, '0');
                const durationMinutes = Math.floor(video.duration / 60);
                const durationSeconds = Math.floor(video.duration % 60).toString().padStart(2, '0');
                
                timeDisplay.textContent = `${currentMinutes}:${currentSeconds} / ${durationMinutes}:${durationSeconds}`;

                // Send progress update every 30 seconds
                const currentTime = Math.floor(video.currentTime);
                if (currentTime % 30 === 0 && currentTime > 0 && !video.seeking) {
                    updateProgress(progress);
                }

                // Auto-complete module when 90% watched
                if (progress >= 90 && !video.seeking) {
                    completeModule();
                }
            });

            // Resume playback from progress
            video.addEventListener('loadedmetadata', () => {
                const isModuleCompleted = {{ $isModuleCompleted ? 'true' : 'false' }};
                const resumeSeconds = {{ $resumeSeconds ?? 0 }};
                
                // Hanya seek jika belum completed dan ada progress
                if (!isModuleCompleted && resumeSeconds > 0 && video.duration > resumeSeconds) {
                    video.currentTime = resumeSeconds;
                }
                // Jika completed, biarkan video show dari awal (untuk preview thumbnail)
            });

            // Function to update progress on server
            function updateProgress(progressPercentage) {
                fetch('{{ route("ecourse.module.progress", $module) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        progress_percentage: progressPercentage
                    })
                }).catch(error => {
                    console.error('Error updating progress:', error);
                });
            }

            // Function to complete module
            let moduleCompleted = false;
            function completeModule() {
                if (moduleCompleted) return; // Prevent duplicate calls
                moduleCompleted = true;

                fetch('{{ route("ecourse.module.complete", $module) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                }).then(response => response.json())
                  .then(data => {
                    if (data.success) {
                        console.log(data.message);
                        // Show success message
                        showNotification('Modul telah diselesaikan', 'success');
                        
                        // Update sidebar module status
                        updateModuleSidebarStatus();
                    }
                }).catch(error => {
                    console.error('Error completing module:', error);
                    moduleCompleted = false; // Reset on error
                });
            }

            // Seek functionality
            progressBar.addEventListener('input', (e) => {
                const seekTime = (e.target.value / 100) * video.duration;
                video.currentTime = seekTime;
            });

            // Fullscreen functionality
            fullscreenBtn.addEventListener('click', () => {
                if (!document.fullscreenElement) {
                    video.requestFullscreen();
                } else {
                    document.exitFullscreen();
                }
            });

            // Playback speed
            const speeds = [1, 1.25, 1.5, 2];
            let currentSpeedIndex = 0;
            
            speedBtn.addEventListener('click', () => {
                currentSpeedIndex = (currentSpeedIndex + 1) % speeds.length;
                const newSpeed = speeds[currentSpeedIndex];
                video.playbackRate = newSpeed;
                speedBtn.textContent = newSpeed + 'x';
            });

            // Update video status when completed
            video.addEventListener('ended', () => {
                // Ensure module is marked as completed
                completeModule();
            });
        @endif {{-- Akhir dari if video YouTube --}}

        // Tab functionality (tetap aktif untuk kedua jenis video)
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.tab-btn').forEach(b => {
                    b.classList.remove('active', 'text-primary');
                    b.classList.add('text-gray-500');
                });
                
                // Add active class to clicked tab
                this.classList.add('active', 'text-primary');
                this.classList.remove('text-gray-500');
                
                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                });
                
                // Show selected tab content
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId + '-tab').classList.remove('hidden');
            });
        });
    </script>

    <style>
        .tab-btn {
            position: relative;
        }
        
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: var(--primary-color);
        }

        /* Custom scrollbar for module list */
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Video progress bar styling */
        #progress-bar::-webkit-slider-thumb {
            appearance: none;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: white;
            cursor: pointer;
        }

        #progress-bar::-moz-range-thumb {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: white;
            cursor: pointer;
            border: none;
        }
    </style>
@endsection
