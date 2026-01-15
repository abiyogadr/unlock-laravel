@extends('layouts.app')

@section('title', 'Unlock - Status Pendaftaran')

@section('content')
<div class="max-w-3xl mx-auto my-16 px-4">
  <div class="bg-white rounded-2xl p-8 sm:p-10 shadow-xl border border-gray-100 text-center">

    {{-- ICON --}}
    @if($status === 'success')
      <div class="mx-auto mb-6 w-20 h-20 flex items-center justify-center rounded-full bg-green-100 text-green-600">
        <i class="fas fa-check-circle text-4xl"></i>
      </div>
    @elseif($status === 'pending')
      <div class="mx-auto mb-6 w-20 h-20 flex items-center justify-center rounded-full bg-yellow-100 text-yellow-600">
        <i class="fas fa-clock text-4xl"></i>
      </div>
    @else
      <div class="mx-auto mb-6 w-20 h-20 flex items-center justify-center rounded-full bg-red-100 text-red-600">
        <i class="fas fa-times-circle text-4xl"></i>
      </div>
    @endif

    {{-- INFORMASI PENDAFTARAN --}}
    @if($registration)
      <div class="rounded-lg p-4 mb-6 
        @if($status === 'success') bg-green-50 border border-green-200 
        @elseif($status === 'pending') bg-yellow-50 border border-yellow-200 
        @else bg-red-50 border border-red-200 
        @endif">
        <div class="text-center space-y-2">
          <div>
            <p class="text-sm font-semibold 
              @if($status === 'success') text-green-800 
              @elseif($status === 'pending') text-yellow-700 
              @else text-red-700 @endif
            ">Kode Pendaftaran</p>
            <code class="text-lg font-bold bg-white px-3 py-1 rounded border 
              @if($status === 'success') border-green-300 text-green-900
              @elseif($status === 'pending') border-yellow-300 text-yellow-900
              @else border-red-300 text-red-900 @endif">
              {{ $registration->registration_code }}
            </code>
          </div>

          <div>
            <p class="text-sm font-semibold 
              @if($status === 'success') text-green-800 
              @elseif($status === 'pending') text-yellow-700 
              @else text-red-700 @endif">Event</p>
            <p class="text-base font-bold 
              @if($status === 'success') text-green-900 
              @elseif($status === 'pending') text-yellow-900 
              @else text-red-900 @endif">
              {{ $registration->event_name }}
              <span class="block text-xs opacity-80 font-normal">
                ({{ $registration->event_code }})
              </span>
            </p>
          </div>
        </div>
      </div>
    @endif

    {{-- TITLE --}}
    <h1 class="text-2xl sm:text-3xl font-bold mb-4 
      @if($status === 'success') text-green-600 
      @elseif($status === 'pending') text-yellow-600 
      @else text-red-600 @endif">
      @if($status === 'success')
        Pembayaran Berhasil 🎉
      @elseif($status === 'pending')
        Menunggu Pembayaran ⏳
      @else
        Pembayaran Gagal ❌
      @endif
    </h1>

    {{-- MESSAGE --}}
    <p class="text-gray-700 text-sm sm:text-base mb-8 leading-relaxed">
      @if($status === 'success')
        Terima kasih telah menyelesaikan pembayaran untuk event <strong>{{ $registration->event_name }}</strong>.
        Pendaftaran Anda telah kami konfirmasi. Detail pendaftaran telah kami kirimkan ke email Anda.
        <br class="hidden sm:block">
        Silakan hubungi admin untuk informasi lebih lanjut.
      @elseif($status === 'pending')
        Pembayaran Anda belum kami terima untuk event <strong>{{ $registration->event_name }}</strong>.
        <br class="hidden sm:block">
        Silakan lanjutkan proses pembayaran sebelum batas waktu berakhir.
      @else
        Maaf, pembayaran Anda tidak berhasil diproses.
        <br class="hidden sm:block">
        Silakan coba kembali atau hubungi admin untuk bantuan.
      @endif
    </p>

    {{-- ACTION BUTTON --}}
    <div class="flex flex-col sm:flex-row justify-center gap-4">
      @if($status === 'success')
        <a href="https://wa.me/6285176767623"
           target="_blank"
           class="inline-flex items-center justify-center px-6 py-3 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700 transition-all duration-300">
          <i class="fab fa-whatsapp mr-2 text-lg"></i>
          Chat Admin WhatsApp
        </a>
        <a href="{{ route('myevents') }}"
           class="inline-flex items-center justify-center px-6 py-3 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-all duration-300">
          Event Saya
        </a>
      @elseif($status === 'pending')
        <a href="{{ route('registration.payment', $registration) }}"
           class="inline-flex items-center justify-center px-6 py-3 rounded-lg bg-yellow-500 text-white font-semibold hover:bg-yellow-600 transition-all duration-300">
          <i class="fas fa-credit-card mr-2"></i>
          Lanjutkan Pembayaran
        </a>
        <a href="{{ route('myevents') }}"
           class="inline-flex items-center justify-center px-6 py-3 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-all duration-300">
          Event Saya
        </a>
      @else
        <a href="{{ route('registration.create') }}"
           class="inline-flex items-center justify-center px-6 py-3 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition-all duration-300">
          <i class="fas fa-redo mr-2"></i>
          Coba Lagi
        </a>
      @endif
    </div>
  </div>
</div>
@endsection
