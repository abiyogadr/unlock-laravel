@extends('layouts.admin')

@section('title', 'Unlock - Edit Events')
@section('page-title', 'Edit Event')

@section('page-actions')
    <a href="{{ route('admin.events.index') }}" class="text-gray-500 hover:text-gray-700 font-medium flex items-center transition duration-200">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
@endsection

@section('admin-content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <form action="{{ route('admin.events.update', $event) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @csrf
        @method('PUT')
        
        <!-- HEADER -->
        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-blue-50">
            <div class="flex items-center gap-3">
                <i class="fas fa-edit text-2xl text-primary"></i>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $event->event_code }}</h2>
                    <p class="text-sm text-gray-600">{{ $event->event_title }}</p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <!-- POSTER -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1">
                    @if($event->kv_path)
                        <img src="{{ asset('storage/' . $event->kv_path) }}" 
                             alt="Poster" 
                             class="block mx-auto max-w-full max-h-[300px] md:max-h-[400px] mb-4 w-auto h-auto object-cover rounded-xl shadow-md border border-gray-200">
                    @else
                        <div class="mx-auto max-w-full max-h-[300px] md:max-h-[400px] w-auto h-auto bg-gray-100 rounded-xl flex items-center justify-center border-2 border-dashed border-gray-300">
                            <i class="fas fa-image text-4xl text-gray-400"></i>
                        </div>
                    @endif
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Upload Poster</label>
                            <input type="file" name="kv_path" accept="image/*" 
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-primary/10 file:text-primary">
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
                            :value="$event->event_code" 
                            required 
                        />

                        {{-- STATUS --}}
                        <x-custom-select name="status" label="Status" :value="$event->status" placeholder="Pilih status…">
                            @foreach(['open' => 'text-green-600', 'close' => 'text-gray-600', 'draft' => 'text-yellow-600'] as $key => $color)
                                <x-custom-select-item :val="$key" :label="ucfirst($key)">
                                    <span class="{{ $color }}">●</span> {{ ucfirst($key) }}
                                </x-custom-select-item>
                            @endforeach
                        </x-custom-select>

                        {{-- CLASSIFICATION --}}
                        <x-custom-select name="classification" label="Classification" :value="$event->classification" placeholder="Pilih klasifikasi…">
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
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Event Title *</label>
                            <textarea name="event_title" rows="3" 
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition resize-vertical" 
                                required>{{ old('event_title', $event->event_title) }}</textarea>
                        </div>
                    </div>

                    <!-- DATES & TIMES -->
                    <div class="grid grid-cols-1 gap-3">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            <x-input-field 
                                type="date" 
                                name="date_start" 
                                label="Date Start" 
                                :value="\Carbon\Carbon::parse($event->date_start)->format('Y-m-d')" 
                                required 
                            />
                            <x-input-field 
                                type="date" 
                                name="date_end" 
                                label="Date End" 
                                :value="\Carbon\Carbon::parse($event->date_end)->format('Y-m-d')" 
                                required 
                            />
                            <x-input-field 
                                type="time" 
                                name="time_start" 
                                label="Time Start" 
                                :value="$event->time_start" 
                                required 
                            />
                            <x-input-field 
                                type="time" 
                                name="time_end" 
                                label="Time End" 
                                :value="$event->time_end" 
                                required 
                            />
                        </div>
                    </div>

                    <!-- ADDITIONAL INFO -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input-field 
                            name="payment_unique_code" 
                            label="Payment Code" 
                            :value="$event->payment_unique_code" 
                        />
                        <x-input-field 
                            name="collaboration" 
                            label="Collaboration" 
                            :value="$event->collaboration" 
                        />
                    </div>
                </div>
            </div>

            <!-- PACKET SELECTION -->
            <div class="mt-4 mb-4">
                <div x-data="{
                    packets: {{ $packets->toJson() }},
                    selectedPackets: {{ $eventPackets->toJson() }}, // Pre-fill with event's packets
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
                    
                    <div class="flex flex-wrap items-start gap-3">
                        <!-- Dropdown -->
                        <div class="w-64 flex-shrink-0">
                            <select @change="addPacket($event.target.value)" 
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary focus:border-primary">
                                <option value="">Pilih paket...</option>
                                <template x-for="packet in packets" :key="packet.id">
                                    <option :value="packet.id" x-text="packet.packet_name"></option>
                                </template>
                            </select>
                        </div>
                        
                        <!-- Tags container -->
                        <div class="flex-1 flex flex-wrap gap-2 min-h-[40px] max-h-32 overflow-y-auto">
                            <template x-for="packet in selectedPackets" :key="packet.id">
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 
                                            bg-purple-100 text-purple-800
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

            <!-- SPEAKERS -->
            <x-multi-select 
                name="speaker_ids" 
                label="Event Speakers" 
                :options="$speakers" 
                :selected="old('speaker_ids', $event->speakers->pluck('id')->toArray())" 
                labelField="display_name" 
                placeholder="Cari & tambah pembicara..."
                class="mb-40"
            />

                <!-- HIDDEN INPUTS -->
                <template x-for="id in selected" :key="'h-'+id">
                    <input type="hidden" name="speaker_ids[]" :value="id">
                </template>
            </div>
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
