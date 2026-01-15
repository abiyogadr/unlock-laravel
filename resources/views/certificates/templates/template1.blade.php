@extends('layouts.certificate')

@section('certificate_content')
    <!-- 2. BACKGROUND DESIGN -->
    <div class="absolute inset-0 z-0 opacity-[0.07] pointer-events-none" 
         style="background-image: radial-gradient(#40224f 0.8px, transparent 0.8px); background-size: 25px 25px;">
    </div>
    
    <div class="absolute w-[150%] h-full bg-[#40224f]/[0.03] rounded-[40%] -top-[40%] -left-[20%] -rotate-[15deg] z-0"></div>
    <div class="absolute w-[120%] h-[80%] bg-[#ff6407]/[0.03] rounded-[45%] -bottom-[30%] -right-[10%] rotate-[10deg] z-0"></div>

    <div class="absolute z-[2] w-[250px] h-[60px] bg-gradient-to-r from-[#ff6407] to-[#ff9045] top-[40px] -left-[60px] -rotate-45 rounded-[50px] shadow-[4px_4px_20px_rgba(255,100,7,0.3)]"></div>
    <div class="absolute z-[2] w-[150px] h-5 bg-[#40224f] top-[20px] left-[40px] -rotate-45 rounded-[20px]"></div>
    <div class="absolute z-[2] w-10 h-10 border-[4px] border-[#40224f] opacity-20 rounded-full top-[110px] left-[10px]"></div>
    
    <div class="absolute z-[2] w-[300px] h-20 bg-gradient-to-r from-[#40224f] to-[#6a3085] bottom-[40px] -right-[80px] -rotate-45 rounded-[60px] shadow-[4px_4px_20px_rgba(64,34,79,0.3)]"></div>
    <div class="absolute z-[2] w-[180px] h-[25px] bg-[#ff6407] bottom-[140px] right-[10px] -rotate-45 rounded-[20px]"></div>
    <div class="absolute z-[2] bottom-[50px] right-[250px] flex gap-[10px]">
        <div class="w-2.5 h-2.5 bg-[#ff6407] opacity-60 rounded-full"></div>
        <div class="w-2.5 h-2.5 bg-[#ff6407] opacity-60 rounded-full"></div>
        <div class="w-2.5 h-2.5 bg-[#ff6407] opacity-60 rounded-full"></div>
    </div>

    <!-- 3. KONTEN UTAMA -->
    <div class="relative z-10 w-full h-full px-[60px] py-[30px] flex flex-col text-center box-border font-['Roboto'] text-[#333]">
        
        <div class="h-[50px] mb-[10px] mt-[20px] flex justify-center items-end">
            <img src="{{ asset('assets/images/logo-unlock.png') }}" class="h-[150%] w-auto object-contain block" alt="UNLOCK">
        </div>

        <div class="mb-[15px] border-b-[2px] border-[#f0f0f0] pb-[10px]">
            <h1 class="text-[24pt] font-[900] text-[#40224f] uppercase tracking-[3px] mt-[30px] leading-normal font-sans">
                Certificate of Appreciation
            </h1>
            <div class="text-[10pt] text-[#666] uppercase tracking-[2px] mt-[5px] mb-[5px]">Presented to:</div>
            <div class="text-[28pt] text-[#ff6407] leading-[1.2] font-['Roboto'] italic" id="participant_name">
                {{ $participant_name }}
            </div>
        </div>

        <div class="mt-[5px] grow flex flex-col justify-center">
            <div class="text-[10pt] font-[700] text-white bg-[#40224f] px-[15px] py-[4px] rounded-[20px] inline-block mb-[5px] uppercase mx-auto w-fit">
                WEBINAR UNLOCK
            </div>
            <div class="text-[16pt] font-[800] text-[#333] leading-[1.1] my-[5px] uppercase">
                {{ $main_title }}
            </div>
            <div class="text-[11pt] text-[#666] italic font-[400] mb-[10px]">
                {{ $sub_title }}
            </div>
            
            <div class="mb-[10px]">
                <div class="text-[10pt] text-[#555] mb-[2px]">Dengan pemateri:</div>
                <div class="text-[18pt] font-[700] text-[#40224f] leading-none">{{ $speaker_name }}</div>
                <div class="text-[8pt] text-[#666] max-w-[90%] mx-auto mt-[2px] leading-[1.3] uppercase">{{ $speaker_titles }}</div>
            </div>

            <div class="text-[10pt] text-[#444] mt-[10px] bg-[#ff6407]/[0.05] px-[20px] py-[8px] rounded-[8px] inline-block mx-auto w-fit">
                Pada: <strong>{{ $event_date }}</strong> <br>
                @if($date_extra)
                    <span class="text-[9pt] text-[#ff6407] italic">{{ $date_extra }}</span>
                @endif
            </div>
        </div>

        {{-- FOOTER AREA: Pakai justify-between agar posisi otomatis --}}
        <div class="flex justify-between items-end w-full px-30 mt-6 pb-10">
            {{-- QR CODE (KIRI) --}}
            <div class="flex flex-col items-center">
                <div id="qrcode-container" class="w-[85px] h-[85px] bg-white p-1 border border-slate-100 shadow-sm" data-qr-text="{{ $credential_url }}"></div>
                <div class="text-[7pt] text-slate-400 text-center leading-tight mt-2">
                    ID: <span class="font-bold text-slate-500">{{ $certificate_id }}</span><br>
                    Verify at: {{ parse_url($verify_url, PHP_URL_HOST) }}
                </div>
            </div>

            {{-- TANDA TANGAN (KANAN): Hanya muncul jika $has_sign true --}}
            @if($has_sign)
            <div class="text-center w-[220px]">
                <div class="h-[75px] mb-1 flex items-center justify-center">
                    @if($sign_path)
                        <img src="{{ asset('storage/' . $sign_path) }}" class="sig-img max-h-full mx-auto object-contain block">
                    @else
                        {{-- Spacer saat di preview admin tapi file belum diupload --}}
                        <div class="h-[75px]"></div>
                    @endif
                </div>
                <div class="h-[2px] bg-gradient-to-r from-[#40224f] to-[#ff6407] mb-2 w-full"></div>
                <span class="font-bold text-[11pt] text-[#40224f] block leading-none">{{ $speaker_name }}</span>
                <span class="text-[8pt] text-slate-500 uppercase tracking-tighter">{{ $speaker_titles ?: 'Pembicara' }}</span>
            </div>
            @endif
        </div>
    </div>
@endsection
