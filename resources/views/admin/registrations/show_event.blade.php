@extends('layouts.admin')

@section('title', 'Peserta - ' . $event->event_title)
@section('page-title', 'Data Peserta')

@section('page-actions')
    <a href="{{ route('admin.registrations.index') }}" class="text-gray-500 hover:text-gray-700 font-medium flex items-center transition text-sm md:text-base">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
@endsection

@section('admin-content')

{{-- 1. Statistik Cards - Responsive Grid --}}
<div x-data="certificateManager()">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-6">
        <div class="bg-white p-3 md:p-4 rounded-xl border border-gray-100 shadow-sm">
            <p class="text-[10px] md:text-xs text-gray-500 uppercase font-semibold">Total Peserta</p>
            <p class="text-lg md:text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white p-3 md:p-4 rounded-xl border border-gray-100 shadow-sm border-l-4 border-l-yellow-400">
            <p class="text-[10px] md:text-xs text-gray-500 uppercase font-semibold">Pending</p>
            <p class="text-lg md:text-2xl font-bold text-yellow-600 mt-1">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white p-3 md:p-4 rounded-xl border border-gray-100 shadow-sm border-l-4 border-l-green-500">
            <p class="text-[10px] md:text-xs text-gray-500 uppercase font-semibold">Verified</p>
            <p class="text-lg md:text-2xl font-bold text-green-600 mt-1">{{ $stats['verified'] }}</p>
        </div>
        <div class="bg-white p-3 md:p-4 rounded-xl border border-gray-100 shadow-sm">
            <p class="text-[10px] md:text-xs text-gray-500 uppercase font-semibold">Revenue</p>
            <p class="text-sm md:text-xl font-bold text-primary mt-1">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- 2. Konten Utama --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        
        {{-- Toolbar: Search & Filter --}}
        <div class="p-4 md:p-5 border-b border-gray-100">
            <div class="flex flex-col lg:flex-row justify-between gap-4">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-users text-gray-400"></i> Daftar Peserta
                </h3>
                <div class="flex gap-4">
                    {{-- PEMBUNGKUS TOGGLE --}}
                    <div x-data="{ 
                        is_open: {{ $event->is_attendance_open ? 'true' : 'false' }},
                        loading: false,
                        async toggle() {
                            if (this.loading) return;
                            this.loading = true;
                            
                            try {
                                let response = await fetch('{{ route('admin.events.toggle_attendance', $event->id) }}', {
                                    method: 'PATCH',
                                    headers: { 
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    }
                                });
                                
                                let data = await response.json();
                                if (data.success) {
                                    this.is_open = data.is_open;
                                }
                            } catch (error) {
                                alert('Gagal mengubah status presensi');
                            } finally {
                                this.loading = false;
                            }
                        }
                    }" class="relative z-10 flex items-center gap-2 bg-gray-50 px-3 py-2 rounded-xl border border-gray-100">
                        
                        <span class="text-[10px] font-bold uppercase tracking-wider transition-colors" 
                            :class="is_open ? 'text-green-600' : 'text-gray-400'" 
                            x-text="is_open ? 'Presensi Buka' : 'Presensi Tutup'">
                        </span>
                        
                        {{-- TOMBOL TOGGLE --}}
                        <button type="button" 
                                @click="toggle()" 
                                :disabled="loading"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer items-center rounded-full transition-colors duration-200 focus:outline-none shadow-inner"
                                :class="is_open ? 'bg-green-500' : 'bg-gray-300'">
                            
                            {{-- BULATAN TOGGLE --}}
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200 shadow-sm"
                                :class="is_open ? 'translate-x-6' : 'translate-x-1'">
                            </span>
                        </button>
                    </div>
                    <button @click="openModal()" class="px-4 py-2 hidden md:block bg-primary text-white rounded-lg text-sm font-bold flex items-center gap-2 cursor-pointer">
                        <i class="fas fa-book"></i> Generate Sertifikat 
                    </button>
                    <a href="{{ route('admin.registrations.export', ['event_id' => $event->id]) }}" class="text-lg font-bold flex items-center gap-2">
                        <i class="fas fa-download text-green-600"></i>
                    </a>

                    {{-- Ganti bagian Form di Blade Anda dengan ini --}}
                    <form method="GET" x-data>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:flex gap-2">
                            
                            {{-- Filter Status - Auto submit saat ganti status --}}
                            <select name="status" 
                                    x-on:change="$el.form.submit()"
                                    class="text-sm border border-primary/50 rounded-lg focus:ring-primary focus:border-primary w-full lg:w-44 bg-white">
                                <option value="all">Semua Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>

                            {{-- Search - Auto submit saat berhenti mengetik (debounce) --}}
                            <div class="relative w-full lg:w-64">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                <input type="text" name="search" value="{{ request('search') }}" 
                                    x-on:input.debounce.500ms="$el.form.submit()"
                                    placeholder="Nama / Email / Kode..." 
                                    class="pl-9 pr-4 py-2 w-full text-sm border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                            </div>

                            {{-- Tombol Reset (Opsional, hanya muncul jika ada filter aktif) --}}
                            @if(request()->filled('search') || request('status', 'all') !== 'all')
                                <a href="{{ route(request()->route()->getName(), $event->id) }}" 
                                class="px-4 py-2 bg-red-50 text-red-600 rounded-lg text-sm font-medium flex items-center justify-center gap-2 hover:bg-red-100 transition">
                                    <i class="fas fa-undo"></i> Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- VIEW DESKTOP --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-4 text-left">Peserta</th>
                        <th class="px-6 py-4 text-left">Paket</th>
                        <th class="px-6 py-4 text-left">Tanggal</th>
                        <th class="px-6 py-4 text-left">Status</th>
                        <th class="px-6 py-4 text-left">Presensi</th>
                        <th class="px-6 py-4 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse($registrations as $reg)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">{{ $reg->name }}</div>
                            <div class="text-xs text-gray-400">{{ $reg->registration_code }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-700">{{ $reg->packet->packet_name ?? '-' }}</div>
                            <div class="text-xs text-gray-500">Rp {{ number_format($reg->packet->price ?? 0, 0, ',','.') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                            {{ $reg->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $colors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'verified' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    '0' => 'bg-yellow-100 text-yellow-800',
                                    '1' => 'bg-green-100 text-green-800',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-[10px] font-bold rounded-full {{ $colors[$reg->registration_status] ?? 'bg-gray-100' }}">
                                @if ($reg->payment_status === 'success')
                                    {{ strtoupper($reg->registration_status) }}
                                @elseif ($reg->payment_status === 'pending')
                                    {{ 'Belum Bayar' }}
                                @else
                                    {{ 'Gagal' }}
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-[10px] font-bold rounded-full {{ $colors[$reg->is_attended  ] ?? 'bg-gray-100' }}">
                                {{ $reg->is_attended ? 'Terisi' : 'Belum' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right flex justify-start gap-3">
                            <a href="{{ route('myevents.show', $reg->id) }}" class="text-indigo-600 hover:underline">Detail</a>
                            @if($reg->registration_status === 'verified')
                                <button type="button" 
                                        onclick="confirmSingle('{{ $reg->id }}', {{ $reg->certificate ? 'true' : 'false' }})"
                                        class="text-indigo-600 font-bold flex items-center gap-1 cursor-pointer">
                                    <i class="fas fa-certificate"></i> Sertifikat
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400">Tidak ada peserta.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- VIEW MOBILE --}}
        <div class="md:hidden divide-y divide-gray-100">
            @forelse($registrations as $reg)
            <div class="p-4 space-y-3 bg-white">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-xs font-mono text-gray-400 mb-1">#{{ $reg->registration_code }}</div>
                        <div class="font-bold text-gray-900">{{ $reg->name }}</div>
                    </div>
                    <div class="flex gap-2">
                        <span class="px-2 py-1 text-[10px] font-bold rounded-full {{ $colors[$reg->registration_status] ?? 'bg-gray-100' }}">
                            {{ Str::title($reg->registration_status) }}
                        </span>
                        <span class="px-2 py-1 text-[10px] font-bold rounded-full {{ $colors[$reg->is_attended] ?? 'bg-gray-100' }}">
                            {{ $reg->is_attended ? 'Sudah Presensi' : 'Belum Presensi' }}
                        </span>
                    </div>
                </div>
                <div class="flex justify-between text-xs text-gray-600 bg-gray-50 p-2 rounded">
                    <div>
                        <div class="text-gray-400 uppercase text-[9px] font-bold">Paket</div>
                        <div class="font-medium">{{ $reg->packet->packet_name ?? '-' }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-gray-400 uppercase text-[9px] font-bold">Tanggal</div>
                        <div>{{ $reg->created_at->format('d/m/y H:i') }}</div>
                    </div>
                </div>
                <a href="{{ route('myevents.show', $reg->id) }}" class="block w-full text-center py-2 bg-primary/10 text-primary rounded-lg text-sm font-bold">
                    Lihat Detail / Verifikasi
                </a>
            </div>
            @empty
            <div class="p-8 text-center text-gray-400 text-sm">Tidak ada peserta.</div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($registrations->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $registrations->withQueryString()->links() }}
        </div>
        @endif
    </div>
    {{-- MODAL MASSAL DENGAN TABEL & SEARCH --}}
    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4">
            {{-- Overlay --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="open = false"></div>
            
            {{-- Modal Content --}}
            <div class="relative bg-white rounded-2xl shadow-xl max-w-4xl w-full overflow-hidden">
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Generate Sertifikat Massal</h3>
                        <p class="text-xs text-gray-500 italic">Pilih peserta yang ingin dibuatkan sertifikatnya.</p>
                    </div>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>

                {{-- Toolbar: Search & Select All --}}
                <div class="p-4 border-b border-gray-50 flex flex-col sm:flex-row justify-between gap-3 bg-white">
                    <div class="relative w-full sm:w-64">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" x-model="searchQuery" placeholder="Cari nama peserta..." 
                            class="pl-9 pr-4 py-2 w-full text-sm border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="selectAll(true)" class="text-xs font-bold text-primary hover:underline">Pilih Semua</button>
                        <span class="text-gray-300">|</span>
                        <button @click="selectAll(false)" class="text-xs font-bold text-red-500 hover:underline">Batal Semua</button>
                    </div>
                </div>

                {{-- Body: Table --}}
                <div class="overflow-x-auto max-h-[50vh]">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] font-bold sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-3 w-10">#</th>
                                <th class="px-6 py-3">Nama Peserta</th>
                                <th class="px-6 py-3">Paket</th>
                                <th class="px-6 py-3 text-center">Presensi</th>
                                <th class="px-6 py-3 text-center">Sertifikat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            {{-- Loading State --}}
                            <template x-if="loading && filteredParticipants.length === 0">
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-gray-400">
                                        <i class="fas fa-spinner animate-spin mr-2"></i> Mengambil data...
                                    </td>
                                </tr>
                            </template>

                            {{-- Data Loop --}}
                            <template x-for="p in filteredParticipants" :key="p.id">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-3">
                                        <input type="checkbox" x-model="p.selected" class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="font-bold text-gray-900" x-text="p.name"></div>
                                    </td>
                                    <td class="px-6 py-3">
                                        <span class="text-xs text-gray-600" x-text="p.packet_type"></span>
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <span :class="p.attended ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400'" 
                                            class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase">
                                            <i :class="p.attended ? 'fas fa-check' : 'fas fa-times'" class="mr-1"></i>
                                            <span x-text="p.attended ? 'Hadir' : 'Absen'"></span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <span :class="p.has_certificate ? 'text-green-500' : 'text-gray-300'">
                                            <i :class="p.has_certificate ? 'fas fa-check-circle' : 'far fa-circle'" class="text-lg"></i>
                                            <div class="text-[9px] font-bold uppercase" x-text="p.has_certificate ? 'Sudah' : 'Belum'"></div>
                                        </span>
                                    </td>
                                </tr>
                            </template>

                            {{-- Empty State --}}
                            <template x-if="filteredParticipants.length === 0 && !loading">
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-gray-400">Data peserta tidak ditemukan.</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 bg-gray-50 border-t flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="text-xs text-gray-500 font-medium">
                        Terpilih: <span class="text-primary font-bold" x-text="selectedCount"></span> peserta
                    </div>
                    <div class="flex gap-2 w-full sm:w-auto">
                        <button @click="open = false" class="flex-1 sm:flex-none px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">Batal</button>
                        <button @click="submitMass()" :disabled="loading || selectedCount === 0" 
                            class="flex-1 sm:flex-none px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 disabled:opacity-50 transition shadow-md cursor-pointer">
                            <span x-show="!loading"><i class="fas fa-magic mr-1"></i> Generate Sertifikat</span>
                            <span x-show="loading"><i class="fas fa-spinner animate-spin mr-1"></i> Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Global function untuk tombol di tabel
    window.confirmSingle = function(id, status) {
        let msg = status === true ? "Sertifikat sudah ada. Generate ulang?" : "Buat sertifikat untuk peserta ini?";
        if (confirm(msg)) {
            window.location.href = `/upanel/registrations/${id}/generate-certificate`;
        }
    };

    function certificateManager() {
        return {
            open: false,
            loading: false,
            participants: [],
            searchQuery: '',
            
            // Memfilter data berdasarkan input nama
            get filteredParticipants() {
                if (this.searchQuery.trim() === '') {
                    return this.participants;
                }
                return this.participants.filter(p => 
                    p.name.toLowerCase().includes(this.searchQuery.toLowerCase())
                );
            },

            // Menghitung jumlah yang dicentang
            get selectedCount() {
                return this.participants.filter(p => p.selected).length;
            },

            openModal() {
                this.open = true;
                this.fetchData();
            },

            fetchData() {
                this.loading = true;
                fetch("{{ route('admin.registrations.list_for_certificate', $event->id) }}")
                    .then(res => res.json())
                    .then(data => { 
                        this.participants = data; 
                    })
                    .finally(() => this.loading = false);
            },

            // Fungsi Pilih Semua / Batal Semua
            selectAll(status) {
                this.filteredParticipants.forEach(p => p.selected = status);
            },

            submitMass() {
                const ids = this.participants.filter(p => p.selected).map(p => p.id);
                if (ids.length === 0) return alert('Pilih minimal satu peserta!');
                
                this.loading = true;
                fetch("{{ route('admin.registrations.generate_mass_certificate') }}", {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    },
                    body: JSON.stringify({ ids: ids })
                })
                .then(res => res.json())
                .then(data => { 
                    alert(data.message); 
                    this.open = false; 
                    location.reload(); 
                })
                .catch(() => alert('Terjadi kesalahan teknis.'))
                .finally(() => this.loading = false);
            }
        }
    }
</script>
@endsection
