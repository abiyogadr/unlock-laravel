@extends('layouts.app')

@section('title', '410 - Link Kedaluwarsa')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4">
    <div class="max-w-lg w-full text-center bg-white rounded-2xl shadow-xl border border-gray-100 p-8 sm:p-10">

        {{-- Icon --}}
        <div class="mx-auto mb-6 w-20 h-20 flex items-center justify-center rounded-full
                    bg-gradient-to-br from-amber-400 to-orange-500 shadow-lg">
            <i class="fas fa-clock text-white text-3xl"></i>
        </div>

        {{-- Code --}}
        <h1 class="text-7xl sm:text-8xl font-extrabold text-amber-500 tracking-tight mb-4">
            410
        </h1>

        {{-- Title --}}
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-3">
            Link Kedaluwarsa
        </h2>

        {{-- Description --}}
        <p class="text-gray-600 text-sm sm:text-base mb-8 leading-relaxed">
            Maaf, halaman yang anda tuju sudah tidak dapat diakses.
        </p>

        {{-- Action --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-center">

            <a href="{{ url('/') }}"
               class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg
                      bg-primary text-white font-semibold
                      hover:bg-purple-900 transition-all duration-300">
                <i class="fas fa-house"></i>
                Beranda
            </a>
        </div>
    </div>
</div>
@endsection