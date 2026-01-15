@extends('layouts.app')

@section('title', 'Unlock - Profile')

@section('content')

{{-- DATA PREPARATION UNTUK ALPINE --}}
@php
    $user = auth()->user();
    // Siapkan data untuk dropdown dalam format {value, label} agar sesuai dengan x-select-search
    $professionsData = collect($professions)->map(fn($p) => ['value' => $p, 'label' => $p]);

    $citiesData = $cities_provinces->map(fn($item) => [
        'value' => $item->kode,          // Value dropdown = Kode (misal "35.78")
        'label' => $item->nama_lengkap   // Label dropdown = Nama Kota (misal "Kota Surabaya")
    ]);

    $foundCityCode = '';
    if ($user && $user->city) {
        $match = $cities_provinces->first(function($item) use ($user) {
            return strtolower(trim(explode('-', $item->nama_lengkap)[0])) === strtolower(trim($user->city));
        });
        $foundCityCode = $match ? $match->kode : '';
    }

    // B. Cari Kode Tempat Lahir berdasarkan Nama Tempat Lahir User
    $foundBirthCode = '';
    if ($user && $user->birth_place) {
        $match = $cities_provinces->first(function($item) use ($user) {
            return strtolower(trim(explode('-', $item->nama_lengkap)[0])) === strtolower(trim($user->birth_place));
        });
        $foundBirthCode = $match ? $match->kode : '';
    }
    
    // Siapkan data awal form dari user auth
    $userData = [
        'birth_place_code' => $foundBirthCode,
        'birth_place'      => $user->birth_place ?? '',
        'city_code' => $foundCityCode,
        'city'      => $user->city ?? '',
        'job' => auth()->user()->job ?? '',
        // Tambahkan field lain jika perlu reaktif
    ];
@endphp

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     {{-- Inisialisasi Alpine Data --}}
     x-data="{
        form: {{ json_encode($userData) }},
        options: {
            cities: {{ json_encode($citiesData) }},
            professions: {{ json_encode($professionsData) }}
        },
        errors: {}, // Placeholder untuk kompatibilitas component select-search
        getLabel(value, list) {
            if (!list || !value) return '';
            const item = list.find(i => i.value == value);
            return item ? item.label : value;
        }
     }"
