@extends('layouts.app')

@section('title', 'Unlock - FAQ')

@section('content')

    {{-- Hero FAQ Section --}}
    <section class="w-full bg-gradient-to-br from-primary to-purple-800 text-white py-16">
        <div class="container mx-auto px-4 text-center max-w-7xl">
            <h1 class="text-3xl md:text-4xl font-bold mb-4">Pusat Bantuan</h1>
            <p class="text-lg opacity-90 max-w-7xl mx-auto">
                Punya pertanyaan seputar Unlock Indonesia? Temukan jawabannya di sini.
            </p>
        </div>
    </section>

    {{-- FAQ Accordion Section --}}
    <section class="w-full py-16 bg-white" x-data="{ active: null }">
        <div class="container mx-auto px-4 max-w-7xl">
            
            <div class="space-y-4" x-data="{ active: null }">
                @foreach(config('faq') as $index => $item)
                    <div class="border border-gray-200 rounded-2xl overflow-hidden bg-white transition-all duration-300" 
                        :class="active === {{ $index }} ? 'ring-2 ring-primary/20 border-primary shadow-md' : ''">
                        
                        {{-- Header/Tombol --}}
                        <button @click="active = active === {{ $index }} ? null : {{ $index }}" 
                                class="w-full flex justify-between items-center p-5 text-left transition-colors duration-300">
                            <span class="font-semibold text-gray-800" 
                                :class="active === {{ $index }} ? 'text-primary' : ''">
                                {{ $item['question'] }}
                            </span>
                            
                            {{-- Ikon Panah dengan Transisi Putar --}}
                            <div class="w-6 h-6 flex items-center justify-center rounded-full bg-gray-50 transition-transform duration-500"
                                :class="active === {{ $index }} ? 'rotate-180 bg-primary/10' : ''">
                                <i class="fas fa-chevron-down text-[10px] transition-colors"
                                :class="active === {{ $index }} ? 'text-primary' : 'text-gray-400'"></i>
                            </div>
                        </button>

                        {{-- Konten dengan Transisi x-collapse --}}
                        <div x-show="active === {{ $index }}" 
                            x-collapse.duration.300ms
                            x-cloak>
                            <div class="p-5 pt-0 text-gray-600 text-sm leading-relaxed border-t border-gray-50 mt-2">
                                {{ $item['answer'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Support CTA --}}
            <div class="mt-12 p-8 bg-gray-50 rounded-3xl border border-dashed border-gray-300 text-center">
                <h3 class="font-bold text-gray-800 mb-2">Masih punya pertanyaan lain?</h3>
                <p class="text-sm text-gray-500 mb-6">Jangan ragu untuk menghubungi tim support kami.</p>
                <a href="https://wa.me/6285176767623" target="_blank" 
                   class="bg-primary text-white px-6 py-3 rounded-xl font-bold text-sm hover:bg-secondary transition-all shadow-md inline-flex items-center">
                    <i class="fab fa-whatsapp mr-2"></i> Tanya Admin Sekarang
                </a>
            </div>

        </div>
    </section>

@endsection

@push('scripts')
    {{-- Pastikan Alpine.js terpasang untuk fitur accordion --}}
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
