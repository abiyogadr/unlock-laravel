@extends('layouts.admin')

@section('title', 'Unlock - Edit Paket')
@section('page-title', 'Edit Paket Webinar')

@section('page-actions')
    <a href="{{ route('admin.packets.index') }}" class="text-gray-500 hover:text-gray-700 font-medium flex items-center transition duration-200">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
@endsection

@section('admin-content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    
    {{-- Form Container --}}
    <form action="{{ route('admin.packets.update', $packet) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @csrf
        @method('PUT')

        {{-- Header Section --}}
        <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Informasi Paket</h3>
            </div>
            
            {{-- Status Toggle (Aktif/Non-Aktif) --}}
            <div class="flex items-center gap-3" x-data="{ active: {{ $packet->is_active ? 'true' : 'false' }} }">
                <span class="text-sm font-medium" :class="active ? 'text-green-600' : 'text-gray-500'" x-text="active ? 'Status: Aktif' : 'Status: Non-Aktif'"></span>
                <button type="button" 
                        @click="active = !active" 
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        :class="active ? 'bg-green-500' : 'bg-gray-200'"
                        role="switch" 
                        aria-checked="false">
                    <span aria-hidden="true" 
                          class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                          :class="active ? 'translate-x-5' : 'translate-x-0'"></span>
                </button>
                <input type="hidden" name="is_active" :value="active ? 1 : 0">
            </div>
        </div>

        <div class="p-6 sm:p-8 space-y-8">
            
            {{-- Bagian 1: Detail Dasar --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                {{-- Nama Paket --}}
                <x-input-field 
                    name="packet_name" 
                    label="Nama Paket" 
                    :value="old('packet_name', $packet->packet_name)" 
                    placeholder="Contoh: Paket Premium"
                    class="col-span-2 md:col-span-1"
                    required 
                />

                {{-- Harga --}}
                <div class="col-span-2 md:col-span-1">
                    <x-input-field 
                        type="number"
                        name="price" 
                        label="Harga (Rp)" 
                        prefix="Rp "
                        :value="old('price', $packet->price)" 
                        placeholder="0"
                        min="0"
                        required 
                        :showError="false"
                    />
                    <p class="text-xs text-gray-500 mt-1">Isi 0 untuk paket gratis.</p>
                    @error('price') 
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p> 
                    @enderror
                </div>
            </div>

            {{-- Deskripsi dengan CKEditor --}}
            <div class="col-span-3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi Paket</label>
                
                {{-- Textarea asli disembunyikan oleh CKEditor nanti --}}
                <textarea name="description" id="editor" rows="6"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300">{{ old('description', $packet->description) }}</textarea>
                
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <hr class="border-gray-100">

            {{-- Bagian 2: Persyaratan (Requirements) --}}
            <div>
                <h4 class="text-base font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-tasks text-primary mr-2"></i> Persyaratan Upload Bukti
                </h4>
                <p class="text-sm text-gray-500 mb-6 bg-blue-50 text-blue-700 p-3 rounded-lg border border-blue-100">
                    <i class="fas fa-info-circle mr-1"></i> Centang tugas yang <strong>wajib</strong> diupload oleh peserta saat mendaftar paket ini.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @php
                        $reqs = [
                            'follow_ig' => ['label' => 'Follow Instagram', 'icon' => 'fab fa-instagram text-pink-600'],
                            'follow_yt' => ['label' => 'Subscribe YouTube', 'icon' => 'fab fa-youtube text-red-600'],
                            'follow_tt' => ['label' => 'Follow TikTok', 'icon' => 'fab fa-tiktok text-black'],
                            'tag_friends' => ['label' => 'Tag Teman', 'icon' => 'fas fa-user-friends text-blue-600'],
                            'repost_story' => ['label' => 'Repost IG Story', 'icon' => 'fas fa-history text-purple-600'],
                            'repost_groups' => ['label' => 'Share ke Grup WA', 'icon' => 'fab fa-whatsapp text-green-600'],
                            'repost_wa_story' => ['label' => 'Share WA Story', 'icon' => 'fas fa-mobile-alt text-green-500'],
                        ];
                        // Ambil data existing dari JSON
                        $savedReqs = $packet->requirements ?? [];
                    @endphp

                    @foreach($reqs as $key => $info)
                        <label class="relative flex items-start p-4 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 hover:border-primary transition-all duration-200 group">
                            <div class="flex items-center h-5">
                                <input type="checkbox" 
                                       name="requirements[{{ $key }}]" 
                                       value="1" 
                                       {{ old("requirements.$key", $savedReqs[$key] ?? false) ? 'checked' : '' }}
                                       class="focus:ring-primary h-5 w-5 text-primary border-gray-300 rounded transition duration-150 ease-in-out">
                            </div>
                            <div class="ml-3 text-sm">
                                <div class="font-medium text-gray-700 group-hover:text-primary flex items-center gap-2">
                                    <i class="{{ $info['icon'] }} w-5 text-center"></i>
                                    {{ $info['label'] }}
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Footer Action --}}
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
            <a href="{{ route('admin.packets.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-100 transition duration-200 cursor-pointer">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-white font-medium hover:bg-secondary shadow-lg shadow-primary/30 transition duration-200 transform hover:-translate-y-0.5 cursor-pointer">
                <i class="fas fa-save mr-2"></i>Simpan
            </button>
        </div>

    </form>
</div>
@endsection

@push('scripts')
{{-- Load CKEditor CDN --}}
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        ClassicEditor
            .create(document.querySelector('#editor'), {
                // Konfigurasi Toolbar Sederhana (Sesuai Request)
                toolbar: [ 'bold', 'italic', 'bulletedList', 'numberedList', '|', 'undo', 'redo' ],
                
                // Menghilangkan branding CKEditor (opsional)
                removePlugins: [ 'PoweredBy' ]
            })
            .then(editor => {
                // Style penyesuaian agar tinggi editor pas
                editor.editing.view.change(writer => {
                    writer.setStyle('min-height', '150px', editor.editing.view.document.getRoot());
                    writer.setStyle('border-radius', '0 0 0.5rem 0.5rem', editor.editing.view.document.getRoot());
                });
            })
            .catch(error => {
                console.error(error);
            });
    });
</script>

<style>
    .ck-editor__editable_inline {
        padding: 0 1rem !important;
    }
    .ck-toolbar {
        border-radius: 0.5rem 0.5rem 0 0 !important;
        background-color: #f9fafb !important;
    }
    .ck.ck-editor__main>.ck-editor__editable:not(.ck-focused) {
        border-color: #d1d5db !important;
    }
    .ck.ck-editor__main>.ck-editor__editable.ck-focused {
        border-color: #4f46e5 !important;
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2) !important;
    }
    .ck-content ul, 
    .ck-content ol {
        margin-left: 1.5rem !important;
        padding-left: 1rem !important;
        list-style-position: outside !important;
    }

    .ck-content ul {
        list-style-type: disc !important;
    }

    .ck-content ol {
        list-style-type: decimal !important;
    }
</style>
@endpush