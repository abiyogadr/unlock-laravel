@extends('ecourse.layouts.app')

@section('title', $category->name . ' - Unlock E-Course')

@section('breadcrumb')
    <li><i class="fas fa-chevron-right text-[10px] mx-1"></i></li>
    <li><a href="{{ route('ecourse.catalog') }}" class="hover:text-primary transition">Catalog</a></li>
    <li><i class="fas fa-chevron-right text-[10px] mx-1"></i></li>
    <li class="text-gray-800 font-medium">{{ $category->name }}</li>
@endsection

@section('content')
    <!-- Filter & Search (Action ke URL Kategori Sekarang) -->
    <form id="filter-form" method="GET" action="{{ url()->current() }}"
        class="bg-white rounded-2xl shadow-xl border border-gray-100 p-5 md:p-6
                grid md:grid-cols-4 gap-4 items-end mb-8">

        {{-- Search --}}
        <x-input-field 
            label="Cari di {{ $category->name }}"
            name="q" 
            icon="fas fa-search" 
            class="md:col-span-2"
            placeholder="Cari judul..."
            :value="request('q')"
        />

        {{-- Level --}}
        <x-custom-select 
            name="level" 
            label="Tingkat Kesulitan" 
            :value="request('level')" 
            placeholder="Semua Level" 
            onchange="this.form.submit()"
        >
            <x-custom-select-item val="" label="Semua Level">Semua Level</x-custom-select-item>
            <x-custom-select-item val="beginner" label="Beginner">Beginner</x-custom-select-item>
            <x-custom-select-item val="intermediate" label="Intermediate">Intermediate</x-custom-select-item>
            <x-custom-select-item val="advanced" label="Advanced">Advanced</x-custom-select-item>
        </x-custom-select>

        {{-- Harga --}}
        <x-custom-select 
            name="price_type" 
            label="Harga" 
            :value="request('price_type')" 
            placeholder="Semua Harga" 
            onchange="this.form.submit()"
        >
            <x-custom-select-item val="" label="Semua Harga">Semua Harga</x-custom-select-item>
            <x-custom-select-item val="free" label="Gratis">Gratis</x-custom-select-item>
            <x-custom-select-item val="paid" label="Berbayar">Berbayar</x-custom-select-item>
        </x-custom-select>

        {{-- Loading indicator --}}
        <div id="loading" class="hidden md:col-span-4 text-center text-gray-500 text-sm mt-2">
            <i class="fas fa-spinner fa-spin mr-2"></i> Memuat...
        </div>
    </form>

    <!-- Content Grid -->
    <div class="mb-10">
        @if($courses->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($courses as $course)
                    <x-ecourse-card :course="$course" />
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-10">
                {{ $courses->links() }}
            </div>
        @else
            <div class="text-center py-20 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200">
                <h3 class="text-lg font-bold text-gray-800">Tidak ditemukan e-course</h3>
                <p class="text-gray-500 mt-2">Coba atur ulang filter pencarian Anda.</p>
            </div>
        @endif
    </div>

    <!-- Back Action -->
    <div class="text-center border-t border-gray-100 pt-8">
        <a href="{{ route('ecourse.catalog') }}" class="text-sm font-bold text-gray-500 hover:text-primary transition">
            <i class="fas fa-arrow-left mr-2"></i> KEMBALI KE SEMUA KATEGORI
        </a>
    </div>

@push('scripts')
<script>
    const filterForm = document.getElementById('filter-form');
    const searchInput = filterForm.querySelector('input[name="q"]');
    const loadingIndicator = document.getElementById('loading');
    
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

    if (searchInput) {
        const autoSubmit = debounce(() => {
            loadingIndicator.classList.remove('hidden');
            filterForm.submit();
        }, 700);
        searchInput.addEventListener('input', autoSubmit);
    }

    const filterSelects = filterForm.querySelectorAll('select, input[type="hidden"]');
    filterSelects.forEach(el => {
        el.addEventListener('change', () => {
            loadingIndicator.classList.remove('hidden');
        });
    });

    window.addEventListener('pageshow', () => {
        loadingIndicator.classList.add('hidden');
    });
</script>
@endpush
@endsection
