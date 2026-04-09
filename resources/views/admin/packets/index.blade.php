@extends('layouts.admin')

@section('title', 'Unlock - Admin Packets')
@section('page-title', 'Manage Packets')

@section('page-actions')
    <a href="{{ route('admin.packets.create') }}" 
       class="bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-secondary transition duration-300 shadow-sm flex items-center gap-2">
        <i class="fas fa-plus"></i>
        <span>Tambah Paket</span>
    </a>
@endsection

@section('admin-content')
<div x-data="deleteModal">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        
        {{-- HEADER & FILTER SECTION --}}
        <div class="p-4 md:p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Daftar Paket Webinar</h3>
            
            <form action="{{ route('admin.packets.index') }}" method="GET" x-data data-admin-filter-form>
                <div class="grid grid-cols-1 sm:grid-cols-6 gap-3 md:gap-4">
                    
                    {{-- 1. Search --}}
                    <x-input-field
                        name="search" 
                        label="Pencarian" 
                        icon="fas fa-search" 
                        :value="request('search')" 
                        placeholder="Ketik nama paket..."
                        class="col-span-3"
                        :showError="false"
                    />

                    {{-- 2. Status --}}
                    <x-custom-select 
                        name="status" 
                        label="Status" 
                        :value="request('status', 'all')" 
                        placeholder="Semua Status"
                        class="col-span-2"
                    >
                        <x-custom-select-item val="all" label="Semua Status">Semua Status</x-custom-select-item>
                        <x-custom-select-item val="active" label="Active">
                            <span class="text-green-600">●</span> Active
                        </x-custom-select-item>
                        <x-custom-select-item val="inactive" label="Inactive">
                            <span class="text-gray-600">●</span> Inactive
                        </x-custom-select-item>
                    </x-custom-select>

                    {{-- 3. Reset --}}
                    <div class="col-span-1 flex items-end">
                        <button type="button"
                               data-admin-filter-reset
                               class="w-full py-2 px-3 text-sm font-medium transition text-center flex items-center justify-center gap-2 rounded-lg border cursor-pointer {{ request()->filled('search') || request('status', 'all') !== 'all' ? 'bg-red-50 text-red-600 border-red-100 hover:bg-red-100' : 'bg-gray-50 text-gray-300 border-gray-100 cursor-not-allowed' }}">
                                @if(request()->filled('search') || request('status', 'all') !== 'all')
                                    <i class="fas fa-undo"></i> Reset
                                @else
                                    <i class="fas fa-filter"></i> Auto Filter
                                @endif
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div data-admin-filter-results>

        {{-- DESKTOP TABLE --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Paket</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requirements</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($packets as $packet)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $packet->packet_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            @if($packet->price == 0)
                                <span class="text-green-600 font-bold">Gratis</span>
                            @else
                                Rp {{ number_format($packet->price, 0, ',', '.') }}
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ collect($packet->requirements)->filter()->count() }} Syarat
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $packet->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $packet->is_active ? 'Aktif' : 'Non-Aktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <a href="{{ route('admin.packets.edit', $packet) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                            <button @click="openDelete({{ $packet->id }}, '{{ $packet->packet_name }}')" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition cursor-pointer">
                                <i class="fas fa-trash mr-1"></i> Hapus
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400">Belum ada paket yang ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- MOBILE CARD VIEW --}}
        <div class="md:hidden divide-y divide-gray-100">
            @forelse($packets as $packet)
            <div class="p-4 bg-white space-y-3">
                <div class="flex justify-between items-start">
                    <h4 class="font-bold text-gray-900 text-base leading-tight">{{ $packet->packet_name }}</h4>
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $packet->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $packet->is_active ? 'Aktif' : 'Non-Aktif' }}
                    </span>
                </div>
                
                <div class="flex justify-between items-center">
                    <div class="text-sm font-bold text-primary">
                        @if($packet->price == 0) Gratis @else Rp {{ number_format($packet->price, 0, ',', '.') }} @endif
                    </div>
                    <div class="text-xs text-gray-500">
                        <i class="fas fa-list-check mr-1"></i> {{ collect($packet->requirements)->filter()->count() }} Syarat
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <a href="{{ route('admin.packets.edit', $packet) }}" class="flex-1 bg-blue-50 text-blue-700 text-center py-2 rounded-lg text-xs font-semibold">Edit</a>
                    <button @click="openDelete({{ $packet->id }}, '{{ $packet->packet_name }}')" class="flex-1 bg-red-50 text-red-700 text-center py-2 rounded-lg text-xs font-semibold">Hapus</button>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-gray-400 text-sm">Data tidak ditemukan.</div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        @if($packets->hasPages())
        <div class="p-4 bg-gray-50 border-t border-gray-100" data-admin-pagination>
            {{ $packets->appends(request()->query())->links() }}
        </div>
        @endif

        </div>
    </div>

    {{-- MODAL DELETE --}}
    <div x-show="deleteId !== null" 
         x-transition.opacity
         class="fixed inset-0 bg-black/50 bg-opacity-50 z-50 flex items-center justify-center p-4"
         style="display: none;">
        <div @click.away="closeDelete()" class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-trash-alt text-2xl text-red-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Hapus Paket?</h3>
                <p class="font-semibold text-gray-900 bg-gray-50 px-3 py-1 rounded text-sm" x-text="deleteName"></p>
            </div>
            <div class="flex gap-3">
                <button @click="closeDelete()" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium cursor-pointer">Batal</button>
                <form x-ref="deleteForm" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg font-medium cursor-pointer">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('deleteModal', () => ({
        deleteId: null,
        deleteName: '',
        openDelete(id, name) {
            this.deleteId = id;
            this.deleteName = name;
            this.$refs.deleteForm.action = `/upanel/packets/${id}`;
        },
        closeDelete() {
            this.deleteId = null;
            this.deleteName = '';
        }
    }));
});
</script>

