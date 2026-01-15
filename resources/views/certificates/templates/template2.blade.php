@extends('layouts.certificate')

@section('certificate_content')
    <!-- 2. BACKGROUND DESIGN -->
    <div class="absolute inset-0 z-0 opacity-[0.05] pointer-events-none" 
         style="background-image: radial-gradient(#40224f 1px, transparent 1px); background-size: 20px 20px;">
    </div>
    
    <div class="absolute top-0 left-0 w-[300px] h-[300px] bg-[#40224f]/[0.03] [clip-path:circle(50%_at_0_0)] z-0"></div>
    <div class="absolute top-0 left-0 w-[150px] h-[150px] bg-[#ff6407]/[0.05] [clip-path:polygon(0_0,100%_0,0_100%)] z-0"></div>
    <div class="absolute bottom-0 right-0 w-[400px] h-[400px] bg-[#ff6407]/[0.03] [clip-path:circle(50%_at_100%_100%)] z-0"></div>
    <div class="absolute bottom-0 right-0 w-[200px] h-[200px] bg-[#40224f]/[0.05] [clip-path:polygon(100%_100%,100%_0,0_100%)] z-0"></div>

    <!-- 3. MAIN CONTENT -->
    <div class="relative z-10 w-full h-full px-[100px] py-[50px] flex flex-col text-center box-border font-['Roboto']">
        
        <div class="h-[55px] mb-[30px] flex justify-center">
            <img src="{{ asset('assets/images/logo-unlock.png') }}" class="h-full w-auto object-contain" alt="UNLOCK">
        </div>

        <div class="mb-[25px]">
            <h1 class="text-[28pt] font-[900] text-[#40224f] uppercase tracking-[6px] leading-tight mb-2">Certificate</h1>
            <p class="text-[12pt] text-[#ff6407] font-bold tracking-[4px] uppercase opacity-80">Of Appreciation</p>
        </div>

        <div class="mb-[20px] flex flex-col items-center">
            <span class="text-[10pt] text-slate-400 uppercase tracking-[2px] mb-4 font-sans italic">This certificate is proudly presented to</span>
            <div class="text-[34pt] text-[#40224f] leading-none font-bold border-b-4 border-[#ff6407] inline-block px-12 pb-2" style="text-shadow: 2px 2px 4px rgba(64,34,79,0.05);" id="participant_name">
                {{ $participant_name }}
            </div>
        </div>

        <div class="grow flex flex-col justify-center">
            <p class="text-[11pt] text-slate-500 mb-2">For successfully participating in the webinar event</p>
            <h2 class="text-[18pt] font-[800] text-slate-800 uppercase tracking-wide leading-tight px-10">{{ $main_title }}</h2>
            <p class="text-[11pt] text-[#ff6407] italic mt-1">{{ $sub_title }}</p>
            
            <div class="mt-8 grid grid-cols-2 gap-10 max-w-[640px] mx-auto w-full">
                <div class="text-right border-r border-slate-100 pr-10">
                    <p class="text-[8pt] text-slate-400 uppercase tracking-widest">Narasumber</p>
                    <p class="text-[13pt] font-bold text-[#40224f]">{{ $speaker_name }}</p>
                </div>
                <div class="text-left">
                    <p class="text-[8pt] text-slate-400 uppercase tracking-widest">Waktu & Tempat</p>
                    <p class="text-[13pt] font-bold text-slate-700 leading-tight">{{ $event_date }}</p>
                    {{-- PENAMBAHAN DATE EXTRA DI SINI --}}
                    @if($date_extra)
                        <p class="text-[9pt] text-[#ff6407] font-medium mt-1 tracking-tighter italic border-t border-slate-50 pt-1">
                            {{ $date_extra }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- FOOTER: QR KIRI, TTD KANAN -->
        <div class="flex justify-between items-end w-full px-20 mt-6">
            <div class="flex flex-col items-start gap-2">
                <div id="qrcode-container" class="w-[85px] h-[85px] bg-white p-1 border border-slate-100 shadow-sm" data-qr-text="{{ $credential_url }}"></div>
                <div class="text-[7pt] text-slate-400 text-left leading-tight">
                    ID: <span class="font-bold text-slate-500">{{ $certificate_id }}</span><br>
                    Verify at: {{ $verify_url }}
                </div>
            </div>

           @if($has_sign)
            <div class="text-center w-[220px]">
                @if($sign_path)
                    <img src="{{ asset('storage/' . $sign_path) }}" class="h-[75px] mb-1 mx-auto object-contain block">
                @else
                    <div class="h-[75px] mb-1"></div>
                @endif
                <div class="h-[2px] bg-gradient-to-r from-[#40224f] to-[#ff6407] mb-2 w-full"></div>
                <span class="font-bold text-[11pt] text-[#40224f] block leading-none">{{ $speaker_name }}</span>
                <span class="text-[8pt] text-slate-500 uppercase tracking-tighter">{{ $speaker_titles ?: 'Pembicara' }}</span>
            </div>
            @endif
        </div>
    </div>
@endsection
