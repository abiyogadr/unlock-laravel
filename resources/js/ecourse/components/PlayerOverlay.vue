<template>
  <div v-if="internalVisible" 
       @click.prevent="handleClick"
       class="absolute inset-0 z-10 flex items-center justify-center bg-black/70 backdrop-blur-sm transition-opacity duration-500"
       :class="{ 'opacity-0 pointer-events-none': isFading }">
      <!-- Overlay Content -->
      <div class="flex flex-col items-center justify-center gap-2 md:gap-6 px-4 py-2 md:py-6 text-center max-w-md w-full">
        <!-- Icon -->
        <div class="hidden md:flex self-center w-16 h-16 rounded-full bg-white/10 flex items-center justify-center animate-pulse">
          <i :class="['fas text-3xl text-white', icon]"></i>
        </div>

        <!-- Status Text -->
        <div>
          <h2 class="text-2xl font-bold text-white mb-2">{{ title }}</h2>
          <p class="text-white/80 text-sm leading-relaxed">{{ message }}</p>
        </div>

        <!-- Action Buttons (optional) -->
        <slot name="actions">
          <button @click.prevent="handleClick"
                  class="px-6 py-2 bg-primary hover:bg-primary/80 text-white font-semibold rounded-lg transition-colors">
            {{ actionLabel }}
          </button>
        </slot>

        <!-- Loading Indicator (optional) -->
        <div v-if="showLoading" class="flex gap-1 justify-center mt-4">
          <div class="w-1 h-1 rounded-full bg-white/60 animate-bounce" style="animation-delay: 0s"></div>
          <div class="w-1 h-1 rounded-full bg-white/60 animate-bounce" style="animation-delay: 0.2s"></div>
          <div class="w-1 h-1 rounded-full bg-white/60 animate-bounce" style="animation-delay: 0.4s"></div>
        </div>
      </div>

      <!-- Mobile swipe hint -->
      <div class="absolute bottom-6 left-0 right-0 flex justify-center text-white/50 text-xs md:hidden">
        <span>Ketuk untuk melanjutkan</span>
      </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits, ref, watch, onBeforeUnmount } from 'vue'

const props = defineProps({
  isVisible: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Video Paused',
  },
  message: {
    type: String,
    default: 'Click to continue watching',
  },
  icon: {
    type: String,
    default: 'fa-pause',
  },
  actionLabel: {
    type: String,
    default: 'Lanjutkan',
  },
  showLoading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['click', 'close'])

// Internal visibility to allow delayed hide when props.isVisible -> false
const internalVisible = ref(props.isVisible)
const isFading = ref(false)
let fadeTimer = null
let hideTimer = null

// Watch prop changes and implement 3s delay on hide
watch(() => props.isVisible, (val) => {
  if (val) {
    // Immediately show and cancel any pending timers
    if (fadeTimer) { clearTimeout(fadeTimer); fadeTimer = null }
    if (hideTimer) { clearTimeout(hideTimer); hideTimer = null }
    isFading.value = false
    internalVisible.value = true
  } else {
    // Start fade shortly before hiding to have smooth transition
    // Fade starts at 2.5s, hide at 3s
    if (fadeTimer) clearTimeout(fadeTimer)
    if (hideTimer) clearTimeout(hideTimer)
    fadeTimer = setTimeout(() => { isFading.value = true }, 700)
    hideTimer = setTimeout(() => { internalVisible.value = false; isFading.value = false; fadeTimer = null; hideTimer = null }, 1000)
  }
})

onBeforeUnmount(() => {
  if (fadeTimer) clearTimeout(fadeTimer)
  if (hideTimer) clearTimeout(hideTimer)
})

function handleClick() {
  emit('click')
  emit('close')
}
</script>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.fa-pause {
  animation: fadeIn 0.3s ease-in-out;
}
</style>
