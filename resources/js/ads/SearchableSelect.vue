<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    modelValue: { type: String, required: true },
    options:    { type: Array, required: true },
    label:      { type: String, required: true },
    placeholder: { type: String, default: 'Cari...' },
    required:   { type: Boolean, default: false },
    disabled:   { type: Boolean, default: false },
    loading:    { type: Boolean, default: false },
    emptyText:  { type: String, default: 'Tidak ada hasil' },
})

const emit = defineEmits(['update:modelValue', 'open'])

const isOpen = ref(false)
const searchQuery = ref('')
const dropdownRef = ref(null)
const dropdownId = Symbol()

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options
    const q = searchQuery.value.toLowerCase()
    return props.options.filter(opt => opt.toLowerCase().includes(q))
})

function select(value) {
    emit('update:modelValue', value)
    isOpen.value = false
    searchQuery.value = ''
}

function closeDropdown() {
    isOpen.value = false
    searchQuery.value = ''
}

function toggleDropdown() {
    if (!isOpen.value) {
        emit('open')
        window.dispatchEvent(new CustomEvent('searchable-select-open', { detail: dropdownId }))
    }
    isOpen.value = !isOpen.value
}

function handleExternalOpen(e) {
    if (e?.detail !== dropdownId) closeDropdown()
}

function handleDocumentClick(e) {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        closeDropdown()
    }
}

onMounted(() => {
    document.addEventListener('click', handleDocumentClick)
    window.addEventListener('searchable-select-open', handleExternalOpen)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', handleDocumentClick)
    window.removeEventListener('searchable-select-open', handleExternalOpen)
})
</script>

<template>
<div class="relative" ref="dropdownRef">
    <label class="block text-xs font-bold text-gray-700 mb-1.5">
        {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>

    <!-- Trigger Button -->
    <button @click.stop="toggleDropdown" :disabled="disabled"
            type="button"
            class="w-full text-left border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-purple-400 focus:ring-2 focus:ring-purple-100 transition bg-white flex items-center justify-between disabled:opacity-50 disabled:cursor-not-allowed"
            :class="{ 'border-purple-300 ring-2 ring-purple-100': isOpen }">
        <span :class="{ 'text-gray-400': !modelValue, 'text-gray-800 font-medium': modelValue }">
            {{ modelValue || placeholder }}
        </span>
        <i class="fas fa-chevron-down text-xs text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': isOpen }"></i>
    </button>

    <!-- Dropdown Menu -->
    <Transition name="dropdown">
        <div v-if="isOpen" @click.stop class="absolute top-full left-0 right-0 z-[100] mt-1 border border-gray-200 rounded-xl bg-white shadow-lg overflow-hidden">
            <!-- Search Input -->
            <div class="border-b border-gray-100 p-2">
                <input v-model="searchQuery" type="text" :placeholder="placeholder"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-purple-400 transition bg-gray-50"
                       @click.stop @keydown.esc="closeDropdown" autofocus>
            </div>

            <!-- Options List -->
            <div class="max-h-[200px] overflow-y-auto">
                <div v-if="loading" class="w-full text-left px-4 py-3 text-xs text-gray-400 bg-gray-50">
                    <i class="fas fa-circle-notch fa-spin mr-2 text-gray-300"></i> Memuat data...
                </div>
                <button v-else-if="filteredOptions.length === 0"
                        type="button"
                        class="w-full text-left px-4 py-3 text-xs text-gray-400 hover:bg-gray-50 transition"
                        @click.stop>
                    <i class="fas fa-search mr-2 text-gray-300"></i> {{ emptyText }}
                </button>
                <button v-for="opt in filteredOptions" :key="opt"
                        type="button"
                        @click="select(opt)"
                        class="w-full text-left px-4 py-3 text-sm transition hover:bg-purple-50 flex items-center gap-2.5"
                        :class="{ 'bg-purple-100 text-purple-700 font-semibold': modelValue === opt, 'text-gray-700': modelValue !== opt }">
                    <i v-if="modelValue === opt" class="fas fa-check text-purple-600 text-xs flex-shrink-0"></i>
                    <span v-else class="w-4"></span>
                    {{ opt }}
                </button>
            </div>
        </div>
    </Transition>
</div>
</template>

<style scoped>
.dropdown-enter-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.dropdown-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.dropdown-enter-from   { opacity: 0; transform: translateY(-6px); }
.dropdown-leave-to     { opacity: 0; transform: translateY(-6px); }
</style>
