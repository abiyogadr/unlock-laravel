@extends('ecourse.layouts.app')

@section('title', 'Catalog E-Course - Unlock Indonesia')
@section('page_title', 'Catalog E-Course')

@section('breadcrumb')
    <li><i class="fas fa-chevron-right text-[10px] mx-1"></i></li>
    <li class="text-gray-800 font-medium">Catalog</li>
@endsection


@section('content')
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Catalog E-Course</h1>
        <p class="text-gray-600">Pilih e-course yang sesuai dengan kebutuhan profesionalmu</p>
    </div>

    <!-- Search and Filter Form -->
    <form id="filter-form" method="GET" action="{{ route('ecourse.catalog') }}"
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

        <x-custom-select name="price_type" label="Harga" :value="request('price_type')" placeholder="Semua Harga" onchange="this.form.submit()">
            <x-custom-select-item val="" label="Semua Harga">Semua Harga</x-custom-select-item>
            <x-custom-select-item val="free" label="Gratis">Gratis</x-custom-select-item>
            <x-custom-select-item val="paid" label="Berbayar">Berbayar</x-custom-select-item>
        </x-custom-select>

        <div id="loading" class="hidden md:col-span-5 text-center text-gray-500 text-sm mt-2">
            <i class="fas fa-spinner fa-spin mr-2"></i> Memuat...
        </div>
    </form>

    {{-- Logika Tampilan: Hasil Pencarian vs Katalog Per Kategori --}}
    @if(request()->anyFilled(['q', 'category', 'level', 'price_type']))
        
        {{-- MODE HASIL PENCARIAN (GRID) --}}
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-800">
                Hasil Pencarian <span class="text-primary">({{ $courses->total() }})</span>
            </h2>
            <a href="{{ route('ecourse.catalog') }}" class="text-sm text-red-500 hover:text-red-700 font-medium">
                <i class="fas fa-times-circle mr-1"></i> Bersihkan Filter
            </a>
        </div>

        @if($courses->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-10">
                @foreach($courses as $course)
                    <x-ecourse-card :course="$course" class="w-80 flex-shrink-0" />
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
        @foreach($categories as $category)
            @if($category->courses->count() > 0)
                <section class="mb-12 last:mb-0">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">{{ $category->name }}</h2>
                        <a href="{{ route('ecourse.catalog.category', $category->slug) }}" 
                            class="text-primary hover:text-purple-800 font-semibold text-sm flex items-center transition">
                            Lihat Semua <i class="fas fa-arrow-right ml-2 text-xs"></i>
                        </a>
                    </div>

                    <div class="relative">
                        <div class="flex overflow-x-auto gap-6 pb-6 scrollbar-hide -mx-2 px-2">
                            @foreach($category->courses as $course)
                                <x-ecourse-card :course="$course" class="w-80 flex-shrink-0" />
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        @endforeach

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

@endsection
