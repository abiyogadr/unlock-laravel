@extends('layouts.app')

@section('title', '413 - File Terlalu Besar')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4">
    <div class="max-w-lg w-full text-center bg-white rounded-2xl shadow-xl border border-gray-100 p-8 sm:p-10">

        {{-- Icon --}}
        <div class="mx-auto mb-6 w-20 h-20 flex items-center justify-center rounded-full
                    bg-gradient-to-br from-orange-500 to-red-600 shadow-lg">
            <i class="fas fa-file-circle-xmark text-white text-3xl"></i>
        </div>

        {{-- Code --}}
        <h1 class="text-7xl sm:text-8xl font-extrabold text-orange-500 tracking-tight mb-4">
            413
        </h1>

        {{-- Title --}}
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-3">
            File Terlalu Besar
        </h2>

        {{-- Description --}}
        <p class="text-gray-600 text-sm sm:text-base mb-8 leading-relaxed">
            Ukuran file yang Anda upload melebihi batas maksimum yang diperbolehkan oleh sistem.
            Silakan perkecil ukuran file atau gunakan file dengan resolusi lebih rendah.
        </p>

        {{-- Action --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <button onclick="history.back()"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg
                       border border-gray-300 text-gray-700 font-semibold
                       hover:bg-gray-50 transition-all duration-300">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </button>

            <a href="{{ url('/') }}"
               class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg
                      bg-orange-500 text-white font-semibold
                      hover:bg-orange-600 transition-all duration-300">
                <i class="fas fa-house"></i>
                Beranda
            </a>
        </div>
    </div>
</div>
@endsection
