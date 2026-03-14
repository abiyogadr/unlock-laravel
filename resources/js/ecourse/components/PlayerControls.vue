<template>
  <div class="player-controls group absolute bottom-0 left-0 right-0 z-40">
    <!-- Progress Bar -->
    <div class="w-full h-1 bg-gray-600/30 transition-all"
         :class="ENABLE_VIDEO_SEEK ? 'cursor-pointer hover:h-1.5' : 'cursor-default'"
         @click.stop="seekTo"
         @mousemove="handleProgressHover"
         @mouseleave="handleProgressLeave"
    >
      <div class="h-full bg-primary transition-all" :style="{ width: progressPercent + '%' }"></div>
      <div v-if="hoveredTime !== null" class="absolute top-0 h-full w-1 bg-white/70 opacity-0 group-hover:opacity-100 transition-opacity"
           :style="{ left: hoveredPercent + '%', transform: 'translateX(-50%)' }"
      ></div>
    </div>

    <!-- Control Buttons -->
    <div class="flex items-center justify-between bg-gradient-to-t from-black/80 to-transparent px-3 py-3 transition-all duration-200">
      <!-- Left Controls: Play, Volume -->
      <div class="flex items-center gap-3">
        <!-- Play/Pause Button -->
        <button @click.stop="togglePlayPause" class="text-white hover:text-primary transition p-1 rounded hover:bg-white/10 cursor-pointer"
                :title="isPlaying ? 'Pause' : 'Play'">
          <i :class="['fas text-lg', isPlaying ? 'fa-pause' : 'fa-play']"></i>
        </button>

        <!-- Volume Control -->
        <div class="flex items-center gap-1">
          <button @click.stop="toggleMute" class="text-white hover:text-primary transition p-1 rounded hover:bg-white/10 cursor-pointer"
                  :title="isMuted ? 'Unmute' : 'Mute'">
            <i :class="['fas text-lg', isMuted || volume === 0 ? 'fa-volume-xmark' : 'fa-volume-high']"></i>
          </button>
        </div>

        <!-- Time Display -->
        <span class="text-white text-xs font-semibold min-w-16">
          {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
        </span>
      </div>

      <!-- Right Controls: Quality, Fullscreen -->
      <div class="flex items-center gap-2">
        <!-- Quality Selector (for YouTube) -->
        <div v-if="isYouTube && availableQualitiesInternal.length > 0" class="relative group/quality">
          <button @click.stop="toggleQualityMenu" class="text-white hover:text-primary transition p-1 rounded hover:bg-white/10 cursor-pointer"
                  title="Video Quality">
            <i class="fas fa-cog text-lg"></i>
          </button>
          <div v-if="showQualityMenu" class="absolute bottom-full right-0 mb-2 bg-gray-900/95 rounded shadow-lg overflow-hidden min-w-32">
            <button v-for="q in availableQualitiesInternal" :key="q"
                    @click.stop="setQuality(q)"
                    :class="['w-full px-3 py-2 text-left text-sm text-white hover:bg-primary/60 transition', 
                             q === currentQualityInternal ? 'bg-primary' : 'bg-gray-800']">
              {{ q }}
            </button>
          </div>
        </div>

        <!-- Playback Speed Selector -->
        <div class="relative group/speed">
          <button @click.stop="toggleSpeedMenu" class="text-white hover:text-primary transition p-1 rounded hover:bg-white/10 cursor-pointer"
                  title="Playback Speed">
            <span class="text-sm">{{ props.speed }}x</span>
          </button>
          <div v-if="showSpeedMenu" class="absolute bottom-full right-0 mb-2 bg-gray-900/95 rounded shadow-lg overflow-hidden min-w-32">
            <button v-for="s in speedOptions" :key="s"
                    @click.stop="setSpeed(s)"
                    :class="['w-full px-3 py-2 text-left text-sm text-white hover:bg-primary/60 transition', 
                             s === props.speed ? 'bg-primary' : 'bg-gray-800']">
              {{ s }}x
            </button>
          </div>
        </div>

        <!-- Fullscreen Button -->
        <button @click.stop="toggleFullscreen" class="text-white hover:text-primary transition p-1 rounded hover:bg-white/10 cursor-pointer"
                :title="isFullscreen ? 'Exit Fullscreen' : 'Fullscreen'">
          <i :class="['fas text-lg', isFullscreen ? 'fa-compress' : 'fa-expand']"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  playerElement: HTMLElement,
  youtubePlayer: Object,
  duration: Number,
  currentTime: Number,
  isPlaying: Boolean,
  isYouTube: Boolean,
  speed: {
    type: Number,
    default: 1,
  },
  availableQualities: {
    type: Array,
    default: () => []
  },
  currentQuality: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['play', 'pause', 'volumeChange', 'qualityChange', 'fullscreen', 'seek', 'speedChange'])

const volume = ref(100)
const isMuted = ref(false)
const showQualityMenu = ref(false)
const showSpeedMenu = ref(false)
const isFullscreen = ref(false)
const hoveredTime = ref(null)

// Toggle seek bar interactivity via env (Vite): VITE_ENABLE_VIDEO_SEEK=true
const ENABLE_VIDEO_SEEK = import.meta.env.VITE_ENABLE_VIDEO_SEEK === 'true'

