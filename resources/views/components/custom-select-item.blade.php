@props(['val', 'label'])

<div @click="value = '{{ $val }}'; label = '{{ $label }}'; open = false; dispatchChange()"
     x-init="if(value == '{{ $val }}') label = '{{ $label }}'"
     class="px-3 py-2 text-sm hover:bg-gray-100 cursor-pointer flex items-center gap-2 text-gray-700 transition-colors">
    {{ $slot }}
</div>
