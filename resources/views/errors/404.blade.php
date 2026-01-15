@extends('layouts.app')

@section('title', '404 - Halaman Tidak Ditemukan')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4">
    <div class="max-w-lg w-full text-center bg-white rounded-2xl shadow-xl border border-gray-100 p-8 sm:p-10">

        {{-- Icon --}}
        <div class="mx-auto mb-6 w-20 h-20 flex items-center justify-center rounded-full
                    bg-gradient-to-br from-primary to-purple-700 shadow-lg">
            <i class="fas fa-triangle-exclamation text-white text-3xl"></i>
        </div>

        {{-- Code --}}
        <h1 class="text-7xl sm:text-8xl font-extrabold text-primary tracking-tight mb-4">
            404
        </h1>

        {{-- Title --}}
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-3">
            Halaman Tidak Ditemukan
        </h2>

        {{-- Description --}}
        <p class="text-gray-600 text-sm sm:text-base mb-8 leading-relaxed">
            Maaf, halaman yang Anda cari tidak tersedia atau sudah dipindahkan.
            Silakan kembali ke beranda atau periksa kembali URL yang Anda masukkan.
        </p>

        {{-- Action --}}
        <a href="{{ url('/') }}"
           class="inline-flex items-center gap-2 px-6 py-3 rounded-lg
                  bg-secondary text-white font-semibold
                  hover:bg-purple-700 transition-all duration-300">
            <i class="fas fa-arrow-left"></i>
            Kembali ke Beranda
        </a>
    </div>
</div>
@endsection
