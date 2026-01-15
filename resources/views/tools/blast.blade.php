@extends('layouts.tools')
@section('page-title', 'Email Blast')

@section('tool-content')
<div x-data="blastManager()" x-init="init()">
    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 mb-2 bg-slate-100 p-1.5 rounded-2xl w-fit">
        <button @click="tab = 'editor'" :class="tab === 'editor' ? 'bg-white shadow-sm text-purple-700' : 'text-slate-500 hover:text-slate-700'" class="px-6 py-2.5 rounded-xl font-bold text-sm transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Campaign Editor
        </button>
        <button @click="switchTab('history')" :class="tab === 'history' ? 'bg-white shadow-sm text-purple-700' : 'text-slate-500 hover:text-slate-700'" class="px-6 py-2.5 rounded-xl font-bold text-sm transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Blast History
        </button>
    </div>

    <!-- PROGRESS BAR TIPIS (Hanya muncul saat kirim) -->
    <template x-if="batchId">
        <div class="mb-6 bg-white p-3 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 animate-in fade-in slide-in-from-top-2">
            <div class="flex-1">
                <div class="flex justify-between mb-1 text-xs font-bold text-slate-500 uppercase tracking-wide">
                    <span>Mengirim <span x-text="processedJobs"></span> dari <span x-text="totalJobs"></span></span>
                    <span x-text="progress + '%'"></span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-500 to-orange-500 h-2 rounded-full transition-all duration-500" :style="'width: ' + progress + '%'"></div>
                </div>
            </div>
            <button @click="stopBatch()" class="bg-red-50 text-red-600 hover:bg-red-100 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Batalkan
            </button>
        </div>
    </template>

    <!-- TAB 1: EDITOR -->
    <div x-show="tab === 'editor'" x-transition class="space-y-8">
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <!-- Left: Inputs -->
            <div class="space-y-6">
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200">
                    <h3 class="text-slate-900 font-black text-lg mb-4">Konfigurasi Campaign</h3>
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Subjek Email</label>
                            <input type="text" x-model="subject" class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 focus:ring-2 focus:ring-purple-500 font-bold" placeholder="Input subjek email...">
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Upload CSV (nama,email)</label>
                            <input type="file" @change="handleFileUpload" class="w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-6 file:rounded-2xl file:border-0 file:bg-purple-100 file:text-purple-700 file:font-bold hover:file:bg-purple-200 transition-all cursor-pointer">
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Konten Pesan</label>
                            <div class="border border-slate-100 rounded-2xl overflow-hidden">
                                <div id="editor" style="height: 300px;"></div>
                            </div>
                        </div>

                        <button @click="startBlast" :disabled="isSending" class="w-full py-4 bg-orange-500 text-white rounded-2xl font-black shadow-lg shadow-orange-200 hover:bg-orange-600 transition-all flex items-center justify-center gap-3">
                            <span x-show="!isSending">🚀 Mulai Kirim Sekarang</span>
                            <span x-show="isSending" class="animate-pulse">Sedang Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right: Real-time Template Preview + Recipients Modal -->
            <div class="sticky top-28 space-y-6">
                <!-- Recipients Counter -->
                <div class="bg-gradient-to-r from-purple-500 to-orange-500 p-4 rounded-2xl text-white shadow-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-bold opacity-90">Penerima Siap Kirim</p>
                            <p class="text-2xl font-black" x-text="recipients.length"></p>
                        </div>
                        <button @click="showRecipientsModal = true" class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-xl text-xs font-bold hover:bg-white/30 transition-all">
                            📋 Lihat Daftar
                        </button>
                    </div>
                </div>

                <!-- Preview -->
                <div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Live Preview (Desktop)</p>
                    <div class="bg-slate-200 p-4 rounded-[2rem] shadow-inner overflow-hidden border-8 border-slate-800">
                        <div class="bg-white h-[500px] overflow-y-auto rounded-xl custom-scrollbar shadow-lg p-6">
                            <div class="preview-content text-slate-800" x-html="previewContent()"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL LIST PENERIMA -->
    <div x-show="showRecipientsModal" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="fixed inset-0 z-50 overflow-y-auto" 
        style="background-color: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px);"
        @click.away="showRecipientsModal = false"
        x-cloak>
    
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-orange-500 p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-black">📋 Daftar Penerima</h3>
                            <p class="opacity-90" x-text="recipients.length + ' orang'"></p>
                        </div>
                        <button @click="showRecipientsModal = false" class="w-10 h-10 bg-white/20 rounded-2xl flex items-center justify-center hover:bg-white/30 transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                <div class="max-h-[400px] overflow-y-auto divide-y divide-slate-100">
                    <template x-for="(recipient, index) in recipients" :key="index">
                        <div class="p-6 hover:bg-slate-50 transition-colors group flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-orange-400 rounded-2xl flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                    <span x-text="recipient.name.charAt(0).toUpperCase()"></span>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 text-lg" x-text="recipient.name"></p>
                                    <p class="text-slate-500 text-sm font-mono" x-text="recipient.email"></p>
                                </div>
                            </div>
                            <span class="opacity-0 group-hover:opacity-100 px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold transition-all">✅ Ready</span>
                        </div>
                    </template>
                    <div x-show="recipients.length === 0" class="p-20 text-center text-slate-400">
                        <p class="text-lg font-bold">Belum ada penerima</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: HISTORY -->
    <div x-show="tab === 'history'" x-transition class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Waktu Mulai</th>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Kampanye</th>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Statistik</th>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Progress</th>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Waktu Selesai</th>
                    <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <template x-for="item in history" :key="item.id">
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-2 text-sm text-slate-500 font-medium" x-text="item.date"></td>
                        <td class="px-8 py-2">
                            <p class="text-sm font-bold text-slate-800" x-text="item.subject"></p>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter" x-text="'Batch ID: ' + item.batch_id"></p>
                        </td>
                        <td class="px-8 py-2">
                            <div class="flex items-center gap-4 text-xs">
                                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> <span x-text="item.success"></span></span>
                                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-rose-500"></span> <span x-text="item.failed"></span></span>
                                <span class="text-slate-300">/</span>
                                <span class="font-bold text-slate-600" x-text="item.total"></span>
                            </div>
                        </td>
                        <td class="px-8 py-2 text-right">
                            <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden mx-auto">
                                <div class="h-full bg-gradient-to-r from-blue-500 to-emerald-500 rounded-full transition-all" 
                                    :style="'width: ' + item.progress_percent + '%'"></div>
                            </div>
                            <div class="text-xs font-mono text-slate-500 mt-1" x-text="item.progress_percent + '%'"></div>
                        </td>
                        <td class="px-8 py-2 text-sm text-slate-500 font-medium text-right" 
                            x-text="item.datetime_selesai || '-'"></td>
                        <td class="px-8 py-2 text-right">
                            <span 
                                :class="{
                                    'bg-emerald-100 text-emerald-700': item.status === 'Selesai',
                                    'bg-amber-100 text-amber-700': item.status === 'Dibatalkan',
                                    'bg-blue-100 text-blue-700': item.status === 'Sedang Mengirim',
                                    'bg-rose-100 text-rose-700': item.status === 'Gagal'
                                }" 
                                class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest" 
                                x-text="item.status"
                            ></span>
                        </td>
                    </tr>
                </template>
                <tr x-show="history.length === 0">
                    <td colspan="4" class="px-8 py-20 text-center text-slate-400 italic">Belum ada riwayat pengiriman.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Quill Styles & JS -->
