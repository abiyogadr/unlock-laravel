@extends('layouts.app')

@section('title', 'Unlock - Pendaftaran')

{{-- Import Javascript Logic --}}
@vite('resources/js/registration.js')

@section('content')

{{-- DATA PREPARATION (PHP Helper) --}}
@php
    // 1. Events & Professions
    $eventsData = $events->map(fn($e) => ['value' => (string)$e->id, 'label' => $e->event_code . ' - ' . $e->event_title]);
    $professionsData = collect($professions)->map(fn($p) => ['value' => $p, 'label' => $p]);

    // 2. PROCESSING DATA WILAYAH (Split Collection)
    // Ambil Provinsi (Kode 2 digit, misal '11')
    $provincesOnly = $provinces->filter(fn($area) => strlen($area->kode) == 2)->values();
    $provincesData = $provincesOnly->map(fn($p) => ['value' => $p->kode, 'label' => $p->nama]);

    // Ambil Kota (Kode 5 digit, misal '11.01') - Kirim semua ke frontend
    $citiesOnly = $provinces->filter(fn($area) => strlen($area->kode) == 5)->values();
    $allCitiesData = $citiesOnly->map(fn($c) => ['value' => $c->kode, 'label' => $c->nama]);

    // 3. PREPARE USER DATA
    $user = auth()->user();

    $userCityCode = '';
    $userProvinceCode = '';

    if ($user && $user->city) {
        // Ambil kode kota dari label (nama kota)
        $city = $citiesOnly->firstWhere('nama', $user->city);

        if ($city) {
            $userCityCode = $city->kode;

            // Ambil kode provinsi dari kode kota (11.01 → 11)
            $parts = explode('.', $userCityCode);
            $userProvinceCode = $parts[0] ?? '';
        }
    }

    $initialData = [
        'event_id'   => old('event_id', request('event_code') ? $events->where('event_code', request('event_code'))->first()->id ?? '' : ''),
        'packet_id'  => old('packet_id', ''),
        'name'   => old('name', $user->name ?? ''),
        'email'      => old('email', $user->email ?? ''),
        'whatsapp'   => old('whatsapp', $user ? '0'.$user->phone : ''),
        'age'        => old('age', ($user && $user->birth_date) ? \Carbon\Carbon::parse($user->birth_date)->age : ''),
        'gender'     => old('gender', $user->gender ?? ''),
        'profession' => old('profession', $user->job ?? ''),
        
        // REQUEST ANDA: Set nilai awal
        'province'   => old('province', $userProvinceCode ?? ''),
        'city'       => old('city', $userCityCode ?? ''),
        
        'channel_information' => old('channel_information', [])
    ];
@endphp

@guest
    {{-- POPUP BELUM LOGIN (Sama seperti sebelumnya) --}}
    <div id="login-notification-modal" class="fixed inset-0 z-[999] flex items-center justify-center px-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="text-center mb-6">
                <div class="mx-auto w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-lock text-primary text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Kamu belum login?</h3>
                <p class="text-gray-500 mt-2 text-sm">Masuk agar data tersimpan di akun Anda.</p>
            </div>
            <a href="{{ route('login') }}?redirect={{ urlencode(request()->fullUrl()) }}" class="block w-full py-3 px-4 bg-primary text-white font-semibold rounded-xl text-center shadow-md">Masuk Dulu</a>
        </div>
    </div>
@endguest

@if(auth()->check())
{{-- ROOT COMPONENT ALPINE --}}
<div class="max-w-5xl mx-auto my-8 px-4 auth"
     x-data="registrationForm({
         events: {{ json_encode($eventsData) }},
         professions: {{ json_encode($professionsData) }},
         provinces: {{ json_encode($provincesData) }},
         allCities: {{ json_encode($allCitiesData) }},
         old: {{ json_encode($initialData) }}
     })"
