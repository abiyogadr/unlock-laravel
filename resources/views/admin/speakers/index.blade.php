@extends('layouts.admin')

@section('title', 'Unlock - Speakers')
@section('page-title', 'Manage Speakers')

@section('page-actions')
    <a href="{{ route('admin.speakers.create') }}" 
       class="bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-secondary transition duration-300 shadow-sm flex items-center gap-2">
        <i class="fas fa-plus"></i>
        <span>Tambah Speaker</span>
    </a>
@endsection

@section('admin-content')
<div x-data="deleteModal">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Filter --}}
        <div class="p-4 md:p-6 border-b border-gray-100">
            <form action="{{ route('admin.speakers.index') }}" method="GET" x-data data-admin-filter-form>
                <div class="flex flex-col md:flex-row gap-3">
                    <x-input-field 
                        name="search" 
                        icon="fas fa-search" 
                        :value="request('search')" 
                        placeholder="Cari nama, email, atau perusahaan..."
                        class="flex-1"
                        :showError="false"
                    />
                    <button type="button" data-admin-filter-reset class="px-4 py-2 rounded-lg text-sm font-medium text-center border transition {{ request()->filled('search') ? 'bg-red-50 text-red-600 border-red-100 hover:bg-red-100 cursor-pointer' : 'bg-gray-50 text-gray-300 border-gray-100 cursor-not-allowed' }}">
                        {{ request()->filled('search') ? 'Reset' : 'Auto Filter' }}
                    </button>
                </div>
            </form>
        </div>

        <div data-admin-filter-results>

        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-xs text-gray-500 uppercase font-semibold">
                        <th class="px-6 py-4 text-left">Kode</th>
                        <th class="px-6 py-4 text-left">Nama Lengkap</th>
                        <th class="px-6 py-4 text-left">Jabatan & Instansi</th>
                        <th class="px-6 py-4 text-left">Kontak</th>
                        <th class="px-6 py-4 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($speakers as $speaker)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm font-mono text-gray-600">{{ $speaker->speaker_code }}</td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900">{{ $speaker->prefix_title }} {{ $speaker->speaker_name }}{{ $speaker->suffix_title ? ', ' . $speaker->suffix_title : '' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-700">{{ $speaker->position }}</div>
                            <div class="text-xs text-gray-500">{{ $speaker->company }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-500 space-y-1">
                            <div><i class="fas fa-envelope mr-1"></i> {{ $speaker->email }}</div>
                            <div><i class="fas fa-phone mr-1"></i> {{ $speaker->phone }}</div>
                        </td>
                        <!-- <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.speakers.edit', $speaker) }}" class="text-blue-600 hover:text-blue-800"><i class="fas fa-edit"></i></a>
                            <button @click="openDelete({{ $speaker->id }}, '{{ $speaker->speaker_name }}')" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button>
                        </td> -->
                        <td class="px-6 py-4 space-x-2">
                            <a href="{{ route('admin.speakers.edit', $speaker) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                            <button type="button" @click="openDelete({{ $speaker->id }}, '{{ $speaker->speaker_name }}')" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition cursor-pointer">
                                <i class="fas fa-trash mr-1"></i>Hapus
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400">Data speaker tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile View --}}
        <div class="md:hidden divide-y divide-gray-100">
            @foreach($speakers as $speaker)
            <div class="p-4 space-y-3 bg-white">
                <div class="flex justify-between justify">
                    <span class="flex items-center text-[10px] font-mono bg-gray-100 px-2 py-0.5 rounded text-gray-500">{{ $speaker->speaker_code }}</span>
                    <div class="flex gap-3 text-sm">
                        <a href="{{ route('admin.speakers.edit', $speaker) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
                        <button type="button" @click="openDelete({{ $speaker->id }}, '{{ $speaker->speaker_name }}')" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">
                            <i class="fas fa-trash mr-1"></i>Hapus
                        </button>
                    </div>
                </div>
                <div>
                    <div class="font-bold text-gray-900">{{ $speaker->prefix_title }} {{ $speaker->speaker_name }}{{ $speaker->suffix_title ? ', ' . $speaker->suffix_title : '' }}</div>
                    <div class="text-xs text-gray-600">{{ $speaker->position }} at {{ $speaker->company }}</div>
                </div>
                <div class="grid grid-cols-2 gap-2 text-[11px] text-gray-500 pt-2 border-t border-gray-50">
                    <div class="truncate"><i class="fas fa-envelope mr-1"></i> {{ $speaker->email }}</div>
                    <div class="text-right"><i class="fas fa-phone mr-1"></i> {{ $speaker->phone }}</div>
                </div>
            </div>
            @endforeach
        </div>
        
        @if($speakers->hasPages())
            <div class="p-4 bg-gray-50 border-t border-gray-100" data-admin-pagination>{{ $speakers->withQueryString()->links() }}</div>
        @endif

        </div>
    </div>

    {{-- Delete Modal --}}
    <div x-show="deleteId !== null" x-transition.opacity class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="closeDelete()" class="bg-white rounded-xl p-6 max-w-sm w-full">
            <div class="text-center">
                <i class="fas fa-exclamation-circle text-red-500 text-4xl mb-3"></i>
                <h3 class="font-bold text-gray-900">Hapus Speaker?</h3>
                <p class="text-xs text-gray-500 mt-1" x-text="deleteName"></p>
            </div>
            <div class="flex gap-3 mt-6">
                <button @click="closeDelete()" class="flex-1 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm cursor-pointer">Batal</button>
                <form x-ref="deleteForm" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-2 bg-red-600 text-white rounded-lg text-sm cursor-pointer">Hapus</button>
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
            this.$refs.deleteForm.action = `/upanel/speakers/${id}`;
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
            if (normalized !== '') {
                params.set(key, value);
            }
        }

        return `${window.location.pathname}${params.toString() ? `?${params.toString()}` : ''}`;
    };

    const hasActiveFilters = () => {
        const formData = new FormData(form);

        for (const [key, value] of formData.entries()) {
            const normalized = String(value ?? '').trim();

            if (normalized !== '') {
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
        resetButton.className = `px-4 py-2 rounded-lg text-sm font-medium text-center border transition ${active ? 'bg-red-50 text-red-600 border-red-100 hover:bg-red-100 cursor-pointer' : 'bg-gray-50 text-gray-300 border-gray-100 cursor-not-allowed'}`;
        resetButton.textContent = active ? 'Reset' : 'Auto Filter';
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

    syncActionButton();

    if (resetButton) {
        resetButton.addEventListener('click', resetFilters);
    }

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
