@extends('layouts.admin')

@section('title', 'Unlock - Konfigurasi Sertifikat')
@section('page-title', 'Konfigurasi Template Sertifikat')

@section('page-actions')
    <a href="{{ route('admin.events.index') }}" class="text-gray-500 hover:text-gray-700 font-medium flex items-center transition duration-200">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Event
    </a>
@endsection

@section('admin-content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ isUpdating: false }">
    <!-- HEADER -->
    <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-blue-50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
                <i class="fas fa-certificate"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Event Event</p>
                <p class="font-semibold text-gray-800">{{ $event->event_title }}</p>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.events.certificate.store', $event->id) }}" method="POST" enctype="multipart/form-data" id="certForm">
        @csrf
        @method('PATCH')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 p-6">
            {{-- KIRI: Input Data --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-white rounded-3xl border border-gray-100 p-8 space-y-6">
                    <h3 class="text-lg font-bold text-gray-800 border-b pb-4"><i class="fas fa-edit mr-2 text-primary"></i>Konten Sertifikat</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-input-field name="event_title" label="Judul Event" icon="fas fa-font" :value="old('event_title', $setup->event_title ?? $event->event_title)" required />
                        <x-input-field name="event_subtitle" label="Subtitle Event" icon="fas fa-heading" :value="old('event_subtitle', $setup->event_subtitle ?? '')" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-input-field name="speaker" label="Nama Narasumber" icon="fas fa-user-tie" :value="old('speaker', $setup->speaker ?? ($speaker->prefix_title . ' ' . $speaker->speaker_name))" required />
                        <x-input-field name="speaker_title" label="Jabatan Narasumber" icon="fas fa-briefcase" :value="old('speaker_title', $setup->speaker_title ?? $speaker->suffix_title)" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-input-field name="date_string" label="Tanggal Sertifikat" icon="fas fa-calendar-day" :value="old('date_string', $setup->date_string ?? \Carbon\Carbon::parse($event->date_start)->translatedFormat('l, j F Y'))" required />
                        <x-input-field name="date_extra" label="Keterangan Tambahan" icon="fas fa-info-circle" :value="old('date_extra', $setup->date_extra ?? '')" />
                    </div>
                </div>

                {{-- Upload Tanda Tangan --}}
                <div class="bg-white rounded-3xl border border-gray-100 p-8" 
                    x-data="{ 
                        hasSign: {{ ($setup->has_sign ?? true) ? 'true' : 'false' }}, 
                        imageUrl: '{{ ($setup && ($setup->temp_sign_path ?: $setup->sign_path)) ? asset('storage/' . ($setup->temp_sign_path ?: $setup->sign_path)) : '' }}',
                        shouldDelete: false,
                        clearImage() {
                            this.imageUrl = '';
                            this.shouldDelete = true;
                            document.getElementById('sign_image').value = '';
                            updatePreview();
                        }
                    }">
                    
                    <input type="hidden" name="delete_sign" :value="shouldDelete ? '1' : '0'">

                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-gray-800"><i class="fas fa-signature mr-2 text-primary"></i>Tanda Tangan Digital</h3>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="has_sign" value="1" class="sr-only peer" x-model="hasSign" @change="updatePreview()">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-primary after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                        </label>
                    </div>

                    <div class="flex flex-col md:flex-row items-center gap-8">
                        <div class="group relative w-48 h-32 bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl flex items-center justify-center overflow-hidden">
                            <template x-if="imageUrl">
                                <div class="relative w-full h-full flex items-center justify-center p-2 bg-white">
                                    <img :src="imageUrl" class="max-h-full object-contain">
                                    <button type="button" @click="clearImage()" class="absolute top-2 right-2 w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center shadow-lg hover:bg-red-600">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </template>
                            <template x-if="!imageUrl">
                                <div class="text-center">
                                    <i class="fas fa-image text-gray-300 text-3xl"></i>
                                    <p class="text-[10px] text-gray-400 mt-1 uppercase font-bold">Kosong</p>
                                </div>
                            </template>
                        </div>

                        <div class="flex-1 space-y-2">
                            <label class="block text-sm font-bold text-gray-700">Pilih Tanda Tangan (PNG Transparan)</label>
                            <input type="file" name="sign_image" id="sign_image" accept="image/png" 
                                @change="let file = $event.target.files[0]; if(file) { imageUrl = URL.createObjectURL(file); shouldDelete = false; hasSign = true; updatePreview(); }" 
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                        </div>
                    </div>
                </div>
            </div>

            {{-- KANAN: Visual Preview --}}
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white rounded-3xl border border-gray-100 p-8 space-y-6 sticky top-8">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-800"><i class="fas fa-eye mr-2 text-primary"></i>Live Preview</h3>
                        <div x-show="isUpdating" class="flex items-center text-primary text-xs font-bold animate-pulse">
                            <i class="fas fa-sync-alt fa-spin mr-1"></i> UPDATING...
                        </div>
                    </div>
                    
                    <div class="w-full bg-slate-100 rounded-2xl overflow-hidden border-2 border-slate-50 shadow-inner relative aspect-[1.414/1]">
                        <iframe id="previewIframe" name="previewIframe" src="about:blank" class="w-full h-full border-0 bg-white shadow-sm" @load="isUpdating = false"></iframe>
                    </div>

                    <div class="space-y-4">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Pilih Template</p>
                        <div class="grid grid-cols-1 gap-3">
                            @foreach(['1' => 'Standard Design', '2' => 'Modern Wave', '3' => 'Minimalist Clean'] as $val => $label)
                                <label class="flex items-center gap-3 p-4 border rounded-2xl cursor-pointer hover:bg-gray-50 transition-all has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                    <input type="radio" name="template" value="{{ $val }}" {{ ($setup->template ?? '1') == $val ? 'checked' : '' }} @change="updatePreview()" class="text-primary focus:ring-primary">
                                    <span class="text-sm font-semibold text-gray-700">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-50">
                        <button type="submit" class="w-full py-4 bg-primary text-white font-bold rounded-2xl shadow-lg shadow-primary/30 hover:shadow-xl transform active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i> Simpan Konfigurasi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function updatePreview() {
        const form = document.getElementById('certForm');
        const iframe = document.getElementById('previewIframe');
        if (!form || !iframe) return;

        // Tampilkan loading di UI Admin
        window.dispatchEvent(new CustomEvent('preview-loading'));

        const originalAction = form.action;
        const originalTarget = form.target;
        const methodInput = form.querySelector('input[name="_method"]');
        let originalMethod = '';

        if (methodInput) {
            originalMethod = methodInput.value;
            methodInput.value = 'POST'; // Preview butuh POST untuk kirim file
        }

        form.action = "{{ route('admin.events.certificate.preview', $event->id) }}";
        form.target = "previewIframe";
        form.submit();

        // Kembalikan form ke keadaan semula agar tombol "Simpan" tetap normal
        setTimeout(() => {
            form.action = originalAction;
            form.target = originalTarget;
            if (methodInput) methodInput.value = originalMethod;
        }, 200);
    }

    // Real-time Signature Preview (Tanpa Reload Iframe)
    function handleSignatureChange(event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            const iframe = document.getElementById('previewIframe');
            if (iframe.contentWindow) {
                iframe.contentWindow.postMessage({
                    type: 'UPDATE_SIGNATURE',
                    base64: e.target.result
                }, '*');
            }
        };
        reader.readAsDataURL(file);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('certForm');
        let timeout = null;

        updatePreview();

        // Listener Input Teks (Judul, Nama, dll) - Pakai Debounce agar tidak lag
        form.addEventListener('input', (e) => {
            if (['file', 'radio', 'checkbox'].includes(e.target.type)) return;
            clearTimeout(timeout);
            timeout = setTimeout(updatePreview, 1000);
        });

        // Khusus Input File TTD
        const signInput = document.getElementById('sign_image');
        if (signInput) {
            signInput.addEventListener('change', handleSignatureChange);
        }
    });

    // Matikan loading saat iframe selesai render (Custom event dari JS sertifikat)
    window.addEventListener('preview-loaded', () => {
        // Logika matikan spinner/loading di sini
        console.log("Preview Rendered!");
    });
</script>
@endpush
