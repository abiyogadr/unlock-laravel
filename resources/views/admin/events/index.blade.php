@extends('layouts.admin')

@section('title', 'Unlock - Admin Events')
@section('page-title', 'Manage Events')

@section('page-actions')
    <a href="{{ route('admin.events.create') }}" 
       class="bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-secondary transition duration-300 shadow-sm flex items-center gap-2">
        <i class="fas fa-plus"></i>
        <span>Tambah Event</span>
    </a>
@endsection

@section('admin-content')

<div x-data="deleteLogic">

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        
        {{-- HEADER & FILTER SECTION --}}
        <div class="p-6 border-b border-gray-100 space-y-4">
            <h3 class="text-lg font-semibold text-gray-800">Daftar Event Webinar</h3>
            
            {{-- FORM FILTER AUTO-SUBMIT --}}
            <form action="{{ route('admin.events.index') }}" method="GET" x-data>
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
    
                    {{-- 1. Search (Debounce 500ms) --}}
                    <x-input-field 
                        name="search" 
                        label="Pencarian" 
                        icon="fas fa-search" 
                        :value="request('search')" 
                        placeholder="Ketik kode / judul..."
                        class="md:col-span-4"
                        x-on:input.debounce.500ms="$el.closest('form').submit()"
                        :showError="false"
                    />

                    {{-- 2. Status (Auto Submit on Change) --}}
                    <x-custom-select 
                        name="status" 
                        label="Status" 
                        :value="request('status', 'all')" 
                        placeholder="Semua Status"
                        class="md:col-span-2"
                        onchange="this.form.submit()"
                    >
                        <x-custom-select-item val="all" label="Semua Status">Semua Status</x-custom-select-item>
                        <x-custom-select-item val="open" label="Open">
                            <span class="text-green-600">●</span> Open
                        </x-custom-select-item>
                        <x-custom-select-item val="close" label="Close">
                            <span class="text-gray-600">●</span> Close
                        </x-custom-select-item>
                        <x-custom-select-item val="canceled" label="Canceled">
                            <span class="text-red-600">●</span> Canceled
                        </x-custom-select-item>
                    </x-custom-select>

                    {{-- 3. Date Range (Auto Submit on Change) --}}
                    <div class="md:col-span-4 grid grid-cols-2 gap-2">
                        <x-input-field 
                            type="date"
                            name="date_from" 
                            label="Dari Tanggal" 
                            :value="request('date_from')" 
                            x-on:change="$el.closest('form').submit()"
                            :showError="false"
                        />
                        <x-input-field 
                            type="date"
                            name="date_to" 
                            label="Sampai Tanggal" 
                            :value="request('date_to')" 
                            x-on:change="$el.closest('form').submit()"
                            :showError="false"
                        />
                    </div>

                    {{-- 4. Reset Button --}}
                    <div class="md:col-span-2 flex items-end">
                        @if(request()->anyFilled(['search', 'date_from', 'date_to']) || request('status', 'all') !== 'all')
                            <a href="{{ route('admin.events.index') }}" 
                            class="w-full py-2.5 px-3 bg-red-50 text-red-600 border border-red-100 rounded-lg hover:bg-red-100 text-sm font-medium transition text-center flex items-center justify-center gap-2">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        @else
                        <div class="w-full py-2.5 px-3 text-gray-300 text-sm font-medium text-center border border-gray-100 rounded-lg bg-gray-50 cursor-not-allowed">
                                Auto Filter
                        </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
        
        {{-- VIEW DESKTOP --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($events as $event)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">{{ $event->event_code }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate" title="{{ $event->event_title }}">
                            {{ Str::limit($event->event_title, 50) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($event->date_start)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                {{ $event->status === 'open' ? 'bg-green-100 text-green-800' : 
                                   ($event->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($event->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <a href="{{ route('admin.events.edit', $event) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                            <a href="{{ route('admin.events.certificate.setup', $event) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-blue-700 rounded-lg hover:bg-blue-200 transition">
                                <i class="fas fa-certificate mr-1"></i>Cert
                            </a>
                            <button type="button" @click="openDelete({{ $event->id }}, '{{ $event->event_code }}', '{{ addslashes($event->event_title) }}')" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition cursor-pointer">
                                <i class="fas fa-trash mr-1"></i>Hapus
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-search text-4xl mb-4 opacity-30"></i>
                            <p>Data tidak ditemukan sesuai filter.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- VIEW MOBILE --}}
        <div class="md:hidden block bg-gray-50 p-4 space-y-4">
            @forelse($events as $event)
            <div class="bg-white rounded-lg shadow p-4 border border-gray-100">
                <div class="flex justify-between items-start mb-3">
                    <span class="text-xs font-mono bg-gray-100 px-2 py-1 rounded text-gray-600">{{ $event->event_code }}</span>
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                        {{ $event->status === 'open' ? 'bg-green-100 text-green-800' : 
                           ($event->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ ucfirst($event->status) }}
                    </span>
                </div>
                <h4 class="text-gray-900 font-semibold mb-2 line-clamp-2">{{ $event->event_title }}</h4>
                <div class="flex items-center text-sm text-gray-500 mb-4">
                    <i class="far fa-calendar-alt mr-2"></i>
                    {{ \Carbon\Carbon::parse($event->date_start)->format('d M Y') }}
                </div>
                <div class="flex gap-2 pt-3 border-t border-gray-50">
                    <a href="{{ route('admin.events.edit', $event) }}" class="flex-1 text-center py-2 bg-blue-50 text-blue-700 rounded-lg text-sm font-medium hover:bg-blue-100">Edit</a>
                    <button type="button" @click="openDelete({{ $event->id }}, '{{ $event->event_code }}', '{{ addslashes($event->event_title) }}')" class="flex-1 text-center py-2 bg-red-50 text-red-700 rounded-lg text-sm font-medium hover:bg-red-100">Hapus</button>
                </div>
            </div>
            @empty
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-search text-4xl mb-4 opacity-30"></i>
                <p>Data tidak ditemukan.</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($events->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
            {{ $events->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

    {{-- MODAL DELETE --}}
    <div x-show="isOpen" 
         x-transition.opacity
         class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
         style="display: none;">
        <div @click.away="closeDelete()" class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Konfirmasi Hapus</h3>
                <p class="text-gray-600 mb-2">Apakah Anda yakin ingin menghapus:</p>
                <p class="font-semibold text-gray-900 bg-gray-50 px-3 py-1 rounded text-sm break-words" x-text="eventTitle"></p>
            </div>
            <div class="flex gap-3">
                <button type="button" @click="closeDelete()" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium cursor-pointer">Batal</button>
                <form :action="deleteUrl" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium cursor-pointer">Hapus Event</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('deleteLogic', () => ({
        isOpen: false,
        deleteUrl: '',
        eventTitle: '',
        openDelete(id, code, title) {
            this.isOpen = true;
            this.eventTitle = code + ' - ' + title;
            this.deleteUrl = `/upanel/events/${id}`;
        },
        closeDelete() {
            this.isOpen = false;
        }
    }));
});
</script>
@endsection
