@extends('layouts.app')

@section('title', 'Unlock - Pembayaran')

@section('content')
<div class="max-w-3xl mx-auto my-16 px-4">
    <div class="bg-white rounded-2xl p-4 sm:p-10 shadow-xl border border-gray-100">
        
        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="mx-auto mb-4 w-16 h-16 flex items-center justify-center rounded-full bg-yellow-100 text-yellow-600">
                <i class="fas fa-money-bill-wave text-3xl"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">
                Pembayaran Event
            </h1>
        </div>

        {{-- Payment Info --}}
        <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-2 sm:p-6 mb-8">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                Informasi Pembayaran
            </h3>

            {{-- Registration Code --}}
            <div class="bg-white rounded-xl p-4 mb-3 border border-yellow-100">
                <div class="flex justify-between items-center">
                    <span class="text-xs sm:text-sm font-semibold text-gray-600">Kode Registrasi:</span>
                    <span class="font-mono text-xs sm:text-sm text-gray-800 bg-gray-100 px-3 py-1 rounded-md">
                        {{ $registration->registration_code }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-1 text-right">
                    Gunakan kode ini sebagai referensi pembayaran Anda
                </p>
            </div>

            {{-- Total Price --}}
            <div class="bg-white rounded-xl p-4 mb-4 border border-yellow-100">
                <div class="flex justify-between items-center">
                    <span class="text-xs sm:text-sm font-semibold text-gray-600">Total Pembayaran:</span>
                    <span class="text-xl sm:text-2xl font-bold text-orange-600">
                        Rp. {{ number_format($packet->price + ($event->payment_unique_code ?? 0), 2, ',', '.') }}
                    </span>
                </div>
                @if($event->payment_unique_code)
                    <p class="text-xs text-gray-500 mt-1 text-right">
                        (Harga + Kode Unik: {{ $event->payment_unique_code }})
                    </p>
                @endif
            </div>

            {{-- Payment Description --}}
            <div class="text-sm text-gray-700 leading-relaxed mb-3">
                <p>Anda akan dialihkan ke <strong>Payment Gateway</strong> untuk menyelesaikan pembayaran.</p>
                <p class="text-xs text-gray-500 mt-1">Metode yang tersedia: Transfer Bank, QRIS, Gopay, OVO, dll.</p>
            </div>
        </div>

        {{-- Tombol bayar Midtrans --}}
        <div class="text-center mt-6">
            <button id="pay-button"
                class="inline-flex items-center justify-center gap-2
                    bg-secondary hover:bg-orange-600 text-white 
                    px-8 py-3.5 rounded-xl font-semibold text-base
                    transition-all duration-300 shadow-lg hover:shadow-xl
                    transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-orange-300 cursor-pointer">
                <i class="fas fa-credit-card"></i>
                Bayar Sekarang
            </button>

            <p class="text-xs text-gray-500 mt-4">
                Setelah berhasil, status registrasi Anda akan diperbarui otomatis.
            </p>
        </div>
    </div>
</div>

{{-- Midtrans Script --}}
<script 
    type="text/javascript"
    src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
    data-client-key="{{ config('midtrans.client_key') }}">
</script>

<script type="text/javascript">
document.getElementById('pay-button').onclick = function () {
  window.snap.pay('{{ $snapToken }}', {
    onClose: function() {
      window.location.href = "{{ route('registration.status', ['status' => 'pending']) }}" +
                             "?order_id={{ $registration->registration_code }}";
    }
  });
};
</script>
@endsection
