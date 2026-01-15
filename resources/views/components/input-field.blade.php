@props([
    'name', 
    'label' => null, 
    'type' => 'text', 
    'icon' => null,
    'prefix' => null,
    'suffix' => null,
    'value' => '', 
    'showError' => true,
    'inputClass' => ''
])

@php
    // Logika Padding Kiri (Hanya jika ada Prefix atau Ikon)
    $baseLeftPadding = $icon ? 2.5 : 1;
    $prefixExtra = $prefix ? (strlen($prefix) * 0.5) + 0.25 : 0;
    $finalLeftPadding = $baseLeftPadding + $prefixExtra;

    // Logika Padding Kanan (Hanya jika ada Suffix)
    // Jika suffix berupa tombol "Verify Now", kita butuh sekitar 6rem (96px)
    $finalRightPadding = $suffix ? 6.5 : 1;
@endphp

<div {{ $attributes->only('class') }}>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-gray-600 mb-1">
            {{ $label }}
            @if($attributes->has('required')) <span class="text-red-500">*</span> @endif
        </label>
    @endif

    <div class="relative flex items-center">
        {{-- Ikon Sisi Kiri --}}
        @if($icon)
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 z-10">
                <i class="{{ $icon }} text-sm"></i>
            </div>
        @endif

        {{-- Prefix Sisi Kiri --}}
        @if($prefix)
            <span class="absolute {{ $icon ? 'left-10' : 'left-4' }} text-sm font-semibold text-gray-500 pointer-events-none z-10">
                {{ $prefix }}
            </span>
        @endif

        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            id="{{ $name }}"
            value="{{ $value }}" 
            {{ $attributes->except(['class', 'inputClass']) }}
            style="padding-left: {{ $finalLeftPadding }}rem; padding-right: {{ $finalRightPadding }}rem;"
            class="w-full py-2.5 text-sm font-semibold text-gray-600 shadow-sm rounded-xl border transition focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary hover:bg-gray-50
                   {{ $errors->has($name) ? 'border-red-500' : 'border-gray-200' }} 
                   {{ $inputClass }}"
        >

        {{-- Suffix Sisi Kanan --}}
        @if($suffix)
            <div class="absolute inset-y-0 right-0 flex items-center pr-2 z-10">
                {{ $suffix }}
            </div>
        @endif
    </div>
    
    @if($showError)
        @error($name) 
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
        @enderror
    @endif
</div>
