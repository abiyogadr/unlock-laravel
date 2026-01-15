@extends('layouts.admin')

@section('title', 'Unlock - Edit Speaker')
@section('page-title', 'Edit Data Speaker')

@section('page-actions')
    <a href="{{ route('admin.speakers.index') }}" class="text-gray-500 hover:text-gray-700 font-medium flex items-center transition duration-200">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
@endsection

@section('admin-content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <form action="{{ route('admin.speakers.update', $speaker->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            {{-- Header Section --}}
            <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Edit Profil Speaker</h3>
                    <p class="text-sm text-gray-500 mt-1">Perbarui data diri dan informasi profesional pemateri.</p>
                </div>
                {{-- Speaker Code (Readonly saat edit) --}}
                <div class="text-right">
                    <span class="block text-[10px] uppercase font-bold text-gray-400 mb-1">Kode Speaker</span>
                    <span class="bg-gray-200 text-gray-600 font-mono text-xs py-1 px-3 rounded-lg">{{ $speaker->speaker_code }}</span>
                    <input type="hidden" name="speaker_code" value="{{ $speaker->speaker_code }}">
                </div>
            </div>

            <div class="p-6 sm:p-8 space-y-8">
                
                {{-- Bagian 1: Identitas & Gelar --}}
                <div class="space-y-4">
                    <h4 class="text-sm font-bold text-primary uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-user-tie"></i> Identitas & Gelar
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <x-input-field 
                            name="prefix_title" 
                            label="Gelar Depan" 
                            :value="old('prefix_title', $speaker->prefix_title)" 
                            placeholder="Dr. / Prof." 
                        />

                        <x-input-field 
                            name="speaker_name" 
                            label="Nama Lengkap" 
                            :value="old('speaker_name', $speaker->speaker_name)" 
                            placeholder="Nama tanpa gelar" 
                            class="md:col-span-2"
                            required 
                        />

                        <x-input-field 
                            name="suffix_title" 
                            label="Gelar Belakang" 
                            :value="old('suffix_title', $speaker->suffix_title)" 
                            placeholder="S.Kom, M.T" 
                        />
                    </div>
                </div>

                <hr class="border-gray-100">

                {{-- Bagian 2: Kontak & Profesional --}}
                <div class="space-y-4">
                    <h4 class="text-sm font-bold text-primary uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-briefcase"></i> Kontak & Karir
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-input-field 
                            type="email"
                            name="email" 
                            label="Email Pemateri" 
                            icon="fas fa-envelope"
                            :value="old('email', $speaker->email)" 
                            placeholder="email@speaker.com"
                            required 
                        />

                        <x-input-field 
                            name="phone" 
                            label="No. WhatsApp" 
                            icon="fab fa-whatsapp"
                            :value="old('phone', $speaker->phone)" 
                            placeholder="081234567xxx"
                            required 
                        />

                        <x-input-field 
                            name="position" 
                            label="Jabatan / Posisi" 
                            :value="old('position', $speaker->position)" 
                            placeholder="Contoh: Senior Developer"
                            required 
                        />

                        <x-input-field 
                            name="company" 
                            label="Instansi / Perusahaan" 
                            :value="old('company', $speaker->company)" 
                            placeholder="Contoh: Google Indonesia"
                            required 
                        />
                    </div>
                </div>
            </div>

            {{-- Footer Action --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.speakers.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-100 transition duration-200 cursor-pointer">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-white font-medium hover:bg-secondary shadow-lg shadow-primary/30 transition duration-200 transform hover:-translate-y-0.5 cursor-pointer">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
