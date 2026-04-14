@extends('layouts.admin')

@section('title', 'Unlock - Edit Short Link')
@section('page-title', 'Edit Short Link')

@section('page-actions')
    <a href="{{ route('admin.short-links.index') }}" class="text-gray-500 hover:text-gray-700 font-medium flex items-center transition duration-200">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
@endsection

@section('admin-content')
<div class="space-y-6">
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <p class="text-sm text-gray-500 mb-1">Total Klik</p>
            <p class="text-xl font-bold text-gray-900">{{ $shortLink->click_count }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <p class="text-sm text-gray-500 mb-1">Klik Log</p>
            <p class="text-xl font-bold text-blue-600">{{ $shortLink->clicks_count }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <p class="text-sm text-gray-500 mb-1">Status</p>
            <p class="text-xl font-bold {{ ! $shortLink->is_active ? 'text-gray-500' : ($shortLink->isExpired() ? 'text-amber-600' : 'text-emerald-600') }}">
                {{ ! $shortLink->is_active ? 'Nonaktif' : ($shortLink->isExpired() ? 'Expired' : 'Aktif') }}
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <p class="text-sm text-gray-500 mb-1">Expired At</p>
            <p class="text-md font-bold text-gray-900">{{ $shortLink->expires_at ? $shortLink->expires_at->format('d M Y H:i') : 'Tidak ada' }}</p>
        </div>
    </div>

    <form action="{{ route('admin.short-links.update', $shortLink) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @csrf
        @method('PUT')

        <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Detail Short Link</h3>
            </div>
            <a href="{{ $shortLink->publicUrl() }}" target="_blank" class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition">
                <i class="fas fa-arrow-up-right-from-square mr-2"></i>Buka
            </a>
        </div>

        <div class="p-6 sm:p-8">
            @include('admin.short-links._form', ['shortLink' => $shortLink])
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between gap-3">
            <button type="button" data-delete-short-link class="px-5 py-2.5 rounded-lg border border-red-200 text-red-700 font-medium hover:bg-red-50 transition duration-200 cursor-pointer">
                <i class="fas fa-trash mr-2"></i>Hapus
            </button>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.short-links.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-100 transition duration-200 cursor-pointer">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-white font-medium hover:bg-secondary shadow-lg shadow-primary/30 transition duration-200 transform hover:-translate-y-0.5 cursor-pointer">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 md:p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Recent Click Tracking</h3>
            <p class="text-sm text-gray-500 mt-1">Riwayat klik terakhir yang disimpan di tabel link_clicks.</p>
        </div>

        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-xs text-gray-500 uppercase font-semibold">
                        <th class="px-6 py-4 text-left">Timestamp</th>
                        <th class="px-6 py-4 text-left">IP Address</th>
                        <th class="px-6 py-4 text-left">User Agent</th>
                        <th class="px-6 py-4 text-left">Referer</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($shortLink->clicks as $click)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $click->clicked_at?->format('d M Y H:i:s') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-mono">{{ $click->ip_address ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-48 truncate" title="{{ $click->user_agent }}">{{ $click->user_agent ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-48 truncate" title="{{ $click->referer }}">{{ $click->referer ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-mouse-pointer text-4xl mb-4 opacity-30 block"></i>
                            <p>Belum ada klik yang tercatat.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y divide-gray-100">
            @forelse($shortLink->clicks as $click)
            <div class="p-4 bg-white space-y-2">
                <div class="flex items-center justify-between gap-3 text-sm">
                    <span class="font-semibold text-gray-900">{{ $click->clicked_at?->format('d M Y H:i') }}</span>
                    <span class="text-xs font-mono text-gray-500">{{ $click->ip_address ?? '-' }}</span>
                </div>
                <div class="text-xs text-gray-500 break-all">{{ $click->user_agent ?? '-' }}</div>
                <div class="text-xs text-gray-400 break-all">{{ $click->referer ?? '-' }}</div>
            </div>
            @empty
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-mouse-pointer text-4xl mb-4 opacity-30 block"></i>
                <p>Belum ada klik yang tercatat.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<form id="delete-short-link-form" action="{{ route('admin.short-links.destroy', $shortLink) }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
(() => {
    const shortCodeInput = document.querySelector('[name="short_code"]');
    const shortUrlText = document.querySelector('[data-short-url-text]');
    const qrImage = document.querySelector('[data-qr-image]');
    const qrPlaceholder = document.querySelector('[data-qr-placeholder]');
    const openButton = document.querySelector('[data-short-url-open]');
    const copyButton = document.querySelector('[data-copy-short-url]');
    const deleteButton = document.querySelector('[data-delete-short-link]');
    const deleteForm = document.getElementById('delete-short-link-form');
    const baseUrl = `${window.location.origin}/u/`;

    if (!shortCodeInput || !shortUrlText) {
        return;
    }

    const updatePreview = () => {
        const code = String(shortCodeInput.value || '').trim().toLowerCase();
        const fullUrl = code ? `${baseUrl}${code}` : '';
        const hasUrl = fullUrl !== '';

        shortUrlText.textContent = hasUrl ? fullUrl : 'Belum tersedia';

        if (openButton) {
            openButton.href = hasUrl ? fullUrl : '#';
            openButton.classList.toggle('pointer-events-none', !hasUrl);
            openButton.classList.toggle('opacity-40', !hasUrl);
        }

        if (copyButton) {
            copyButton.disabled = !hasUrl;
            copyButton.classList.toggle('cursor-not-allowed', !hasUrl);
            copyButton.classList.toggle('opacity-40', !hasUrl);
        }

        if (qrImage) {
            if (hasUrl) {
                qrImage.src = `https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=${encodeURIComponent(fullUrl)}`;
            }
        } else if (qrPlaceholder && hasUrl) {
            const image = document.createElement('img');
            image.dataset.qrImage = 'true';
            image.src = `https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=${encodeURIComponent(fullUrl)}`;
            image.alt = 'QR Code';
            image.className = 'w-60 h-60 object-contain';
            qrPlaceholder.replaceWith(image);
        }
    };

    shortCodeInput.addEventListener('input', updatePreview);
    updatePreview();

    if (copyButton) {
        copyButton.addEventListener('click', async () => {
            const code = String(shortCodeInput.value || '').trim().toLowerCase();
            if (!code) {
                return;
            }

            try {
                await navigator.clipboard.writeText(`${baseUrl}${code}`);
                copyButton.innerHTML = '<i class="fas fa-check mr-2"></i>Disalin';
                window.setTimeout(() => {
                    copyButton.innerHTML = '<i class="fas fa-copy mr-2"></i>Salin';
                }, 1200);
            } catch (error) {
                console.error(error);
            }
        });
    }

    if (deleteButton && deleteForm) {
        deleteButton.addEventListener('click', () => {
            if (window.confirm('Hapus short link ini?')) {
                deleteForm.submit();
            }
        });
    }
})();
</script>
@endsection