<link rel='stylesheet' href='https://cdn.quilljs.com/1.2.2/quill.snow.css'>
<script src='https://cdn.quilljs.com/1.2.2/quill.min.js'></script>
<script src='https://cdn.rawgit.com/kensnyder/quill-image-resize-module/3411c9a7/image-resize.min.js'></script>

<script>
function blastManager() {
    return {
        tab: 'editor',
        subject: '',
        content: '',
        recipients: [],
        history: [], 
        showRecipientsModal: false,
        
        // Batch Monitoring
        batchId: null, 
        isSending: false,
        progress: 0,
        processedJobs: 0,
        failedJobs: 0,
        totalJobs: 0,
        stopPolling: null, // FIX: Menyimpan referensi interval untuk stop
        
        init() {
            document.querySelectorAll('.ql-toolbar').forEach(el => el.remove());

            this.quill = new Quill('#editor', {
                theme: 'snow',
                modules: {
                    imageResize: { displaySize: true },
                    toolbar: [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        ['link', 'image'],
                        ['clean']
                    ],
                }
            });

            this.quill.on('text-change', () => {
                this.content = this.quill.root.innerHTML;
            });

            this.fetchHistory();
        },

        async fetchHistory() {
            const res = await fetch('{{ route("tools.email-blast.history") }}');
            this.history = await res.json();
        },

        switchTab(target) {
            this.tab = target;
            if (target === 'history') {
                this.fetchHistory(); 
            }
        },

        handleFileUpload(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (event) => {
                let text = event.target.result.replace(/^\uFEFF/, '').trim();
                const rows = text.split(/\r?\n|\n/).filter(row => row.trim());

                this.recipients = rows.map((row, index) => {
                    const commaIndex = row.indexOf(',');
                    const email = row.substring(0, commaIndex).replace(/[\[\]\(\)]/g, '').trim();
                    const name = row.substring(commaIndex + 1).replace(/[\[\]\(\)]/g, '').trim() || `Unlockers`;
                    
                    return { email, name };
                }).filter(r => r.email && r.email.includes('@'));

                if (this.recipients.length > 0) {
                    this.previewName = this.recipients[0].name;
                }
            };
            reader.readAsText(file, 'UTF-8');
        },

        async startBlast() {
            if (!this.subject || this.recipients.length === 0) return alert('Lengkapi data!');
            
            // FIX: Clear duplikat reference jika ada, memastikan array baru dikirim
            this.recipients = [...this.recipients]; 

            this.isSending = true;

            const res = await fetch('{{ route("tools.email-blast.send") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ subject: this.subject, content: this.content, recipients: this.recipients })
            });
            
            const data = await res.json();
            this.batchId = data.batch_id;
            this.pollProgress();
        },

        // FIX: Fungsi Stop Batch
        async stopBatch() {
            if (confirm('Batalkan pengiriman?')) {
                const res = await fetch(`/tools/email-blast/cancel/${this.batchId}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                
                if (res.ok) {
                    this.isSending = false;
                    this.batchId = null;
                    clearInterval(this.stopPolling);
                    
                    alert('Pengiriman dibatalkan.');
                    
                    setTimeout(() => {
                        this.fetchHistory();
                    }, 1000);
                }
            }
        },

        async pollProgress() {
            // FIX: Simpan interval ke variable agar bisa di-stop
            this.stopPolling = setInterval(async () => {
                const res = await fetch(`/tools/email-blast/progress/${this.batchId}`);
                const data = await res.json();
                
                this.progress = data.progress;
                this.processedJobs = data.processedJobs;
                this.failedJobs = data.failedJobs;
                this.totalJobs = data.totalJobs;

                this.fetchHistory();

                if (data.finishedAt) {
                    clearInterval(this.stopPolling); // Stop polling saat selesai
                    this.isSending = false;
                    this.batchId = null;
                }
            }, 3000);
        },

        previewName: 'Nama Penerima',
        previewContent() {
            if (!this.content) return '<p class="text-slate-300 italic">Tulis pesan...</p>';
    
            let preview = this.content;
            
            const colorMap = {
                'ungu': '#4c1d95', 'purple': '#4c1d95',
                'orange': '#f97316',
                'hijau': '#10b981', 'green': '#10b981',
                'merah': '#ef4444', 'red': '#ef4444',
                'biru': '#3b82f6', 'blue': '#3b82f6',
                'pink': '#ec4899'
            };
            
            preview = preview.replace(/button\[([^\]]+)\|([^\]]+)\|([^\]]+)\]/gi, (match, url, text, colorName) => {
                const color = colorMap[colorName.toLowerCase()] || '#4c1d95';
                return `
                    <table cellpadding="0" cellspacing="0" style="display:inline-block;margin:12px 0;">
                        <tr><td bgcolor="${color}" style="border-radius:12px;padding:16px 32px;background-color:${color};">
                            <a href="${url}" style="color:#fff;font-weight:700;font-size:16px;text-decoration:none;display:block;">${text}</a>
                        </td></tr>
                    </table>
                `;
            });

            // Fallback button syntax
            preview = preview.replace(/button\[([^\]]+)\|([^\]]+)\]/gi, (match, url, text) => {
                return `
                    <table cellpadding="0" cellspacing="0" style="display:inline-block;margin:12px 0;">
                        <tr><td href="${url}" bgcolor="#4c1d95" style="border-radius:12px;padding:16px 32px;background-color:#4c1d95;">
                            <a style="color:#fff;font-weight:700;font-size:16px;text-decoration:none;display:block;">${text}</a>
                        </td></tr>
                    </table>
                `;
            });
            
            return preview.replace(/{name}/g, `<b class="text-purple-900">${this.previewName}</b>`);
        },
    }
}
</script>

<style>
.ql-editor { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 14px; min-height: 250px; }
.ql-container.ql-snow { border-bottom-left-radius: 1.5rem; border-bottom-right-radius: 1.5rem; }
.ql-toolbar.ql-snow { border-top-left-radius: 1.5rem; border-top-right-radius: 1.5rem; border-color: #f1f5f9; }
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
@endsection
