@extends('layouts.admin')

@section('title', 'Unlock - Tambah Short Link')
@section('page-title', 'Tambah Short Link Baru')

@section('page-actions')
    <a href="{{ route('admin.short-links.index') }}" class="text-gray-500 hover:text-gray-700 font-medium flex items-center transition duration-200">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
@endsection

@section('admin-content')
<form action="{{ route('admin.short-links.store') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    @csrf

    <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-bold text-gray-800">Generator Short Link</h3>
            <p class="text-sm text-gray-500 mt-1">Buat short link dengan kode otomatis atau custom slug.</p>
        </div>
    </div>

    <div class="p-6 sm:p-8">
        @include('admin.short-links._form', ['shortLink' => $shortLink])
    </div>

    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
        <a href="{{ route('admin.short-links.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-100 transition duration-200 cursor-pointer">
            Batal
        </a>
        <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-white font-medium hover:bg-secondary shadow-lg shadow-primary/30 transition duration-200 transform hover:-translate-y-0.5 cursor-pointer">
            <i class="fas fa-save mr-2"></i>Simpan
        </button>
    </div>
</form>

<script>
(() => {
    const shortCodeInput = document.querySelector('[name="short_code"]');
    const shortUrlText = document.querySelector('[data-short-url-text]');
    const qrImage = document.querySelector('[data-qr-image]');
    const qrPlaceholder = document.querySelector('[data-qr-placeholder]');
    const openButton = document.querySelector('[data-short-url-open]');
    const copyButton = document.querySelector('[data-copy-short-url]');
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
})();
</script>
@endsection