<script>
(() => {
    const form = document.querySelector('[data-admin-filter-form]');
    const results = document.querySelector('[data-admin-filter-results]');
    const resetButton = document.querySelector('[data-admin-filter-reset]');

    if (!form || !results) {
        return;
    }

    let searchTimer = null;

    const buildUrl = () => {
        const params = new URLSearchParams();
        const formData = new FormData(form);

        for (const [key, value] of formData.entries()) {
            const normalized = String(value ?? '').trim();
            if (normalized !== '' && normalized !== 'all') {
                params.set(key, value);
            }
        }

        return `${window.location.pathname}${params.toString() ? `?${params.toString()}` : ''}`;
    };

    const hasActiveFilters = () => {
        const formData = new FormData(form);

        for (const [key, value] of formData.entries()) {
            const normalized = String(value ?? '').trim();

            if (normalized !== '' && normalized !== 'all') {
                return true;
            }
        }

        return false;
    };

    const syncActionButton = () => {
        if (!resetButton) {
            return;
        }

        const active = hasActiveFilters();

        resetButton.disabled = !active;
        resetButton.className = `w-full py-2 px-3 text-sm font-medium transition text-center flex items-center justify-center gap-2 rounded-lg border ${active ? 'bg-red-50 text-red-600 border-red-100 hover:bg-red-100 cursor-pointer' : 'bg-gray-50 text-gray-300 border-gray-100 cursor-not-allowed'}`;
        resetButton.innerHTML = active ? '<i class="fas fa-undo"></i> Reset' : '<i class="fas fa-filter"></i> Auto Filter';
    };

    const replaceResults = async (url) => {
        const response = await fetch(url, {
            headers: {
                'Accept': 'text/html',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error('Gagal memuat data.');
        }

        const html = await response.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const nextResults = doc.querySelector('[data-admin-filter-results]');

        if (nextResults) {
            results.innerHTML = nextResults.innerHTML;
        }

        window.history.replaceState({}, '', url);
        syncActionButton();
    };

    const resetFilters = () => {
        const searchInput = form.querySelector('[name="search"]');
        if (searchInput) {
            searchInput.value = '';
        }

        window.dispatchEvent(new CustomEvent('admin-filters-reset'));
        syncActionButton();
        replaceResults(window.location.pathname).catch((error) => console.error(error));
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        replaceResults(buildUrl()).catch((error) => console.error(error));
    });

    form.addEventListener('input', (event) => {
        if (!event.target || event.target.name !== 'search') {
            return;
        }

        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => {
            replaceResults(buildUrl()).catch((error) => console.error(error));
        }, 350);
    });

    form.addEventListener('change', (event) => {
        if (event.target && event.target.name === 'search') {
            return;
        }

        syncActionButton();
        replaceResults(buildUrl()).catch((error) => console.error(error));
    });

    if (resetButton) {
        resetButton.addEventListener('click', resetFilters);
    }

    syncActionButton();

    results.addEventListener('click', (event) => {
        const paginationLink = event.target.closest('[data-admin-pagination] a');

        if (!paginationLink) {
            return;
        }

        event.preventDefault();
        replaceResults(paginationLink.href).catch((error) => console.error(error));
    });
})();
</script>
@endsection
