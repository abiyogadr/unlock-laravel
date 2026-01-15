@extends('layouts.app')

@section('title', 'Daftar Presensi Event')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="mb-8 text-center">
        <h1 class="text-3xl font-extrabold text-gray-900">Presensi Event</h1>
        <p class="text-gray-500 mt-2">Daftar webinar yang telah Anda ikuti dan memerlukan pengisian presensi.</p>
    </div>

    @if($pendingAttendances->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-dashed border-gray-200 shadow-sm">
            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
                <i class="fas fa-calendar-check text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Tidak Ada Presensi Pending</h3>
            <p class="text-sm text-gray-500 max-w-xs mx-auto mt-1">Semua presensi Anda sudah terisi atau belum ada event baru yang diverifikasi.</p>
            <a href="{{ route('myevents') }}" class="mt-6 inline-block text-primary font-bold hover:underline">Lihat Event Saya</a>
        </div>
    @else
        <div class="grid gap-6">
            @foreach($pendingAttendances as $reg)
                <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-all flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-5 w-full sm:w-auto">
                        <div class="w-14 h-14 bg-primary/10 rounded-xl flex items-center justify-center text-primary flex-shrink-0">
                            <i class="fas fa-video text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-lg leading-tight">{{ $reg->event->event_title }}</h3>
                            <h5 class="font-semibold text-gray-900 text-xs leading-tight">{{ $reg->registration_code }}</h5>
                            <div class="flex items-center text-sm text-gray-500 mt-1">
                                <i class="far fa-calendar-alt mr-2"></i>
                                {{ $reg->event->date_start->format('d M Y') }}
                            </div>
                        </div>
                    </div>
                    
                    <a href="{{ route('registration.attendance', $reg->registration_code) }}" 
                       class="w-full sm:w-auto px-6 py-3 bg-primary text-white text-sm font-bold rounded-xl hover:bg-primary/90 transition-all text-center shadow-lg shadow-primary/20">
                        Isi Presensi Sekarang
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
