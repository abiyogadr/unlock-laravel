@props([
    'name',
    'label' => null,
    'options' => [], 
    'selected' => [], 
    'placeholder' => 'Cari dan pilih...',
    'labelField' => 'name', 
    'valueField' => 'id',
    'inputClass' => '' {{-- Prop untuk class tambahan pada input --}}
])

<div
    x-data="{
        open: false,
        search: '',
        options: {{ json_encode($options) }},
        selected: {{ json_encode($selected) }},
        add(id) {
            if (!this.selected.includes(id)) {
                this.selected.push(id);
            }
            this.search = '';
            this.open = false;
        },
        remove(id) {
            this.selected = this.selected.filter(v => v !== id);
        }
    }"
    {{-- $attributes->only('class') akan menaruh class di wrapper terluar --}}
    {{ $attributes->only('class')->merge(['class' => 'relative']) }}
    @click.away="open = false"
>
    @if($label)
        <label class="block text-sm font-semibold text-gray-600 mb-1">
            {{ $label }}
            @if($attributes->has('required')) <span class="text-red-500">*</span> @endif
        </label>
    @endif

    <!-- TAGS -->
    <div class="flex-1 flex flex-wrap gap-2 mb-2 min-h-[40px] max-h-32 overflow-y-auto">
        <template x-for="id in selected" :key="id">
            <div class="flex items-center gap-2 px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-medium shadow-sm">
                <span x-text="options.find(o => o.{{ $valueField }} == id)?.{{ $labelField }}"></span>
                <button type="button" @click.stop="remove(id)" class="text-purple-600 hover:text-purple-800 hover:bg-purple-200 w-5 h-5 flex items-center justify-center rounded-full transition-colors duration-150">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>
        </template>
    </div>

    <div class="relative">
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <i class="fas fa-search text-xs"></i>
            </span>
            <input
                type="text"
                x-model="search"
                @focus="open = true"
                @input="open = true"
                {{-- Atribut lainnya (placeholder, required, dll) --}}
                {{ $attributes->except(['options', 'selected', 'label', 'class', 'inputClass']) }}
                class="w-full pl-9 pr-3 py-2.5 text-sm font-semibold text-gray-600 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition bg-white 
                       {{ $inputClass }}" {{-- Gabungkan inputClass di sini --}}
                placeholder="{{ $placeholder }}"
            />
        </div>

        <!-- LIST DROPDOWN -->
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="absolute z-50 mt-1 w-full h-40 bg-white border border-gray-200 rounded-xl shadow-xl max-h-60 overflow-y-auto"
        >
            <template x-for="item in options.filter(i => 
                !selected.includes(i.{{ $valueField }}) && 
                String(i.{{ $labelField }}).toLowerCase().includes(search.toLowerCase())
            )" :key="item.{{ $valueField }}">
                <div
                    @click="add(item.{{ $valueField }})"
                    class="px-4 py-2 text-sm hover:bg-gray-50 cursor-pointer transition-colors border-b border-gray-50 last:border-0"
                    x-text="item.{{ $labelField }}"
                ></div>
            </template>
            {{-- Pesan Kosong --}}
            <div x-show="options.filter(i => !selected.includes(i.{{ $valueField }}) && String(i.{{ $labelField }}).toLowerCase().includes(search.toLowerCase())).length === 0"
                class="px-4 py-4 text-xs text-gray-400 text-center">
                Data tidak ditemukan.
            </div>
        </div>
    </div>

    <!-- HIDDEN INPUT -->
    <template x-for="id in selected" :key="'h-'+id">
        <input type="hidden" name="{{ $name }}[]" :value="id">
    </template>
</div>
