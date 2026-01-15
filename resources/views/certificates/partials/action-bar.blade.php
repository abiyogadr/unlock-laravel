<div class="w-full max-w-[297mm] bg-white p-5 rounded-xl shadow-md border border-slate-100 flex flex-wrap md:flex-nowrap items-center justify-between gap-5 mt-2 sm:mt-6">
    
    {{-- SECTION: URL --}}
    <div class="flex flex-col gap-2 grow w-full md:w-auto">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">
            Certificate URL
        </label>
        <div class="flex h-11">
            <input type="text" 
                   id="cert-url" 
                   class="grow border border-slate-200 bg-slate-50 px-4 rounded-l-lg text-sm text-slate-600 outline-none focus:border-purple-900 focus:bg-white transition-all" 
                   value="{{ $credential_url }}" 
                   readonly>
            <button id="btnCopy" 
                    class="bg-purple-950 text-white px-6 rounded-r-lg font-semibold text-sm hover:bg-purple-900 flex items-center gap-2 transition-colors active:scale-95">
                <i class="far fa-copy"></i> Copy
            </button>
        </div>
    </div>

    {{-- SECTION: DOWNLOAD --}}
    <div class="flex flex-col gap-2 w-full md:w-48">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">
            Download PNG
        </label>
        <button id="downloadBtn" 
                class="h-11 w-full bg-orange-600 text-white rounded-lg font-bold text-sm hover:bg-orange-700 flex items-center justify-center gap-2 shadow-lg shadow-orange-600/20 transition-all active:scale-95 disabled:bg-slate-300 disabled:shadow-none disabled:cursor-not-allowed">
            <i class="fas fa-download"></i> Download
        </button>
    </div>

</div>
