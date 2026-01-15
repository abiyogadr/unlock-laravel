@extends('layouts.app')

@section('title', 'Presensi - ' . $event->event_title)

@section('content')
<div class="max-w-2xl mx-auto px-4 py-12">
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
        {{-- Header --}}
        <div class="bg-primary p-8 text-center text-white">
            <h2 class="text-2xl font-bold">Presensi Kehadiran</h2>
            <p class="text-primary-foreground/80 mt-2">{{ $event->event_title }}</p>
            <p class="text-primary-foreground/80 mt-2">{{ $registration->registration_code }}</p>
        </div>

        <form action="{{ route('registration.attendance.store', $registration->id) }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
            @csrf

            <div class="space-y-4">
                {{-- Nama Lengkap --}}
                <x-input-field 
                    name="name" 
                    label="Nama Lengkap" 
                    icon="fas fa-user" 
                    :value="old('name', $registration->name)" 
                    readonly
                />

                {{-- Email --}}
                <x-input-field 
                    type="email"
                    name="email" 
                    label="Email" 
                    icon="fas fa-envelope" 
                    :value="old('email', $registration->email)" 
                    readonly
                />

                {{-- Upload Bukti --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700">Upload Bukti Mengikuti Webinar</label>
                    <p class="text-xs text-gray-500 italic mb-2">(Screenshot bukti keikutsertaan Anda dalam mengikuti Webinar)</p>
                    <input type="file" name="attendance_proof" accept="image/*" required
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-all" />
                </div>

                <hr class="border-gray-100 my-6">

                {{-- Penilaian --}}
                <div class="space-y-3">
                    <label class="block text-sm font-bold text-gray-700">Bagaimana penilaian Anda terhadap pelaksanaan webinar?</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach(['Sangat Baik', 'Baik', 'Cukup', 'Kurang'] as $rating)
                        <label class="flex items-center justify-center p-3 border border-primary/50 rounded-xl cursor-pointer hover:bg-gray-50 transition-all">
                            <input type="radio" name="rating" value="{{ $rating }}" class="hidden peer">
                            <span class="text-sm peer-checked:text-primary peer-checked:font-bold text-gray-600">{{ $rating }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Kritik & Saran --}}
                <div class="space-y-3" x-data="{ showOthers: false }">
                    <label class="block text-sm font-bold text-gray-700">Kritik & Saran</label>
                    <div class="grid grid-cols-1 gap-2">
                        @php
                            $suggestions = [
                                'Materi sangat bermanfaat dan jelas',
                                'Waktu pelaksanaan sudah tepat',
                                'Kualitas audio/video perlu ditingkatkan',
                                'Sesi tanya jawab perlu diperbanyak',
                                'Narasumber sangat menguasai materi',
                                'Alur presentasi terlalu cepat/terburu-buru',
                                'Modul/handout materi sangat membantu',
                                'Interaksi antara peserta dan pemateri sudah bagus',
                                'Waktu istirahat/jeda sesi dirasa kurang',
                                'Informasi pelaksanaan webinar diberikan tepat waktu',
                                'Kendala teknis (zoom/link) cukup mengganggu',
                                'Sangat merekomendasikan webinar ini untuk teman/kolega'
                            ];
                        @endphp
                        @foreach($suggestions as $sug)
                        <label class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-gray-600 shadow-sm rounded-xl border border-gray-200 transition hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="suggestions[]" value="{{ $sug }}" class="rounded text-primary focus:ring-primary border-gray-300">
                            <span>{{ $sug }}</span>
                        </label>
                        @endforeach
                        
                        {{-- Opsi Lainnya --}}
                        <label class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-gray-600 shadow-sm rounded-xl border border-gray-200 transition hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" x-model="showOthers" class="rounded text-primary focus:ring-primary border-gray-300">
                            <span>Lainnya...</span>
                        </label>
                    </div>
                    
                    <div x-show="showOthers" x-transition class="mt-2">
                        <textarea name="other_feedback" rows="3" 
                            class="w-full py-2.5 px-4 text-sm font-semibold text-gray-600 shadow-sm rounded-xl border transition focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary hover:bg-gray-50 {{ $errors->has('other_feedback') ? 'border-red-500' : 'border-gray-200' }}" 
                            placeholder="Tuliskan kritik dan saran Anda di sini..."></textarea>
                    </div>
                </div>

                {{-- Saran Tema Selanjutnya --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700">Tema/Judul webinar apa yang Anda sarankan untuk selanjutnya?</label>
                    <textarea name="next_theme_suggestion" rows="3" 
                        class="w-full py-2.5 px-4 text-sm font-semibold text-gray-600 shadow-sm rounded-xl border transition focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary hover:bg-gray-50 {{ $errors->has('next_theme_suggestion') ? 'border-red-500' : 'border-gray-200' }}" 
                        placeholder="Contoh: Digital Marketing, Cyber Security, dll"></textarea>
                </div>
            </div>

            <button type="submit" class="w-full py-4 bg-primary text-white font-bold rounded-2xl shadow-lg shadow-primary/30 hover:bg-primary/90 transition-all flex items-center justify-center gap-2 cursor-pointer">
                <i class="fas fa-check-circle"></i>
                Kirim Presensi
            </button>
        </form>
    </div>
</div>

<style>
    input[type="radio"]:checked + span {
        @apply text-primary font-bold;
    }
    label:has(input[type="radio"]:checked) {
        @apply border-primary bg-primary/5;
    }
    /* Style tambahan untuk checkbox yang terpilih agar border berubah warna */
    label:has(input[type="checkbox"]:checked) {
        @apply border-primary bg-primary/5;
    }
</style>
@endsection
