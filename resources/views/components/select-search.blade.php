@props([
    'label', 
    'name', 
    'options', 
    'model', 
    'required' => false, 
    'disabled' => 'false', 
    'placeholder' => 'Pilih opsi...',
    'icon' => null // TAMBAHAN: Prop untuk Icon FontAwesome
])

<div class="space-y-1"> {{-- Space-y-1 agar label lebih dekat --}}
    <label class="block text-sm font-semibold text-gray-600 mb-1">
        {{ $label }}
        @if($required) <span class="text-red-500">*</span> @endif
    </label>

    <input type="hidden" name="{{ $name }}" x-model="{{ $model }}" >

    <div class="relative" x-data="{ open: false, search: '' }" @click.outside="open = false" x-effect="if (open) { $nextTick(() => { setTimeout(() => $refs.searchInput?.focus(), 50) }) }">
        
        {{-- BAGIAN ICON (Absolute Position di Kiri) --}}
        @if($icon)
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                <i class="{{ $icon }} text-gray-400"></i>
            </div>
        @endif

        {{-- BUTTON TRIGGER --}}
        <button type="button" 
            @click="if(!({{ $disabled }})) { open = !open; $nextTick(() => $refs.searchInput.focus()); }"
            class="relative w-full relative py-2.5 pr-10 text-left font-semibold text-gray-600 border rounded-xl transition-all duration-200
                   focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary hover:bg-gray-50 cursor-pointer sm:text-sm shadow-sm
                   grid grid-cols-[1fr_auto] items-center gap-2"
            {{-- Class Dinamis untuk Style --}}
            :class="{
                'opacity-60 cursor-not-allowed bg-gray-100': {{ $disabled }}, 
                'bg-white border-gray-300': !open && !errors['{{ $name }}'],
                'border-primary ring-2 ring-primary bg-white': open, 
                'border-red-500': errors['{{ $name }}'],
                'pl-10': '{{ $icon }}',  /* Padding kiri 10 jika ada icon */
                'px-4': !'{{ $icon }}'   /* Padding kiri 4 jika tidak ada icon */
            }"
            :disabled="{{ $disabled }}"
        >
            <span class="truncate block w-full" 
                  x-text="{{ $options }}.find(o => o.value == {{ $model }})?.label || '{{ $placeholder }}'" 
                  :class="{'text-gray-400': !{{ $model }}, 'text-gray-600': {{ $model }}}">
            </span>
            
            {{-- Chevron Icon (Absolute di Kanan) --}}
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <i class="fas fa-chevron-down text-xs text-gray-400 transition-transform duration-200" 
                   :class="{ 'rotate-180': open }"></i>
            </div>
        </button>

        {{-- DROPDOWN MENU --}}
        <div x-show="open" style="display: none;" 
             class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-hidden flex flex-col"
             x-transition.opacity.duration.200ms
             @click.stop>
            
            <div class="p-2 border-b border-gray-100 bg-gray-50 sticky top-0 z-10">
                <input type="text" x-ref="searchInput" x-model="search" 
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" 
                       placeholder="Cari...">
            </div>

            <div class="overflow-y-auto flex-1">
                <template x-for="option in {{ $options }}.filter(i => i.label.toLowerCase().includes(search.toLowerCase()))" :key="option.value">
                    <div @click="{{ $model }} = option.value; open = false; search = ''; delete errors['{{ $name }}']" 
                         class="px-4 py-2 text-sm text-gray-700 hover:bg-primary/5 hover:text-primary cursor-pointer border-b border-gray-50 last:border-0 truncate">
                        <span x-text="option.label"></span>
                    </div>
                </template>
                <div x-show="{{ $options }}.filter(i => i.label.toLowerCase().includes(search.toLowerCase())).length === 0" class="p-3 text-center text-gray-400 text-sm">Tidak ada hasil</div>
            </div>
        </div>
    </div>
    
    <p x-show="errors['{{ $name }}']" x-text="errors['{{ $name }}']" class="mt-1 text-sm text-red-600 font-medium" style="display: none;"></p>
</div>
