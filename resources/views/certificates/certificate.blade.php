@extends('layouts.app')

@section('title', 'Unlock - Verifikasi Sertifikat')

@section('content')
<div class="max-w-4xl mx-auto my-8 px-8">
    <!-- Motivation Section -->
    <div class="bg-white rounded-xl p-12 text-center mb-8 shadow-lg border-2 border-gray-100 animate-fade-in">
        <p class="text-xl font-normal text-primary mb-4">
            "Setiap langkah kecil menuju pengetahuan adalah kunci untuk membuka potensi tak terbatas Anda."
        </p>
        <p class="text-base text-gray-500">
            Terima kasih telah bergabung dalam webinar kami. Semangat baru Anda adalah awal dari perubahan besar.
        </p>
    </div>

    <div class="bg-white rounded-xl p-10 shadow-lg border-2 border-gray-100 animate-fade-in">
        <h2 class="text-2xl font-semibold text-primary mb-6 text-center">Verifikasi ID Sertifikat</h2>
        
        @if ($errors->any())
            <div class="bg-red-100 border-2 border-red-500 text-red-700 p-4 rounded-lg mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('certificate.verify') }}" method="POST">
            @csrf
            
            <div class="mb-6">
                <label class="block mb-2 font-medium text-gray-700" for="certificate_id">ID Sertifikat</label>
                <input type="text" id="certificate_id" name="certificate_id" 
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg font-poppins text-base 
                           transition-all duration-300 bg-gray-50 
                           focus:outline-none focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10"
                    placeholder="Contoh: UNL-XXXXX" required autofocus>
            </div>
            
            <button type="submit" 
                    class="w-full py-3 bg-secondary text-white font-poppins text-base font-semibold 
                           rounded-lg cursor-pointer transition-all duration-300 mt-4 
                           hover:bg-orange-600 hover:-translate-y-0.5 hover:shadow-orange-500/30">
                Verifikasi & Lihat Sertifikat
            </button>
        </form>
    </div>
</div>
@endsection
