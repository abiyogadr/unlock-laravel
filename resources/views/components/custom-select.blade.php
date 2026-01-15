@props([
    'name', 
    'label' => null, 
    'placeholder' => 'Pilih...', 
    'value' => ''
])

<div x-data="{ 
    open: false, 
    value: '{{ old($name, $value) }}',
    label: '',
    {{-- Fungsi untuk menirukan perilaku onchange pada hidden input --}}
    dispatchChange() {
        $nextTick(() => {
            const el = $refs.hiddenInput;
            if (el.onchange) el.onchange();
            el.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }
}" 
@click.away="open = false"
{{ $attributes->only('class')->merge(['class' => 'relative']) }}>
    
    @if($label)
        <label class="block text-sm font-semibold text-gray-600 mb-1">{{ $label }}</label>
    @endif

    <!-- Button Trigger -->
    <button type="button"
        @click="open = !open"
        class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-semibold text-gray-600
               border border-gray-200 shadow-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-primary transition focus:border-primary hover:bg-gray-50 cursor-pointer">
        
        <span :class="value ? 'text-gray-600' : 'text-gray-400'" 
              x-text="value ? label : '{{ $placeholder }}'">
        </span>

        <i class="fas fa-chevron-down text-xs text-gray-400 transition-transform" 
           :class="{ 'rotate-180': open }"></i>
    </button>

    <!-- Dropdown Menu -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         class="absolute z-50 mt-2 w-full bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden min-w-[150px]">
        
        {{ $slot }}
    </div>

    <!-- Hidden Input -->
    <input type="hidden" 
           name="{{ $name }}" 
           x-model="value" 
           x-ref="hiddenInput"
           {{ $attributes->except(['class', 'label', 'value', 'placeholder']) }}>
</div>
