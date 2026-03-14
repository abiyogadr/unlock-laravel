<template>
  <transition name="toast-fade">
    <div v-if="visible" :class="['fixed left-1/2 -translate-x-1/2 top-20 md:right-6 md:left-auto md:translate-x-0 z-50 max-w-sm w-full rounded-lg shadow-lg p-4 flex items-start gap-3', typeClass]">
      <div class="flex-shrink-0">
        <i :class="iconClass"></i>
      </div>
      <div class="flex-1">
        <p class="text-sm font-semibold">{{ title }}</p>
        <p v-if="message" class="text-xs text-gray-700 mt-1">{{ message }}</p>
        <div v-if="$slots.default" class="mt-3">
          <slot />
        </div>
      </div>
      <button @click="close" class="ml-3 text-gray-600 hover:text-gray-900">
        <i class="fas fa-times"></i>
      </button>
    </div>
  </transition>
</template>

<script setup>
import { computed, watch, ref } from 'vue'
const props = defineProps({
  modelValue: { type: Boolean, required: true },
  title: { type: String, default: 'Info' },
  message: { type: String, default: '' },
  type: { type: String, default: 'info' },
  duration: { type: Number, default: 4000 },
})

const emit = defineEmits(['update:modelValue', 'close'])
const visible = ref(props.modelValue)

watch(() => props.modelValue, (v) => { visible.value = v })
watch(visible, (v) => emit('update:modelValue', v))

function close() {
  visible.value = false
  emit('close')
}

let timer = null
watch(visible, (v) => {
  if (v && props.duration > 0) {
    clearTimeout(timer)
    timer = setTimeout(() => close(), props.duration)
  } else {
    clearTimeout(timer)
  }
})

const typeClass = computed(() => {
  switch (props.type) {
    case 'success': return 'bg-green-50 border border-green-200'
    case 'error': return 'bg-red-50 border border-red-200'
    case 'warning': return 'bg-yellow-50 border border-yellow-200'
    default: return 'bg-white border border-slate-200'
  }
})

const iconClass = computed(() => {
  switch (props.type) {
    case 'success': return 'fas fa-check-circle text-green-600 text-lg'
    case 'error': return 'fas fa-exclamation-circle text-red-600 text-lg'
    case 'warning': return 'fas fa-exclamation-triangle text-yellow-600 text-lg'
    default: return 'fas fa-info-circle text-primary text-lg'
  }
})
</script>

<style scoped>
.toast-fade-enter-active, .toast-fade-leave-active {
  transition: all 0.2s ease;
}
.toast-fade-enter-from, .toast-fade-leave-to {
  transform: translateY(10px);
  opacity: 0;
}
</style>