>

    {{-- ALERT SUKSES --}}
    @if (session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-md" x-data="{ show: true }" x-show="show">
            <div class="flex justify-between items-center">
                <p class="text-sm text-green-700 font-semibold">{{ session('success') }}</p>
                <button @click="show = false" class="text-green-500 hover:text-green-700"><i class="fas fa-times"></i></button>
            </div>
        </div>
    @endif
    @if (session('info'))
        <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-md" x-data="{ show: true }" x-show="show">
            <div class="flex justify-between items-center">
                <p class="text-sm text-yellow-700 font-semibold">{{ session('info') }}</p>
                <button @click="show = false" class="text-yellow-500 hover:text-yellow-700"><i class="fas fa-times"></i></button>
            </div>
        </div>
    @endif

    {{-- ALERT WARNING --}}
    @if (session('warning'))
        <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-md" x-data="{ show: true }" x-show="show">
            <div class="flex justify-between items-start">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700 font-semibold">{{ session('warning') }}</p>
                    </div>
                </div>
                <button @click="show = false" class="text-yellow-500 hover:text-yellow-700 ml-3"><i class="fas fa-times"></i></button>
            </div>
        </div>
    @endif

    {{-- ERROR GLOBAL --}}
    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700 font-semibold">Terjadi kesalahan pada input Anda.</p>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- FORM UTAMA --}}
    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data"
          class="bg-white py-8 px-6 shadow-xl shadow-gray-200/50 sm:rounded-2xl sm:px-10 border border-gray-100">
        
        <div class="mb-8 border-b pb-4">
            <h1 class="text-2xl font-bold text-gray-900">Pengaturan Profil</h1>
            <p class="text-gray-500 text-sm mt-1">Perbarui informasi data diri Anda.</p>
        </div>
        
        @csrf

        {{-- FOTO PROFIL (Logic Preview Javascript Murni Saja Lebih Simple untuk File Upload) --}}
        <div class="mb-8" x-data="{ preview: '{{ auth()->user()->avatar ? Storage::url(auth()->user()->avatar) : '' }}' }">
            <label class="block text-sm font-semibold text-gray-600 mb-3">Foto Profil</label>
            <div class="flex items-center gap-6">
                <div class="shrink-0 relative group">
                    <!-- Image Preview -->
                    <template x-if="preview">
                        <img :src="preview" class="h-24 w-24 rounded-full object-cover border-4 border-white shadow-md">
                    </template>
                    <!-- Placeholder -->
                    <template x-if="!preview">
                        <div class="h-24 w-24 rounded-full bg-primary/10 flex items-center justify-center text-primary border-4 border-white shadow-md">
                            <i class="fas fa-user text-3xl"></i>
                        </div>
                    </template>
                </div>

                <div class="flex-1">
                    <input type="file" name="avatar" accept="image/*"
                           @change="const file = $event.target.files[0]; if(file){ preview = URL.createObjectURL(file) }"
                           class="block w-full text-sm text-gray-500
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-full file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-primary/10 file:text-primary
                                  hover:file:bg-primary/20 cursor-pointer transition-colors">
                    <p class="mt-2 text-xs text-gray-400">Format: JPG, PNG, GIF. Maksimal 2MB.</p>
                    @error('avatar') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- INFO DASAR --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-input-field 
                name="name" 
                label="Nama Lengkap" 
                icon="fas fa-user" 
                :value="old('name', auth()->user()->name)" 
                required 
            />

            <x-input-field 
                name="username" 
                label="Username" 
                icon="fas fa-at" 
                :value="old('username', auth()->user()->username)" 
                required 
            />
        </div>

        {{-- KONTAK --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <x-input-field 
                name="whatsapp" 
                label="No. WhatsApp" 
                icon="fas fa-phone" 
                :value="old('whatsapp', '0'.auth()->user()->phone)" 
                required 
            />

            <x-input-field 
                type="email"
                name="email" 
                label="Email" 
                icon="fas fa-envelope" 
                :value="auth()->user()->email" 
                readonly
            >
                <x-slot:suffix>
                    @if(auth()->user()->hasVerifiedEmail())
                        <span class="text-[10px] font-bold text-green-600 bg-green-50 px-2 py-1 rounded-lg border border-green-200 uppercase">
                            Verified
                        </span>
                    @else
                        <button type="button" 
                            onclick="event.preventDefault(); document.getElementById('resend-verification-form').submit();"
                            class="text-[10px] font-bold text-white bg-orange-500 hover:bg-orange-600 px-2 py-1.5 rounded-lg transition-all shadow-sm uppercase cursor-pointer">
                            Verify Now
                        </button>
                    @endif
                </x-slot:suffix>
            </x-input-field>
        </div>

        {{-- GENDER --}}
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">Jenis Kelamin</label>
            <div class="flex gap-6">
                <label class="relative flex items-center p-3 shadow-sm rounded-xl border border-gray-200 cursor-pointer hover:bg-blue-50 hover:border-blue-200 transition-all w-full md:w-auto">
                    <input type="radio" name="gender" value="male" {{ auth()->user()->gender == 'male' ? 'checked' : '' }} class="h-4 w-4 text-primary border-gray-300 focus:ring-primary">
                    <span class="ml-3 flex items-center text-sm font-semibold text-gray-600 font-medium">
                        <i class="fas fa-male text-blue-500 text-lg mr-2"></i> Laki-laki
                    </span>
                </label>
                <label class="relative flex items-center p-3 shadow-sm rounded-xl border border-gray-200 cursor-pointer hover:bg-pink-50 hover:border-pink-200 transition-all w-full md:w-auto">
                    <input type="radio" name="gender" value="female" {{ auth()->user()->gender == 'female' ? 'checked' : '' }} class="h-4 w-4 text-primary border-gray-300 focus:ring-primary">
                    <span class="ml-3 flex items-center text-sm font-semibold text-gray-600 font-medium">
                        <i class="fas fa-female text-pink-500 text-lg mr-2"></i> Perempuan
                    </span>
                </label>
            </div>
            @error('gender') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- TEMPAT & TANGGAL LAHIR --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            {{-- TEMPAT LAHIR --}}
            <div>
                <x-select-search 
                    label="Tempat Lahir" 
                    name="birth_place_code" 
                    model="form.birth_place_code" 
                    options="options.cities" 
                    placeholder="Pilih kota kelahiran"
                    icon="fas fa-map-marker-alt" 
                />
                {{-- Icon ditambahkan di atas ^ --}}
                
                <input type="hidden" name="birth_place" :value="getLabel(form.birth_place_code, options.cities)">
            </div>

           <x-input-field 
                type="date" 
                name="birth_date" 
                label="Tanggal Lahir" 
                icon="fas fa-calendar-alt" 
                :value="old('birth_date', auth()->user()->birth_date)" 
            />
        </div>

        {{-- DOMISILI & PEKERJAAN --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
                <x-select-search 
                    label="Kabupaten Domisili" 
                    name="city_code" 
                    model="form.city_code" 
                    options="options.cities" 
                    placeholder="Cari domisili..."
                    icon="fas fa-map" 
                />
                {{-- Icon ditambahkan di atas ^ --}}

                <input type="hidden" name="city" :value="getLabel(form.city_code, options.cities)">
            </div>

            <div>
                <x-select-search 
                    label="Pekerjaan" 
                    name="job" 
                    model="form.job" 
                    options="options.professions" 
                    placeholder="Pilih profesi..."
                    icon="fas fa-briefcase" 
                />
                {{-- Icon ditambahkan di atas ^ --}}
            </div>
        </div>

        {{-- SOSMED --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <x-input-field 
                name="instagram" 
                label="Instagram" 
                icon="fa-brands fa-instagram" 
                prefix="instagram.com/" 
                :value="old('instagram', auth()->user()->instagram)" 
                placeholder="username"
            />

            <x-input-field 
                name="linkedin" 
                label="Linkedin" 
                icon="fa-brands fa-linkedin" 
                prefix="linkedin.com/in/"
                :value="old('linkedin', auth()->user()->linkedin)" 
                placeholder="username"
            />
        </div>

        {{-- TOMBOL SUBMIT --}}
        <div class="mt-10 flex justify-end border-t pt-6">
            <button type="submit"
                    class="px-5 py-2.5 rounded-lg bg-primary text-white font-medium hover:bg-secondary shadow-lg shadow-primary/30 transition duration-200 transform hover:-translate-y-0.5">
                <i class="fas fa-save mr-2"></i> Simpan
            </button>
        </div>
    </form>
    <form id="resend-verification-form" action="{{ route('verification.send') }}" method="POST" class="hidden">
        @csrf
    </form>
</div>

@push('scripts')
<script>
    // Preview foto profil
    document.getElementById('avatar')?.addEventListener('change', function (e) {
        const [file] = this.files;
        if (!file) return;
        const preview = document.getElementById('preview');
        const placeholder = document.getElementById('preview-placeholder');
        const url = URL.createObjectURL(file);
        if (preview) {
            preview.src = url;
            preview.classList.remove('hidden');
        }
        if (placeholder) {
            placeholder.classList.add('hidden');
        }
    });
</script>
@endpush
@endsection