>
    
    {{-- Progress Bar (Static HTML dengan class binding Alpine) --}}
    <div class="mb-8">
        <div class="flex items-center justify-between max-w-2xl mx-auto">
            @foreach([1=>'Event', 2=>'Data Diri', 3=>'Tugas', 4=>'Konfirmasi'] as $step => $label)
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-colors duration-300"
                         :class="currentStep >= {{ $step }} ? 'bg-primary text-white' : 'bg-gray-200 text-gray-500'">{{ $step }}</div>
                    <span class="text-xs sm:text-sm mt-2 font-medium" 
                          :class="currentStep >= {{ $step }} ? 'text-primary' : 'text-gray-600'">{{ $label }}</span>
                </div>
                @if($step < 4)
                    <div class="flex-1 h-1 mx-2 sm:mx-4 transition-colors duration-300" 
                         :class="currentStep > {{ $step }} ? 'bg-primary' : 'bg-gray-200'"></div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 sm:p-8 md:p-10 shadow-xl border border-gray-100">
        
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-primary mb-2">Formulir Pendaftaran</h2>
            <p class="text-gray-600 text-sm">Lengkapi data di bawah ini.</p>
        </div>

        {{-- Global Alert --}}
        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 text-sm" role="alert">
                <p class="font-bold">Ada Kesalahan!</p>
                <p>{{ $errors->first() }}</p>
            </div>
        @endif

        <form @submit.prevent="if(currentStep === 4) $el.submit()" action="{{ route('registration.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- Simpan step saat ini agar controller tahu jika ada error --}}
            <input type="hidden" name="current_step" :value="currentStep">

            {{-- STEP 1 --}}
            <div x-show="currentStep === 1" x-transition.opacity>
                <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center"><i class="fas fa-calendar-alt text-primary mr-3"></i> Pilih Event & Paket</h3>
                
                <div class="grid md:grid-cols-1 gap-6">
                    <x-select-search label="Pilih Event" name="event_id" model="form.event_id" options="options.events" required="true" />
                    
                    <div class="relative">
                        <x-select-search label="Pilih Paket" name="packet_id" model="form.packet_id" options="packets" required="true" disabled="!form.event_id" placeholder="-- Pilih Paket --" />
                    </div>

                    {{-- Deskripsi & Harga --}}
                    <div x-show="packetDescription" x-html="packetDescription" class="mt-3 p-3 bg-gray-50 rounded-lg text-sm text-gray-600 border"></div>
                    
                    <div x-show="priceDisplay !== 'Rp 0'" class="mt-4 p-4 bg-primary/5 rounded-lg border-l-4 border-primary">
                        <div class="text-sm font-medium text-gray-700">Biaya Paket</div>
                        <div class="text-2xl font-bold text-secondary" x-text="priceDisplay"></div>
                    </div>
                </div>
            </div>

            {{-- STEP 2 --}}
            <div x-show="currentStep === 2" style="display: none;" x-transition.opacity>
                <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center"><i class="fas fa-user text-primary mr-3"></i> Data Diri</h3>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <x-input-field 
                        name="name" 
                        label="Nama Lengkap" 
                        x-model="form.name" 
                        readonly 
                        required 
                    />

                    <x-input-field 
                        type="email"
                        name="email" 
                        label="Email" 
                        x-model="form.email" 
                        required 
                    />

                    <x-input-field 
                        name="whatsapp" 
                        label="WhatsApp" 
                        x-model="form.whatsapp" 
                        required 
                    />

                    <x-input-field 
                        type="number"
                        name="age" 
                        label="Usia" 
                        x-model="form.age" 
                        required 
                    />

                    {{-- Gender Manual Data --}}
                    <x-select-search label="Jenis Kelamin" name="gender" model="form.gender" options="[{value:'male', label:'Laki-laki'}, {value:'female', label:'Perempuan'}]" required="true" />
                    
                    <x-select-search label="Profesi" name="profession" model="form.profession" options="options.professions" required="true" />
                    
                    <x-select-search label="Provinsi" name="province" model="form.province" options="options.provinces" required="true" />
                    
                    <x-select-search label="Kota/Kabupaten" name="city" model="form.city" options="cities" required="true" disabled="!form.province" />
                </div>

                                {{-- Channel Checkbox --}}
                <div class="mt-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        Tahu info dari mana? <span class="text-red-500">*</span>
                    </label>
                    
                    {{-- Container Grid --}}
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($channels as $ch)
                            <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer hover:border-primary hover:bg-primary/5 transition-colors"
                                   {{-- Tambahkan border merah dinamis jika error --}}
                                   :class="errors.channel_information ? 'border-red-300 bg-red-50' : 'border-gray-200'"
                            >
                                <input type="checkbox" 
                                       name="channel_information[]" 
                                       value="{{ $ch }}" 
                                       x-model="form.channel_information" 
                                       class="w-5 h-5 rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="text-xs sm:text-sm font-medium">{{ $ch }}</span>
                            </label>
                        @endforeach
                    </div>

                    {{-- Pesan Error --}}
                    <p x-show="errors.channel_information" 
                       x-text="errors.channel_information" 
                       class="mt-2 text-sm text-red-600 font-medium"></p>
                </div>
            </div>

            {{-- STEP 3 (Uploads) --}}
            <div x-show="currentStep === 3" style="display: none;" x-transition.opacity>
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-upload text-primary mr-3"></i> Tugas Media Sosial
                </h3>
                <p class="text-gray-600 text-sm mb-6">Silakan upload bukti screenshot untuk tugas berikut.</p>
                
                <div class="space-y-4">
                    <template x-for="(req, key) in activeRequirements" :key="key">
                        <div class="border-2 border-dashed rounded-lg p-5 bg-gray-50 transition-colors"
                             :class="errors['upload_' + req.key] ? 'border-red-500 bg-red-50' : 'border-gray-200'"
                        >
                            <div class="flex items-center mb-3">
                                <i :class="req.icon" class="text-2xl mr-3"></i>
                                <div class="font-semibold text-gray-800 text-sm">
                                    <span x-text="req.label"></span> <span class="text-red-500">*</span>
                                </div>
                            </div>
                            
                            {{-- Input File --}}
                            {{-- Logic: Saat file dipilih, set state uploads[key] jadi true --}}
                            <input type="file" 
                                   :name="'upload_proofs[' + req.key + ']'" 
                                   accept="image/*" 
                                   required 
                                   @change="uploads[req.key] = $el.files.length > 0"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 hover:file:bg-primary/20"
                                   :class="errors['upload_' + req.key] ? 'file:bg-red-100 file:text-red-700' : 'file:bg-primary/10 file:text-primary'"
                            >
                            
                            {{-- Pesan Error --}}
                            <p x-show="errors['upload_' + req.key]" x-text="errors['upload_' + req.key]" class="text-red-500 text-xs mt-2 font-medium"></p>
                        </div>
                    </template>
                </div>
            </div>

            {{-- STEP 4 (Konfirmasi) --}}
            <div x-show="currentStep === 4" style="display: none;" x-transition.opacity>
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-check-circle text-primary mr-3"></i> Konfirmasi Data
                </h3>

                <div class="space-y-6">

                    {{-- 1. DATA DIRI PESERTA (tampil pertama) --}}
                    <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                        <h4 class="font-bold text-gray-700 mb-3 border-b pb-2">Data Diri Peserta</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-4 text-sm">
                            <div>
                                <div class="text-gray-500 text-xs uppercase tracking-wide">Nama Lengkap</div>
                                <div class="font-medium text-gray-900" x-text="form.name"></div>
                            </div>
                            <div>
                                <div class="text-gray-500 text-xs uppercase tracking-wide">Email</div>
                                <div class="font-medium text-gray-900" x-text="form.email"></div>
                            </div>
                            <div>
                                <div class="text-gray-500 text-xs uppercase tracking-wide">WhatsApp</div>
                                <div class="font-medium text-gray-900" x-text="form.whatsapp"></div>
                            </div>
                            <div class="flex gap-4">
                                <div>
                                    <div class="text-gray-500 text-xs uppercase tracking-wide">Usia</div>
                                    <div class="font-medium text-gray-900"><span x-text="form.age"></span> Tahun</div>
                                </div>
                                <div>
                                    <div class="text-gray-500 text-xs uppercase tracking-wide">Gender</div>
                                    <div class="font-medium text-gray-900" x-text="form.gender === 'male' ? 'Laki-laki' : 'Perempuan'"></div>
                                </div>
                            </div>
                            <div>
                                <div class="text-gray-500 text-xs uppercase tracking-wide">Profesi</div>
                                <div class="font-medium text-gray-900" x-text="getLabel(form.profession, options.professions)"></div>
                            </div>
                            <div>
                                <div class="text-gray-500 text-xs uppercase tracking-wide">Domisili</div>
                                <div class="font-medium text-gray-900">
                                    <span x-text="getLabel(form.city, cities)"></span>,
                                    <span x-text="getLabel(form.province, options.provinces)"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. DETAIL PEMBAYARAN (tampil kedua) --}}
                    <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                        <h4 class="font-bold text-gray-700 mb-3 border-b pb-2">Detail Pembayaran</h4>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Event</span>
                                <span class="font-medium text-gray-900 text-right max-w-[65%]" x-text="getLabel(form.event_id, options.events)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Paket</span>
                                <span class="font-medium text-gray-900" x-text="getLabel(form.packet_id, packets)"></span>
                            </div>
                            <div class="flex justify-between pt-3 border-t border-gray-200">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-semibold text-gray-900" x-text="priceDisplay"></span>
                            </div>

                            {{-- Voucher & total: hanya tampil jika harga paket > 0 --}}
                            <template x-if="packetPrice > 0">
                                <div class="space-y-3">

                                    {{-- Input voucher (disembunyikan jika sudah applied) --}}
                                    <div x-show="!voucher.applied" class="pt-1">
                                        <div class="grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-3 items-end">
                                            <x-input-field 
                                                name="voucher_code"
                                                label="Kode Voucher (Opsional)"
                                                type="text"
                                                x-model="voucher.code"
                                                @input="voucher.code = $event.target.value.toUpperCase()"
                                                @keydown.enter.prevent="applyVoucher()"
                                                class="mb-0"
                                            />

                                            <button
                                                type="button"
                                                @click="applyVoucher()"
                                                :disabled="voucher.loading || !voucher.code"
                                                class="w-full lg:w-auto flex items-center justify-center px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary/90 disabled:opacity-50 transition whitespace-nowrap"
                                            >
                                                <span x-show="!voucher.loading">Terapkan</span>
                                                <span x-show="voucher.loading"><i class="fas fa-spinner fa-spin"></i></span>
                                            </button>
                                        </div>
                                        <p x-show="voucher.error" class="mt-1.5 text-xs text-red-600 font-medium flex items-center gap-1">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <span x-text="voucher.error"></span>
                                        </p>
                                    </div>

                                    {{-- Badge voucher aktif --}}
                                    <div x-show="voucher.applied" class="flex items-center justify-between bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center shrink-0">
                                                <i class="fas fa-check text-green-600 text-xs"></i>
                                            </div>
                                            <div>
                                                <div class="text-xs text-green-600 font-semibold uppercase tracking-wide">Voucher Aktif</div>
                                                <div class="text-sm font-bold text-green-900 font-mono" x-text="voucher.applied?.voucher?.code"></div>
                                            </div>
                                        </div>
                                        <button type="button" @click="removeVoucher()" class="text-xs text-red-500 hover:text-red-700 font-semibold flex items-center gap-1 transition">
                                            <i class="fas fa-times"></i> Hapus
                                        </button>
                                    </div>

                                    {{-- Baris diskon --}}
                                    <div x-show="voucher.applied" class="flex justify-between text-green-700">
                                        <span class="flex items-center gap-1">
                                            <i class="fas fa-tag text-xs"></i> Diskon Voucher
                                        </span>
                                        <span class="font-semibold" x-text="'- Rp ' + Number(voucher.applied?.discount ?? 0).toLocaleString('id-ID')"></span>
                                    </div>

                                    {{-- Total bayar --}}
                                    <div class="flex justify-between items-center pt-3 border-t-2 border-gray-300">
                                        <span class="font-bold text-gray-800 text-base">Total Bayar</span>
                                        <span class="font-bold text-secondary text-2xl" x-text="finalPriceDisplay()"></span>
                                    </div>

                                </div>
                            </template>

                            {{-- Gratis --}}
                            <template x-if="packetPrice === 0">
                                <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                                    <span class="font-bold text-gray-800">Total Bayar</span>
                                    <span class="font-bold text-green-600 text-xl">GRATIS</span>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>
            </div>

            {{-- BUTTONS --}}
            <div class="mt-8 flex justify-between items-center">
                <button type="button" @click="prevStep" x-show="currentStep > 1" class="px-6 py-3 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50">
                    Kembali
                </button>

                <div class="ml-auto">
                    <button type="button" @click="nextStep" x-show="currentStep < 4" class="px-6 py-3 rounded-lg bg-primary text-white font-semibold hover:bg-primary/90 cursor-pointer">
                        Lanjut
                    </button>
                    <button type="submit" x-show="currentStep === 4" class="px-6 py-3 rounded-lg bg-secondary text-white font-semibold hover:bg-secondary/90 bg-orange-500 cursor-pointer">
                        Daftar Sekarang
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>
@endif
@endsection
