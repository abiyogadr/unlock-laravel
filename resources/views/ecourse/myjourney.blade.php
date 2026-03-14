@extends('ecourse.layouts.app')

@section('title', 'My Journey - Unlock E-Course')
@section('page_title', 'Perjalanan Belajar Saya')

@section('breadcrumb')
    <li><i class="fas fa-chevron-right text-[10px] mx-1"></i></li>
    <li class="text-gray-800 font-medium">My Journey</li>
@endsection

@section('content')
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">My Journey</h1>
        <p class="text-gray-600">Unlock semua e-course yang telah kamu jelajahi</p>
    </div>

    <!-- Search and Filter Form -->
    <form id="filter-form" method="GET" action="{{ route('ecourse.my-journey') }}"
        class="bg-white rounded-2xl shadow-xl border border-gray-100 p-5 md:p-6
                grid md:grid-cols-5 gap-4 items-end mb-8">

        <x-input-field 
            label="Cari E-Course"
            name="q" 
            icon="fas fa-search" 
            class="md:col-span-2"
            placeholder="Cari e-course..."
            :value="request('q')"
        />

        <x-custom-select name="category" label="Kategori" :value="request('category')" placeholder="Semua Kategori" onchange="this.form.submit()">
            <x-custom-select-item val="" label="Semua Kategori">Semua Kategori</x-custom-select-item>
            @foreach($filterCategories as $cat)
                <x-custom-select-item :val="$cat->slug" :label="$cat->name">{{ $cat->name }}</x-custom-select-item>
            @endforeach
        </x-custom-select>

        <x-custom-select name="level" label="Tingkat Kesulitan" :value="request('level')" placeholder="Semua Level" onchange="this.form.submit()">
            <x-custom-select-item val="" label="Semua Level">Semua Level</x-custom-select-item>
            <x-custom-select-item val="beginner" label="Beginner">Beginner</x-custom-select-item>
            <x-custom-select-item val="intermediate" label="Intermediate">Intermediate</x-custom-select-item>
            <x-custom-select-item val="advanced" label="Advanced">Advanced</x-custom-select-item>
        </x-custom-select>

        <x-custom-select name="status" label="Status" :value="request('status')" placeholder="Semua Status" onchange="this.form.submit()">
            <x-custom-select-item val="" label="Semua Status">Semua Status</x-custom-select-item>
            <x-custom-select-item val="progress" label="Sedang Berjalan">Sedang Berjalan</x-custom-select-item>
            <x-custom-select-item val="completed" label="Selesai">Selesai</x-custom-select-item>
        </x-custom-select>

        <div id="loading" class="hidden md:col-span-5 text-center text-gray-500 text-sm mt-2">
            <i class="fas fa-spinner fa-spin mr-2"></i> Memuat...
        </div>
    </form>

    {{-- Logika Tampilan: Hasil Pencarian vs Per Kategori --}}
    @if(request()->anyFilled(['q', 'category', 'level', 'status']))
        
        {{-- MODE HASIL PENCARIAN (GRID) --}}
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-800">
                Hasil Pencarian <span class="text-primary">({{ $courses->total() }})</span>
            </h2>
            <a href="{{ route('ecourse.my-journey') }}" class="text-sm text-red-500 hover:text-red-700 font-medium">
                <i class="fas fa-times-circle mr-1"></i> Bersihkan Filter
            </a>
        </div>

        @if($courses->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-10">
                @foreach($courses as $userCourse)
                    <x-ecourse-card :course="$userCourse->course" :progress="$userCourse->progress" class="w-80 flex-shrink-0"/>
                @endforeach
            </div>
            
            <div class="mt-8">
                {{ $courses->links() }}
            </div>
        @else
            <div class="text-center py-24 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-search text-3xl text-gray-300"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Ups! E-Course tidak ditemukan</h3>
                <p class="text-gray-500">Kami tidak menemukan hasil yang cocok dengan kriteria filter Anda.</p>
            </div>
        @endif

    @else

        {{-- MODE DEFAULT (HORIZONTAL SLIDER PER KATEGORI) --}}
        @if($categories->count() > 0)
            @foreach($categories as $category)
                @if($category->userCourses->count() > 0)
                    <section class="mb-12 last:mb-0">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">{{ $category->name }}</h2>
                            <span class="text-sm text-gray-500">{{ $category->userCourses->count() }} kursus</span>
                        </div>

                        <div class="relative">
                            <div class="flex overflow-x-auto gap-6 pb-6 scrollbar-hide -mx-2 px-2">
                                @foreach($category->userCourses as $userCourse)
                                    <x-ecourse-card :course="$userCourse->course" :progress="$userCourse->progress" class="w-80 flex-shrink-0" />
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif
            @endforeach
        @else
            {{-- Empty State --}}
            <div class="text-center py-24 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-inbox text-3xl text-gray-300"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Belum ada kursus</h3>
                <p class="text-gray-500 mb-6">Kamu belum mengambil kursus apapun. Yuk, mulai dari katalog kami!</p>
                <a href="{{ route('ecourse.catalog') }}" class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-xl font-bold text-sm hover:bg-purple-800 transition">
                    <i class="fas fa-plus mr-2"></i> Jelajahi Katalog
                </a>
            </div>
        @endif

    @endif

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
        const searchInput = filterForm.querySelector('input[name="q"]');
        const loadingIndicator = document.getElementById('loading');
        
        if (searchInput) {
            const autoSubmit = debounce(() => {
                if (loadingIndicator) loadingIndicator.classList.remove('hidden');
                filterForm.submit();
            }, 700); 
            searchInput.addEventListener('input', autoSubmit);
        }

        const filterSelects = filterForm.querySelectorAll('select, input[type="hidden"]');
        filterSelects.forEach(el => {
            el.addEventListener('change', () => {
                if (loadingIndicator) loadingIndicator.classList.remove('hidden');
            });
        });

        window.addEventListener('pageshow', () => {
            if (loadingIndicator) loadingIndicator.classList.add('hidden');
        });
    </script>
    @endpush

    <style>
        .tab-btn.active {
            background-color: var(--primary-color);
            color: white;
        }
        .tab-btn:not(.active) {
            color: #6b7280;
        }
        .tab-btn:not(.active):hover {
            background-color: #f3f4f6;
        }
    </style>
@endsection
