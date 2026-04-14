@php
    $publicUrl = $shortLink->exists ? $shortLink->publicUrl() : '';
    $expiresAtValue = old('expires_at', optional($shortLink->expires_at)->format('Y-m-d\TH:i'));
    $shortCodeValue = old('short_code', $shortLink->short_code);
    $originalUrlValue = old('original_url', $shortLink->original_url);
    $isActiveValue = old('is_active', $shortLink->exists ? $shortLink->is_active : true);
@endphp

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 space-y-6">
        <div class="space-y-4">
            <h4 class="text-sm font-bold text-primary uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-link"></i> Short Link
            </h4>
            <div class="grid grid-cols-1 gap-5">
                <x-input-field
                    type="url"
                    name="original_url"
                    label="URL Tujuan"
                    icon="fas fa-globe"
                    :value="$originalUrlValue"
                    placeholder="https://contoh.com/halaman-panjang"
                    required
                />

                <x-input-field
                    name="short_code"
                    label="Short Code"
                    icon="fas fa-scissors"
                    :value="$shortCodeValue"
                    placeholder="Kosongkan untuk generate otomatis"
                    :showError="true"
                />

                <x-input-field
                    type="datetime-local"
                    name="expires_at"
                    label="Expired Link"
                    icon="fas fa-clock"
                    :value="$expiresAtValue"
                    :showError="true"
                />
            </div>
        </div>

        <div class="space-y-4">
            <h4 class="text-sm font-bold text-primary uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-toggle-on"></i> Status
            </h4>
            <label class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 cursor-pointer hover:border-primary/30 transition">
                <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-primary focus:ring-primary" @checked($isActiveValue)>
                <span>
                    <span class="block text-sm font-semibold text-gray-900">Aktifkan link</span>
                    <span class="block text-xs text-gray-500">Nonaktifkan jika link sementara tidak boleh diakses publik.</span>
                </span>
            </label>
        </div>
    </div>

    <div class="xl:col-span-1">
        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-5 sticky top-6 space-y-4">
            <div>
                <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-qrcode"></i> Preview
                </h4>
                <p class="text-xs text-slate-500 mt-1">QR code dan alamat publik akan mengikuti short code yang diisi.</p>
            </div>

            <div class="rounded-xl bg-white border border-gray-200 p-4 flex items-center justify-center min-h-[240px]">
                @if($publicUrl)
                    <img
                        data-qr-image
                        src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&data={{ urlencode($publicUrl) }}"
                        alt="QR Code"
                        class="w-60 h-60 object-contain"
                    >
                @else
                    <div data-qr-placeholder class="text-center text-gray-400 text-sm">
                        <i class="fas fa-qrcode text-4xl mb-3 block opacity-40"></i>
                        QR akan muncul setelah short code diisi.
                    </div>
                @endif
            </div>

            <div class="space-y-2">
                <p class="text-[11px] uppercase tracking-wider font-semibold text-slate-500">Alamat Short Link</p>
                <div class="rounded-xl bg-white border border-gray-200 px-4 py-3 text-sm font-mono text-slate-700 break-all" data-short-url-text>
                    {{ $publicUrl ?: 'Belum tersedia' }}
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ $publicUrl ?: '#' }}" target="_blank" data-short-url-open class="px-4 py-2 rounded-lg bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition {{ $publicUrl ? '' : 'pointer-events-none opacity-40' }}">
                        <i class="fas fa-arrow-up-right-from-square mr-2"></i>Buka
                    </a>
                    <button type="button" data-copy-short-url class="px-4 py-2 rounded-lg bg-white border border-gray-200 text-slate-700 text-xs font-semibold hover:bg-gray-50 transition {{ $publicUrl ? '' : 'opacity-40 cursor-not-allowed' }}" {{ $publicUrl ? '' : 'disabled' }}>
                        <i class="fas fa-copy mr-2"></i>Salin
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>