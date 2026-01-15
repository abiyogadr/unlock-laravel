@extends('layouts.admin')

@section('title', 'Unlock - Buat Event')

@section('admin-content')
@if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        
        <!-- HEADER -->
        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-blue-50">
            <div class="flex items-center gap-3">
                <i class="fas fa-edit text-2xl text-primary"></i>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Buat Event Baru</h2>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-2">
            <!-- POSTER -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div x-data="{ preview: null }" class="lg:col-span-1 space-y-3">

                    <!-- Preview -->
                    <div
                        class="mx-auto w-full h-[325px] bg-gray-100 rounded-xl
                            flex items-center justify-center
                            border-2 border-dashed border-gray-300 overflow-hidden">

                        <!-- Image -->
                        <template x-if="preview">
                            <img
                                :src="preview"
                                class="w-full h-full object-contain rounded-xl"
                                alt="Preview">
                        </template>

                        <!-- Placeholder -->
                        <template x-if="!preview">
                            <i class="fas fa-image text-4xl text-gray-400"></i>
                        </template>
                    </div>

                    <!-- Upload -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Upload Poster
                        </label>

                        <input
                            type="file"
                            name="kv_path"
                            accept="image/*"
                            @change="preview = URL.createObjectURL($event.target.files[0])"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg
                                focus:ring-1 focus:ring-primary
                                file:mr-2 file:py-1.5 file:px-3 file:rounded
                                file:border-0 file:text-xs file:font-medium
                                file:bg-primary/10 file:text-primary">

                        <p class="text-xs text-gray-500 mt-1">Max 2MB</p>
                    </div>

                </div>

                
                <div class="lg:col-span-2 space-y-4">
                    <!-- BASIC INFO -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">

                        {{-- EVENT CODE --}}
                        <x-input-field 
                            name="event_code" 
                            label="Event Code" 
                            prefix="event-" 
                            placeholder="kode-event"
                            required 
                        />

                        {{-- STATUS --}}
                        <x-custom-select name="status" label="Status" :value="old('status')" placeholder="Pilih status…">
                            <x-custom-select-item val="open" label="Open">
                                <span class="text-green-600">●</span> Open
                            </x-custom-select-item>
                            <x-custom-select-item val="close" label="Close">
                                <span class="text-gray-600">●</span> Close
                            </x-custom-select-item>
                            <x-custom-select-item val="draft" label="Draft">
                                <span class="text-yellow-600">●</span> Draft
                            </x-custom-select-item>
                        </x-custom-select>

                        {{-- CLASSIFICATION --}}
                        <x-custom-select name="classification" label="Classification" :value="old('classification')" placeholder="Pilih klasifikasi…">
                            @foreach([
                                'Accounting'          => 'text-emerald-600',
                                'Audit'               => 'text-blue-600',
                                'Audit Digital'       => 'text-sky-600',
                                'Audit Investigasi'   => 'text-orange-600',
                                'External Audit'      => 'text-indigo-600',
                                'Financial Planning'  => 'text-pink-600',
                                'Forensic Accounting' => 'text-yellow-600',
                                'Fraud Prevention'    => 'text-rose-600',
                                'ICOFR'               => 'text-cyan-600',
                                'Internal Audit'      => 'text-green-600',
                                'Internal Control'    => 'text-lime-600',
                                'Law'                 => 'text-gray-600',
                                'PSAK'                => 'text-teal-600',
                                'Risk Management'     => 'text-red-600',
                                'Tax Planning'        => 'text-indigo-600',
                                'Tax Technology'      => 'text-purple-600',
                            ] as $label => $color)
                                <x-custom-select-item :val="$label" :label="$label">
                                    <span class="{{ $color }}">●</span> {{ $label }}
                                </x-custom-select-item>
                            @endforeach
                        </x-custom-select>
                    </div>

                    <!-- EVENT TITLE -->
                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Event Title</label>
                            <textarea name="event_title" rows="3" 
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition resize-vertical" 
                                required>{{ old('event_title') }}</textarea>
                        </div>
                    </div>

                    <!-- UPLOAD + DATES -->
                    <div class="grid grid-cols-1 gap-3">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            <x-input-field type="date" name="date_start" label="Date Start" required />
                            <x-input-field type="date" name="date_end" label="Date End" required />
                            <x-input-field type="time" name="time_start" label="Time Start" required />
                            <x-input-field type="time" name="time_end" label="Time End" required />
                        </div>
                    </div>

                    <!-- ADDITIONAL INFO -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input-field name="payment_unique_code" label="Unique Payment Code" placeholder="Contoh: 001"/>
                        <x-input-field name="collaboration" label="Collaboration" placeholder="Nama mitra..."/>
                    </div>
                </div>
            </div>

            <!-- PACKET SELECTION -->
            <div class="mt-4 mb-4">
                <div x-data="{
                    packets: {{ $packets->toJson() }},
                    selectedPackets: [],
                    addPacket(packetId) {
                        if (!packetId) return;
                        const packet = this.packets.find(p => p.id == packetId);
                        if (packet && !this.selectedPackets.find(p => p.id == packet.id)) {
                            this.selectedPackets.push(packet);
                        }
                    },
                    removePacket(packetId) {
                        this.selectedPackets = this.selectedPackets.filter(p => p.id != packetId);
                    }
                }">
                    <label class="block text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-box text-primary"></i> Pilih Paket
                    </label>
                    
                    <!-- Flex container: dropdown and tags side by side -->
                    <div class="flex flex-wrap items-start gap-3">
                        <!-- Dropdown - fixed width -->
                        <div class="w-64 flex-shrink-0">
                            <select @change="addPacket($event.target.value)" 
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary focus:border-primary">
                                <option value="">Pilih paket...</option>
                                <template x-for="packet in packets" :key="packet.id">
                                    <option :value="packet.id" x-text="packet.packet_name"></option>
                                </template>
                            </select>
                        </div>
                        
                        <!-- Tags container - flexible width, wraps when full -->
                        <div class="flex-1 flex flex-wrap gap-2 min-h-[40px]">
                            <template x-for="packet in selectedPackets" :key="packet.id">
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 
                                            bg-purple-100 text-purple-800 border-primary/20
                                            rounded-full text-sm font-medium shadow-sm">
                                    <span x-text="packet.packet_name"></span>
                                    <button type="button" @click="removePacket(packet.id)" 
                                            class="text-purple-600 hover:text-purple-800 hover:bg-purple-200 
                                                w-5 h-5 flex items-center justify-center rounded-full 
                                                transition-colors duration-150">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                    <input type="hidden" name="packet_ids[]" :value="packet.id">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SPEAKER -->
            <x-multi-select 
                name="speaker_ids" 
                label="Pilih Pembicara" 
                :options="$speakers" 
                labelField="display_name"
                class="md:col-span-2 mb-20" {{-- Mengatur grid & margin pada wrapper --}}
                placeholder="Cari..."
            />

        </div>
        
        <!-- BUTTONS -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
            <a href="{{ route('admin.events.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-100 transition duration-200 cursor-pointer">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-white font-medium hover:bg-secondary shadow-lg shadow-primary/30 transition duration-200 transform hover:-translate-y-0.5 cursor-pointer">
                <i class="fas fa-save mr-2"></i>Simpan
            </button>
        </div>
    </form>
</div>
@endsection
