@extends('layouts.admin')

@section('title', 'Unlock - Short Links')
@section('page-title', 'Short Link Generator')

@section('page-actions')
    <a href="{{ route('admin.short-links.create') }}"
       class="bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-secondary transition duration-300 shadow-sm flex items-center gap-2">
        <i class="fas fa-plus"></i>
        <span>Tambah Short Link</span>
    </a>
@endsection

@section('admin-content')
<div x-data="deleteShortLinkModal">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Link Dibuat</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total_links'] }}</p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-link text-indigo-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Klik</p>
                    <p class="text-3xl font-bold text-emerald-600">{{ $stats['total_clicks'] }}</p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-mouse-pointer text-emerald-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Link Aktif</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $stats['active_links'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-bolt text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Link Expired</p>
                    <p class="text-3xl font-bold text-amber-600">{{ $stats['expired_links'] }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-amber-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 md:p-6 border-b border-gray-100">
            <form action="{{ route('admin.short-links.index') }}" method="GET" data-admin-filter-form>
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <x-input-field
                        name="search"
                        label="Pencarian"
                        icon="fas fa-search"
                        :value="request('search')"
                        placeholder="Cari short code atau URL tujuan"
                        class="md:col-span-5"
                        :showError="false"
                    />

                    <div class="md:col-span-3">
                        <x-custom-select name="status" :value="request('status', 'all')" placeholder="Semua Status" onchange="this.form.submit()">
                            <x-custom-select-item val="all" label="Semua Status">Semua Status</x-custom-select-item>
                            <x-custom-select-item val="active" label="Aktif">Aktif</x-custom-select-item>
                            <x-custom-select-item val="inactive" label="Nonaktif">Nonaktif</x-custom-select-item>
                            <x-custom-select-item val="expired" label="Expired">Expired</x-custom-select-item>
                        </x-custom-select>
                    </div>

                    <div class="md:col-span-4 flex items-end gap-2">
                        <button type="submit" class="px-4 py-2.5 bg-slate-900 text-white rounded-lg text-sm font-semibold hover:bg-slate-800 transition cursor-pointer">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                        <button type="button" data-admin-filter-reset class="px-4 py-2.5 rounded-lg text-sm font-semibold transition border {{ request()->filled('search') || request('status', 'all') !== 'all' ? 'bg-red-50 text-red-600 border-red-100 hover:bg-red-100 cursor-pointer' : 'bg-gray-50 text-gray-300 border-gray-100 cursor-not-allowed' }}">
                            {{ request()->filled('search') || request('status', 'all') !== 'all' ? 'Reset' : 'Auto Filter' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div data-admin-filter-results>
            <div class="hidden md:block overflow-x-auto w-full">
                <table class="w-full table-auto">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr class="text-xs text-gray-500 uppercase font-semibold">
                            <th class="px-6 py-4 text-left">Short Code</th>
                            <th class="px-6 py-4 text-left">URL Tujuan</th>
                            <th class="px-6 py-4 text-left">Klik</th>
                            <th class="px-6 py-4 text-left">Status</th>
                            <th class="px-6 py-4 text-left">Expired</th>
                            <th class="px-6 py-4 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($shortLinks as $shortLink)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-mono text-sm font-semibold text-gray-900">{{ $shortLink->short_code }}</div>
                                <div class="text-xs text-gray-500 max-w-48 truncate">{{ $shortLink->publicUrl() }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 max-w-48 truncate" title="{{ $shortLink->original_url }}">
                                {{ $shortLink->original_url }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ $shortLink->click_count }}</div>
                                <div class="text-xs text-gray-500">{{ $shortLink->clicks_count }} log</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $rowStatus = ! $shortLink->is_active ? ['bg-gray-100 text-gray-700', 'Nonaktif'] : ($shortLink->isExpired() ? ['bg-amber-100 text-amber-700', 'Expired'] : ['bg-green-100 text-green-700', 'Aktif']);
                                @endphp
                                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $rowStatus[0] }}">{{ $rowStatus[1] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $shortLink->expires_at ? $shortLink->expires_at->format('d M Y H:i') : 'Tidak ada' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <a href="{{ route('admin.short-links.edit', $shortLink) }}" class="inline-flex w-full sm:w-auto items-center justify-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition">
                                        <i class="fas fa-edit mr-1"></i>
                                        <span></span>
                                    </a>
                                    <a href="{{ $shortLink->publicUrl() }}" target="_blank" class="inline-flex w-full sm:w-auto items-center justify-center px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-lg hover:bg-emerald-200 transition">
                                        <i class="fas fa-arrow-up-right-from-square mr-1"></i>
                                        <span></span>
                                    </a>
                                    <button type="button" @click="openDelete({{ $shortLink->id }}, '{{ $shortLink->short_code }}')" class="inline-flex w-full sm:w-auto items-center justify-center px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition cursor-pointer">
                                        <i class="fas fa-trash mr-1"></i>
                                        <span></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-link text-4xl mb-4 opacity-30 block"></i>
                                <p>Belum ada short link.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="md:hidden divide-y divide-gray-100">
                @forelse($shortLinks as $shortLink)
                <div class="p-4 space-y-3 bg-white">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="font-mono text-sm font-semibold text-gray-900">{{ $shortLink->short_code }}</div>
                            <div class="text-xs text-gray-500 break-all">{{ $shortLink->publicUrl() }}</div>
                        </div>
                        @php
                            $mobileStatus = ! $shortLink->is_active ? ['bg-gray-100 text-gray-700', 'Nonaktif'] : ($shortLink->isExpired() ? ['bg-amber-100 text-amber-700', 'Expired'] : ['bg-green-100 text-green-700', 'Aktif']);
                        @endphp
                        <span class="px-3 py-1 text-[11px] font-semibold rounded-full {{ $mobileStatus[0] }}">{{ $mobileStatus[1] }}</span>
                    </div>

                    <div class="text-sm text-gray-700 line-clamp-2 break-all">{{ $shortLink->original_url }}</div>

                    <div class="grid grid-cols-3 gap-2 text-xs text-gray-500 pt-2 border-t border-gray-50">
                        <div><span class="block text-gray-400">Klik</span>{{ $shortLink->click_count }}</div>
                        <div><span class="block text-gray-400">Expired</span>{{ $shortLink->expires_at ? $shortLink->expires_at->format('d M Y') : 'Tidak ada' }}</div>
                        <div class="text-right space-x-2">
                            <a href="{{ route('admin.short-links.edit', $shortLink) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition">
                                <i class="fas fa-edit mr-1"></i>
                            </a>
                            <button type="button" @click="openDelete({{ $shortLink->id }}, '{{ $shortLink->short_code }}')" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition cursor-pointer">
                                <i class="fas fa-trash mr-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-link text-4xl mb-4 opacity-30 block"></i>
                    <p>Belum ada short link.</p>
                </div>
                @endforelse
            </div>

            @if($shortLinks->hasPages())
            <div class="px-4 sm:px-6 py-4 bg-gray-50 border-t border-gray-100" data-admin-pagination>
                {{ $shortLinks->links() }}
            </div>
            @endif
        </div>
    </div>

    <div x-show="isOpen" x-transition.opacity class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="closeDelete()" class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Konfirmasi Hapus</h3>
                <p class="text-gray-600 mb-2">Apakah Anda yakin ingin menghapus:</p>
                <p class="font-semibold text-gray-900 bg-gray-50 px-3 py-1 rounded text-sm break-words" x-text="shortCode"></p>
            </div>
            <div class="flex gap-3">
                <button type="button" @click="closeDelete()" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium cursor-pointer">Batal</button>
                <form :action="deleteUrl" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium cursor-pointer">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('deleteShortLinkModal', () => ({
        isOpen: false,
        deleteUrl: '',
        shortCode: '',
        openDelete(id, shortCode) {
            this.isOpen = true;
            this.shortCode = shortCode;
            this.deleteUrl = `/upanel/short-links/${id}`;
        },
        closeDelete() {
            this.isOpen = false;
            this.shortCode = '';
            this.deleteUrl = '';
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
        resetButton.className = `px-4 py-2.5 rounded-lg text-sm font-semibold transition border ${active ? 'bg-red-50 text-red-600 border-red-100 hover:bg-red-100 cursor-pointer' : 'bg-gray-50 text-gray-300 border-gray-100 cursor-not-allowed'}`;
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

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        replaceResults(buildUrl()).catch((error) => console.error(error));
    });

    if (resetButton) {
        resetButton.addEventListener('click', () => {
            const searchInput = form.querySelector('[name="search"]');
            const statusInput = form.querySelector('[name="status"]');

            if (searchInput) {
                searchInput.value = '';
            }

            if (statusInput) {
                statusInput.value = 'all';
            }

            syncActionButton();
            replaceResults(window.location.pathname).catch((error) => console.error(error));
        });
    }

    results.addEventListener('click', (event) => {
        const paginationLink = event.target.closest('[data-admin-pagination] a');

        if (!paginationLink) {
            return;
        }

        event.preventDefault();
        replaceResults(paginationLink.href).catch((error) => console.error(error));
    });

    syncActionButton();
})();
</script>
@endsection