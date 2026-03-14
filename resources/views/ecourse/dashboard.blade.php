@extends('ecourse.layouts.app')

@section('title', 'Dashboard - Unlock E-Course')
@section('page_title', 'Dashboard Overview')

@section('breadcrumb')
    <li><i class="fas fa-chevron-right text-[8px] mx-1 opacity-50"></i></li>
    <li class="text-primary uppercase tracking-wider">Dashboard</li>
@endsection

@section('content')
    <div class="space-y-8">
        {{-- Welcome Message --}}
        <div class="bg-gradient-to-r from-primary to-purple-700 rounded-3xl p-8 text-white shadow-lg relative overflow-hidden">
            <div class="relative z-10">
                <h1 class="text-2xl md:text-3xl font-bold mb-2">Halo, {{ Auth::user()->name }}! 👋</h1>
                <p class="text-white/80 max-w-md">Senang melihatmu kembali. Lanjutkan belajarmu dan tingkatkan skill profesionalmu hari ini.</p>
            </div>
            <i class="fas fa-graduation-cap absolute -right-10 -bottom-10 text-[200px] text-white/10 rotate-12"></i>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-6">
            <!-- Total E-Course -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 transition group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Kursus Saya</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['total_owned'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center transition">
                        <i class="fas fa-book text-primary text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- On Progress -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 transition group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Berjalan</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['ongoing'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center transition">
                        <i class="fas fa-play-circle text-amber-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Completed -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 transition group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Selesai</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['completed'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center transition">
                        <i class="fas fa-check-double text-emerald-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Certificates -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 transition group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Sertifikat</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['certificates'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center transition">
                        <i class="fas fa-award text-purple-500 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest/My Courses -->
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Lanjutkan Belajar</h2>
                    <p class="text-sm text-gray-500">Akses cepat ke kursus terakhir yang kamu buka</p>
                </div>
                <a href="{{ route('ecourse.my-journey') }}" class="px-6 py-3 bg-primary text-white rounded-xl font-semibold text-sm hover:bg-purple-800 transition inline-flex items-center">
                    Lihat My Journey <i class="fas fa-arrow-right ml-2 text-xs"></i>
                </a>
            </div>

            @if($latestCourses->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($latestCourses as $course)
                        <div class="flex flex-col">
                            {{-- Reusable Component with extra progress bar for dashboard --}}
                            <x-ecourse-card :course="$course->course" :progress="$course->progress" />
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Empty State --}}
                <div class="bg-white rounded-3xl p-12 text-center border border-dashed border-gray-200">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-layer-group text-gray-300 text-2xl"></i>
                    </div>
                    <h3 class="text-gray-800 font-bold">Belum ada kursus</h3>
                    <p class="text-gray-500 text-sm mb-6">Kamu belum memiliki kursus. Yuk, mulai cari materi favoritmu!</p>
                    <a href="{{ route('ecourse.catalog.index') }}" class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-xl font-bold text-sm hover:bg-purple-800 transition">
                        Buka Katalog
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