const speedOptions = [0.5, 0.75, 1, 1.25, 1.5, 2]

// reactive copies of prop values for dropdown
const availableQualitiesInternal = ref([])
const currentQualityInternal = ref(null)

// sync prop values into internal refs
watch(() => props.availableQualities, (list) => {
  availableQualitiesInternal.value = list || []
})
watch(() => props.currentQuality, (q) => {
  currentQualityInternal.value = q
})

// sync quality props
watch(() => props.availableQualities, (list) => {
  availableQualitiesInternal.value = list || []
})
watch(() => props.currentQuality, (q) => {
  currentQualityInternal.value = q
})

const progressPercent = computed(() => {
  if (!props.duration || props.duration === 0) return 0
  return (props.currentTime / props.duration) * 100
})

const hoveredPercent = computed(() => {
  if (!props.duration || props.duration === 0) return 0
  return (hoveredTime.value / props.duration) * 100
})

function formatTime(seconds) {
  if (!seconds || Number.isNaN(seconds)) return '0:00'

  const total = Math.floor(seconds)
  const hours = Math.floor(total / 3600)
  const mins = Math.floor((total % 3600) / 60)
  const secs = total % 60

  if (hours > 0) {
    return `${hours}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
  }

  return `${mins}:${secs.toString().padStart(2, '0')}`
}

function togglePlayPause() {
  if (props.youtubePlayer) {
    if (props.isPlaying) {
      props.youtubePlayer.pauseVideo?.()
    } else {
      props.youtubePlayer.playVideo?.()
    }
  }
}

function toggleMute() {
  if (props.youtubePlayer) {
    if (isMuted.value) {
      props.youtubePlayer.unMute?.()
      isMuted.value = false
    } else {
      props.youtubePlayer.mute?.()
      isMuted.value = true
    }
  }
}

function setVolume(event) {
  const val = parseInt(event.target.value)
  volume.value = val
  if (props.youtubePlayer) {
    props.youtubePlayer.setVolume?.(val)
    if (val > 0) {
      isMuted.value = false
    }
  }
  emit('volumeChange', val)
}

function toggleQualityMenu() {
  showQualityMenu.value = !showQualityMenu.value
}

function setQuality(quality) {
  currentQualityInternal.value = quality
  showQualityMenu.value = false
  emit('qualityChange', quality)
}

function toggleFullscreen() {
  const elem = props.playerElement
  if (!elem) return

  if (!isFullscreen.value) {
    if (elem.requestFullscreen) {
      elem.requestFullscreen().catch(() => {})
    } else if (elem.webkitRequestFullscreen) {
      elem.webkitRequestFullscreen()
    } else if (elem.mozRequestFullScreen) {
      elem.mozRequestFullScreen()
    }
    isFullscreen.value = true
  } else {
    if (document.fullscreenElement) {
      document.exitFullscreen().catch(() => {})
    }
    isFullscreen.value = false
  }
  emit('fullscreen', isFullscreen.value)
}

function toggleSpeedMenu() {
  showSpeedMenu.value = !showSpeedMenu.value
}

function setSpeed(speed) {
  showSpeedMenu.value = false
  emit('speedChange', speed)
}
function handleProgressHover(event) {
  const rect = event.currentTarget.getBoundingClientRect()
  const percent = (event.clientX - rect.left) / rect.width
  // write to hoveredTime (ref) instead of computed hoveredPercent
  hoveredTime.value = percent * props.duration
}

function handleProgressLeave() {
  hoveredTime.value = null
}

function seekTo(event) {
  const rect = event.currentTarget.getBoundingClientRect()
  const percent = (event.clientX - rect.left) / rect.width
  const time = percent * props.duration

  // When seeking is disabled, only allow rewinding (clicking behind current time)
  if (!ENABLE_VIDEO_SEEK && time > props.currentTime) return

  if (props.youtubePlayer) {
    props.youtubePlayer.seekTo?.(time, true)
  }
  emit('seek', time)
}

// Handle fullscreen change events
function handleFullscreenChange() {
  isFullscreen.value = !!document.fullscreenElement
}

onMounted(() => {
  document.addEventListener('fullscreenchange', handleFullscreenChange)
})

onBeforeUnmount(() => {
  document.removeEventListener('fullscreenchange', handleFullscreenChange)
})
</script>

<style scoped>
.player-controls {
  font-family: 'Font Awesome 6 Free', sans-serif;
}

input[type='range']::-webkit-slider-thumb {
  width: 12px;
  height: 12px;
  -webkit-appearance: none;
  appearance: none;
  background: currentColor;
  border-radius: 50%;
  cursor: pointer;
}

input[type='range']::-moz-range-thumb {
  width: 12px;
  height: 12px;
  background: currentColor;
  border-radius: 50%;
  cursor: pointer;
  border: none;
}

/* Vertical slider styling */
input[type='range'][style*='writing-mode']::-webkit-slider-thumb {
  width: 12px;
  height: 12px;
}

input[type='range'][style*='writing-mode']::-webkit-slider-runnable-track {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 4px;
}

input[type='range'][style*='writing-mode']::-moz-range-track {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 4px;
}
</style>
