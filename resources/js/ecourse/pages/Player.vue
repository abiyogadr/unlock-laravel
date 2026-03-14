<template>
  <div class="min-h-screen bg-black">
    <!-- Toast -->
    <Toast v-model="toastVisible" :title="toastTitle" :message="toastMessage" :type="toastType">
      <template #default>
        <div class="flex items-center gap-2">
          <button v-if="nextModuleUrl" @click.prevent="gotoNextModule" class="px-3 py-1 bg-primary text-white rounded-md text-sm">Lanjut ke Modul Berikutnya</button>
          <button v-else-if="nextModule" @click.prevent="gotoNextModule" class="px-3 py-1 bg-primary text-white rounded-md text-sm">Lihat Modul Berikutnya</button>
        </div>
      </template>
    </Toast>

    <!-- PDF Module Viewer -->
    <div v-if="isPdfModule" class="max-w-4xl mx-auto bg-gray-100">
      <div v-if="pdfSource" class="relative">
        <iframe
          :src="pdfSource"
          class="w-full border-0"
          style="min-height: 80vh;"
          @load="onPdfLoaded"
        ></iframe>
      </div>
      <div v-else class="flex items-center justify-center bg-gray-200" style="min-height: 40vh;">
        <div class="text-center text-gray-500">
          <i class="fas fa-file-pdf text-5xl text-red-400 mb-4 block"></i>
          <p class="font-semibold">File PDF tidak tersedia</p>
        </div>
      </div>
      <!-- PDF Complete Button -->
      <div v-if="!isModuleCompleted" class="flex justify-center p-4 bg-white border-t border-gray-200">
        <button
          @click="markPdfComplete"
          :disabled="isCompleting"
          class="px-6 py-2 bg-primary text-white rounded-lg font-semibold hover:bg-purple-900 transition disabled:opacity-50 cursor-pointer"
        >
          <i class="fas fa-check mr-2"></i>
          {{ isCompleting ? 'Menyimpan...' : 'Tandai Selesai Dibaca' }}
        </button>
      </div>
      <div v-else class="flex justify-center p-4 bg-white border-t border-gray-200">
        <span class="flex items-center gap-2 text-green-600 font-semibold">
          <i class="fas fa-check-circle"></i> Modul selesai
        </span>
      </div>
    </div>

    <!-- Video Container -->
    <div v-else ref="playerContainer" class="relative aspect-video bg-black max-w-4xl mx-auto overflow-hidden">
      <!-- YouTube Player -->
      <div v-if="isYouTube" class="w-full h-full relative">
        <iframe
          id="youtube-player-element"
          class="w-full h-full"
          :src="`https://www.youtube.com/embed/${youTubeId}?enablejsapi=1&rel=0&modestbranding=1&controls=0&iv_load_policy=3&disablekb=1&origin=${origin}&playsinline=1&autoplay=0&mute=0`"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen
        ></iframe>
        
        <!-- Overlay for pause/end (positioned relative to video, not fullscreen) -->
        <PlayerOverlay 
          :isVisible="playerOverlayVisible"
          :title="overlayTitle"
          :message="overlayMessage"
          :icon="overlayIcon"
          actionLabel="Lanjutkan"
          @click="handleOverlayClick"
        />
        
        <!-- Custom Player Controls -->
        <PlayerControls 
          :playerElement="playerContainer"
          :youtubePlayer="youTubePlayer"
          :duration="videoDuration"
          :currentTime="currentVideoTime"
          :isPlaying="isVideoPlaying"
          :isYouTube="true"
          @seek="handleSeek"
          @play="() => { isVideoPlaying = true; playerOverlayVisible = false }"
          @pause="() => { isVideoPlaying = false; playerOverlayVisible = true; overlayTitle = 'Video Dijeda'; overlayMessage = 'Klik untuk melanjutkan menonton'; overlayIcon = 'fa-pause' }"
          @volumeChange="handleVolumeChange"
          @qualityChange="handleQualityChange"
          @speedChange="handleSpeedChange"
          @fullscreen="handleFullscreenChange"
          :availableQualities="availableQualities"
          :currentQuality="currentQuality"
          :speed="playbackSpeed"
        />
      </div>

      <!-- HTML5 Video Player -->
      <video
        v-else
        :id="`course-video-${moduleId}`"
        ref="videoElement"
        class="w-full h-full"
        controlsList="nodownload"
        disablepictureinpicture
        @contextmenu.prevent
        @play="isVideoPlaying = true"
        @pause="isVideoPlaying = false"
        @ended="handleHtml5Ended"
        @timeupdate="handleHtml5TimeUpdate"
      >
        <source :src="videoSource" type="video/mp4">
        Browser Anda tidak mendukung tag video.
      </video>

      <!-- Overlay for HTML5 video -->
      <PlayerOverlay 
        :isVisible="playerOverlayVisible"
        :title="overlayTitle"
        :message="overlayMessage"
        :icon="overlayIcon"
        actionLabel="Lanjutkan"
        @click="handleOverlayClick"
      />

      <!-- Custom Controls for HTML5 (optional) -->
      <PlayerControls 
        v-if="!isYouTube"
        :playerElement="playerContainer"
        :duration="videoDuration"
        :currentTime="currentVideoTime"
        :isPlaying="isVideoPlaying"
        :isYouTube="false"
        @seek="handleSeek"
        @play="() => { isVideoPlaying = true; playerOverlayVisible = false }"
        @pause="() => { isVideoPlaying = false; playerOverlayVisible = true; overlayTitle = 'Video Dijeda'; overlayMessage = 'Klik untuk melanjutkan menonton'; overlayIcon = 'fa-pause' }"
        @volumeChange="handleVolumeChange"
        @speedChange="handleSpeedChange"
        @fullscreen="handleFullscreenChange"
        :speed="playbackSpeed"
      />

      <!-- Preview Overlay -->
      <div v-if="showPreviewOverlay" class="absolute inset-0 z-30 flex items-center justify-center bg-black/60 p-4">
        <div class="max-w-3xl w-full bg-white rounded-xl overflow-hidden shadow-lg">
          <div class="flex flex-col md:flex-row">
            <div class="md:w-1/3 bg-black h-48 flex items-center justify-center relative overflow-hidden">
              <div id="preview-container" class="w-full h-full"></div>
              <img v-if="!previewPlaying && nextModule?.thumbnail_url" :src="nextModule.thumbnail_url" class="absolute inset-0 w-full h-full object-cover" />
              <button v-if="!previewPlaying" @click="playPreviewVideo" class="absolute inset-0 m-auto w-12 h-12 bg-white/90 rounded-full flex items-center justify-center text-primary shadow-lg">
                <i class="fas fa-play"></i>
              </button>
            </div>
            <div class="p-4 md:w-2/3">
              <h4 class="text-lg font-bold mb-2">Preview: {{ nextModule ? nextModule.title : '' }}</h4>
              <p class="text-sm text-gray-600 mb-4">{{ nextModule ? nextModule.short_description : '' }}</p>
              <div class="flex items-center gap-3">
                <button @click="playPreviewVideo" :disabled="previewPlaying" class="px-4 py-2 bg-primary text-white rounded-lg font-semibold">Putar Preview</button>
                <Link :href="nextModule ? `/ecourse/player/${course.slug}/${nextModule.slug}` : '# '" class="px-4 py-2 bg-white border rounded-lg font-semibold">Lanjut Belajar</Link>
                <button @click="stopPreview" class="px-3 py-2 text-sm text-gray-600">Tutup</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Content Below Video -->
    <div class="bg-white min-h-screen" v-if="course && module">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6 max-w-7xl">
        <div class="grid lg:grid-cols-3 gap-8">
          <!-- Main Content -->
          <div class="lg:col-span-2">
            <!-- Course Header -->
            <div class="mb-2">
              <h1 class="text-lg font-bold text-gray-800 mb-2">{{ course.title }}</h1>
              <h1 class="text-lg font-bold text-gray-800 mb-2">{{ module.title }}</h1>
              <p class="text-sm text-gray-600">Modul {{ module.order_num }} - {{ module.title }}</p>
            </div>

            <!-- Module Tabs -->
            <div class="border-b border-gray-200 mb-6">
              <nav class="flex space-x-8">
                <button 
                  @click="activeTab = 'overview'"
                  :class="['tab-btn text-sm py-4 font-semibold cursor-pointer', activeTab === 'overview' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700']"
                >
                  Overview
                </button>
                <button 
                  @click="activeTab = 'materials'"
                  :class="['tab-btn text-sm py-4 font-semibold cursor-pointer', activeTab === 'materials' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700']"
                >
                  Materi
                </button>
                <!-- <button 
                  @click="activeTab = 'discussion'"
                  :class="['tab-btn text-sm py-4 font-semibold cursor-pointer', activeTab === 'discussion' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700']"
                >
                  Diskusi
                </button> -->
                <button 
                  @click="activeTab = 'certificate'"
                  :class="['tab-btn text-sm py-4 font-semibold cursor-pointer', activeTab === 'certificate' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700']"
                >
                  Sertifikat
                </button>
              </nav>
            </div>

            <!-- Tab Content -->
            <!-- Overview Tab -->
            <div v-if="activeTab === 'overview'">
              <div class="text-sm prose max-w-none mb-8" v-html="module.description"></div>

              <!-- Learning Objectives -->
              <div v-if="module.objectives && module.objectives.length > 0" class="mt-8 mb-8">
                <h3 class="text-md font-semibold text-gray-800 mb-4">Tujuan Pembelajaran</h3>
                <ul class="space-y-2">
                  <li v-for="(objective, idx) in module.objectives" :key="idx" class="flex items-start">
                    <i class="fas fa-check-circle text-primary mt-1 mr-3"></i>
                    <span class="text-sm text-gray-700">{{ objective }}</span>
                  </li>
                </ul>
              </div>
            </div>

            <!-- Materials Tab -->
            <div v-if="activeTab === 'materials'" class="space-y-3">
              <template v-if="module.materials && module.materials.length > 0">
                <div
                  v-for="material in module.materials"
                  :key="material.id"
                  class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100 hover:border-primary/30 hover:bg-primary/5 transition"
                >
                  <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-white border border-gray-200 flex items-center justify-center shadow-sm">
                    <i :class="[material.icon || 'fas fa-file', material.type_color || 'text-gray-500', 'text-xl']"></i>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm text-gray-800 truncate">{{ material.title }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                      <span class="uppercase font-medium">{{ material.type }}</span>
                      <span v-if="material.file_size" class="ml-2">· {{ material.file_size }}</span>
                    </p>
                  </div>
                  <button
                    @click="downloadMaterial(material)"
                    :disabled="isDownloading"
                    class="flex-shrink-0 px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-purple-900 transition font-semibold disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                  >
                    <i class="fas fa-download mr-1"></i> {{ isDownloading && downloadingId === material.id ? 'Mengunduh...' : 'Unduh' }}
                  </button>
                </div>
              </template>
              <div v-else class="text-center py-12 text-gray-400">
                <i class="fas fa-folder-open text-4xl mb-3 block"></i>
                <p class="text-sm">Tidak ada materi untuk modul ini</p>
              </div>
            </div>

            <!-- Discussion Tab -->
            <!-- <div v-if="activeTab === 'discussion'" class="space-y-6">
              <div class="bg-gray-50 p-4 rounded-lg">
                <textarea 
                  v-model="commentText"
                  placeholder="Tanyakan sesuatu tentang modul ini..." 
                  class="w-full p-3 border border-gray-200 rounded-lg resize-none" 
                  rows="3"
                ></textarea>
                <button 
                  @click="submitComment"
                  :disabled="isSubmittingComment"
                  class="mt-2 px-6 py-2 bg-primary text-white rounded-lg hover:bg-purple-900 transition font-semibold disabled:opacity-50"
                >
                  {{ isSubmittingComment ? 'Mengirim...' : 'Kirim Pertanyaan' }}
                </button>
              </div>
              <p class="text-gray-500 text-center">Fitur diskusi belum tersedia untuk modul ini.</p>
            </div> -->

            <!-- Certificate Tab -->
            <div v-if="activeTab === 'certificate'" class="space-y-6">
              <div v-if="certLoading" class="text-center py-8">Memeriksa sertifikat...</div>

              <div v-else>
                <div v-if="certData" class="bg-primary/5 border border-primary/10 p-4 rounded-lg text-center">
                  <div class="font-semibold text-primary mb-2">Sertifikat telah terbit</div>
                  <div class="text-sm text-gray-700 mb-3">Diterbitkan: {{ certData.issued_at }}</div>
                  <div>
                    <button @click="showCertModal = true" class="px-4 py-2 bg-primary text-white rounded-lg font-semibold cursor-pointer">Lihat</button>
                  </div>
                </div>

                <div v-else class="bg-gray-50 p-4 rounded-lg text-center">
                  <div v-if="certIsCompleted">
                    <div v-if="certCanGenerate" class="space-y-3">
                      <div class="text-gray-700">Course sudah selesai dan Anda memiliki kuota sertifikat.</div>
                      <div>
                        <button @click="generateCertificate" :disabled="certLoading" class="px-4 py-2 bg-primary text-white rounded-lg font-semibold cursor-pointer">{{ certLoading ? 'Membuat...' : 'Buat Sertifikat' }}</button>
                      </div>
                    </div>
                    <div v-else class="text-gray-700">
                      <div v-if="!certHasActiveSubscription">Silakan melakukan pembelian paket subscription untuk memperoleh tambahan kuota sertifikat.</div>
                      <div v-else class="">Silakan melakukan pembelian paket subscription untuk memperoleh tambahan kuota sertifikat.</div>
                    </div>
                  </div>
                  <div v-else class="text-gray-700">Belum ada sertifikat untuk course ini.</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 sticky top-4">
              <h3 class="text-lg font-bold text-gray-800 mb-6">Daftar Modul</h3>
              <ul class="space-y-2 max-h-96 overflow-y-auto">
                <li v-for="(mod, idx) in allModules" :key="mod.id">
                  <Link
                    :href="`/ecourse/player/${course.slug}/${mod.slug}`"
                    :class="[
                      'flex items-center p-3 rounded-lg transition',
                      mod.id === module.id ? 'bg-primary text-white' : 'bg-gray-50 text-gray-700 hover:bg-gray-100'
                    ]"
                  >
                    <div
                      :class="[
                        'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center mr-3',
                        mod.id === module.id ? 'bg-white/20' : 'bg-gray-200'
                      ]"
                    >
                      <i
                        v-if="mod.is_completed"
                        :class="['fas fa-check text-xs font-bold', mod.id === module.id ? 'text-white' : 'text-green-500']"
                      ></i>
                      <i
                        v-else-if="mod.module_type === 'pdf'"
                        :class="['fas fa-file-pdf text-xs', mod.id === module.id ? 'text-white' : 'text-red-400']"
                      ></i>
                      <span v-else class="text-xs font-bold">{{ mod.order_num || idx + 1 }}</span>
                    </div>
                    <div class="flex-1">
                      <div class="flex items-center justify-between">
                        <p class="font-semibold text-sm">{{ mod.title }}</p>
                        <span
                          v-if="!mod.is_completed && mod.progress > 0"
                          :class="['text-xs font-bold', mod.id === module.id ? 'text-white/80' : 'text-primary']"
                        >{{ Math.round(mod.progress) }}%</span>
                        <span
                          v-else-if="mod.is_completed"
                          :class="['text-xs font-bold', mod.id === module.id ? 'text-white/80' : 'text-green-600']"
                        >Selesai</span>
                      </div>
                      <!-- <p  :class="['text-xs', mod.id === module.id ? 'text-white/70' : 'text-gray-500']">
                        {{ mod.formatted_duration || 'Durasi tidak diketahui' }}
                      </p> -->
                    </div>
                  </Link>

                  <div v-if="!mod.is_completed && mod.progress > 0" class="px-3 py-2">
                    <div class="bg-gray-200 rounded-full h-1.5 overflow-hidden">
                      <div class="bg-primary h-full transition-all duration-300" :style="{ width: mod.progress + '%' }"></div>
                    </div>
                  </div>
                </li>
              </ul>

              <!-- Progress -->
              <!-- <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Progress E-Course</h3>
                <div class="mb-4">
                  <div class="flex justify-between text-sm text-gray-600 mb-2">
                    <span>Selesai</span>
                    <span>{{ Math.round(courseProgress) }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-primary h-3 rounded-full transition-all duration-300" :style="{ width: courseProgress + '%' }"></div>
                  </div>
                </div>
                <p class="text-sm text-gray-500">{{ completedModules }}/{{ totalModules }} modul selesai</p>
              </div> -->

              <!-- Action Status (automatic) -->
              <div class="mt-6 space-y-2">
                <Link href="/ecourse/my-journey" class="block text-center px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition font-semibold text-sm">
                  <i class="fas fa-arrow-left mr-2"></i> Kembali
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Certificate Prompt Modal (show when course complete and no certificate yet) -->
    <div v-if="showCertPrompt" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
      <div class="bg-white w-full max-w-md rounded-lg overflow-hidden shadow-lg">
        <div class="p-6">
          <h3 class="text-lg font-semibold mb-2">Sertifikat Siap Dicetak</h3>
          <p class="text-sm text-gray-600 mb-4">Kamu sudah menyelesaikan kursus ini. Ingin membuat sertifikat sekarang?</p>
          <div class="flex justify-end gap-2">
            <button @click="showCertPrompt = false" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 cursor-pointer">Nanti</button>
            <button @click="promptGenerateCertificate" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-purple-900 cursor-pointer">Cetak Sertifikat</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Certificate Modal (match Certificates.vue sizing) -->
    <div v-if="showCertModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
      <div class="bg-white w-full max-w-4xl h-[80vh] rounded-lg overflow-hidden shadow-lg">
        <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200">
          <div class="font-semibold">Preview Sertifikat</div>
          <div>
            <a :href="certData?.view_url" target="_blank" rel="noopener" class="mr-2 text-sm text-primary underline">Buka di tab baru</a>
            <button @click="showCertModal = false" class="px-3 py-1 rounded bg-gray-100 hover:bg-gray-200 cursor-pointer">Tutup</button>
          </div>
        </div>
        <iframe v-if="certIframeSrc" :src="certIframeSrc" class="w-full h-[calc(100%-48px)] border-0"></iframe>
        <div v-else class="p-6 text-center">Tidak ada sertifikat untuk ditampilkan.</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import Toast from '@/ecourse/components/Toast.vue'
import PlayerControls from '@/ecourse/components/PlayerControls.vue'
import PlayerOverlay from '@/ecourse/components/PlayerOverlay.vue'

const props = defineProps({
  course_slug: String,
  module_slug: String,
  player_data: {
    type: Object,
    required: true,
  },
})

const course = ref(props.player_data.course || {})
const module = ref(props.player_data.module || {})
const allModules = ref(props.player_data.modules || [])
const courseProgress = ref(props.player_data.progress || 0)
const completedModules = ref(props.player_data.completed_modules || 0)
const totalModules = ref(props.player_data.total_modules || allModules.value.length)
const lastSentProgress = ref(module.value?.progress || 0)

// Progress tracking
const PROGRESS_SEND_INTERVAL = 30 * 1000
const lastSentAt = ref(0)

// UI State
const activeTab = ref('overview')
const commentText = ref('')
const isSubmittingComment = ref(false)
const isCompleting = ref(false)
const isModuleCompleted = ref(module.value?.is_completed || false)

// Video state
const playerContainer = ref(null)
const videoElement = ref(null)
const videoDuration = ref(0)
const currentVideoTime = ref(0)
const isVideoPlaying = ref(false)
const playbackSpeed = ref(1)

// Toast state
const toastVisible = ref(false)
const toastTitle = ref('')
const toastMessage = ref('')
const toastType = ref('success')
const nextModuleUrl = ref(null)
const toastShownForModule = ref(null)

// Player Overlay state
const playerOverlayVisible = ref(false)
const overlayTitle = ref('Video Paused')
const overlayMessage = ref('Klik untuk melanjutkan menonton')
const overlayIcon = ref('fa-pause')

const origin = window.location.origin
let progressInterval = null
let youTubePlayer = null

// expose qualities for control component
const availableQualities = ref([])
const currentQuality = ref(null)

const showPreviewOverlay = ref(false)
const previewPlaying = ref(false)
const previewTimer = ref(null)
const previewElement = ref(null)
const PREVIEW_DURATION = 10 // seconds

// Certificate state
const certData = ref(null)
const certLoading = ref(false)
const certLoadedForCourseId = ref(null)
const certIsCompleted = ref(false)
const certHasActiveSubscription = ref(false)
const certRemaining = ref(0)
const certCanGenerate = ref(false)
const showCertModal = ref(false)

// Certificate prompt state (show when course complete + no cert yet + quota available)
const showCertPrompt = ref(false)
const certPromptShownForCourseId = ref(null)

// Download state
const isDownloading = ref(false)
const downloadingId = ref(null)



const videoPath = computed(() => module.value?.video_url || module.value?.video_path || '')

const isPdfModule = computed(() => module.value?.module_type === 'pdf')
const pdfSource = computed(() => module.value?.pdf_url || (module.value?.pdf_path ? `/storage/${module.value.pdf_path}` : ''))

const isYouTube = computed(() => {
  if (!videoPath.value) return false
  return [
    'https://www.youtube.com/',
    'https://youtube.com/',
    'https://youtu.be/',
    'http://www.youtube.com/',
    'http://youtu.be/',
    'https://youtube.com/live/',
    'http://youtube.com/live/',
  ].some(url => videoPath.value.startsWith(url))
})

const youTubeId = computed(() => {
  if (!isYouTube.value || !videoPath.value) return null
  const match = videoPath.value.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|live\/)|youtu\.be\/)([^"&?\/ ]{11})/i)
  return match ? match[1] : null
})

const videoSource = computed(() => {
  if (!videoPath.value || isYouTube.value) return ''
  return videoPath.value.startsWith('http') ? videoPath.value : `/storage/${videoPath.value}`
})

const nextModule = computed(() => {
  if (!module.value) return null
  const currentIdx = allModules.value.findIndex(m => m.id === module.value.id)
  return currentIdx < allModules.value.length - 1 ? allModules.value[currentIdx + 1] : null
})

const totalModulesComputed = computed(() => totalModules.value || allModules.value.length)
const moduleId = computed(() => module.value?.id || 'unknown')

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content

async function postJson(path, payload = {}) {
  const response = await fetch(`/ecourse/api${path}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken() || '',
      'Accept': 'application/json',
    },
    body: JSON.stringify(payload),
  })

  if (!response.ok) throw new Error('Request failed')
  return response.json().catch(() => ({}))
}

function onPdfLoaded() {
  // PDF iframe loaded — progress is sent during setupProgressTracking
}

async function markPdfComplete() {
  if (isModuleCompleted.value || isCompleting.value) return
  await maybeSendProgress(100, true)
}

async function completeModule() {
  if (!module.value || isModuleCompleted.value) return
  isCompleting.value = true
  try {
    const data = await postJson(`/module/${module.value.id}/complete`)
    // Mark module completed after successful server confirmation
    isModuleCompleted.value = true

    // Update local state for the module to show it's completed
    const idx = allModules.value.findIndex(m => m.id === module.value.id)
    if (idx !== -1) {
      allModules.value[idx].is_completed = true
      allModules.value[idx].progress = 100
    }

    // Update aggregate counters
    if (typeof completedModules.value === 'number') completedModules.value = Math.min(totalModules.value, completedModules.value + 1)
    courseProgress.value = Math.round((completedModules.value / totalModules.value) * 100)

    // Show success toast only once per module
    if (toastShownForModule.value !== module.value.id) {
      toastTitle.value = 'Modul selesai!'
      toastMessage.value = data.message || 'Selamat, kamu telah menyelesaikan modul ini.'
      toastType.value = 'success'
      nextModuleUrl.value = data.next_module_url || null
      toastVisible.value = true
      toastShownForModule.value = module.value.id
    }

    // Re-check certificate status when course progress updates (esp. when it becomes complete)
    await loadCertificate(true)
  } catch (error) {
    console.error('Failed to complete module:', error)
    // Show error toast once
    toastTitle.value = 'Gagal'
    toastMessage.value = error.message || 'Terjadi kesalahan saat menyelesaikan modul.'
    toastType.value = 'error'
    toastVisible.value = true
  } finally {
    isCompleting.value = false
  }
}

async function submitComment() {
  if (!commentText.value.trim() || !module.value) return
  isSubmittingComment.value = true
  try {
    await postJson(`/module/${module.value.id}/comment`, { content: commentText.value })
    commentText.value = ''
  } catch (error) {
    console.error('Failed to submit comment:', error)
  } finally {
    isSubmittingComment.value = false
  }
}

function gotoNextModule() {
  // Prefer server-provided next module URL, fall back to computed nextModule
  if (nextModuleUrl.value) {
    router.visit(nextModuleUrl.value)
  } else if (nextModule.value) {
    router.get(`/ecourse/player/${course.value.slug}/${nextModule.value.slug}`)
  }
  toastVisible.value = false
}

function handleOverlayClick() {
  if (youTubePlayer && typeof youTubePlayer.playVideo === 'function') {
    youTubePlayer.playVideo()
  } else if (videoElement.value) {
    videoElement.value.play()
  }
  
  // Hide other video elements first (preview overlay, etc)
  showPreviewOverlay.value = false
  playerOverlayVisible.value = false
}

function handleSeek(time) {
  currentVideoTime.value = time
  if (videoElement.value) {
    videoElement.value.currentTime = time
  }
}

function handleVolumeChange(vol) {
  if (youTubePlayer && typeof youTubePlayer.setVolume === 'function') {
    youTubePlayer.setVolume(vol)
  } else if (videoElement.value) {
    videoElement.value.volume = vol / 100
  }
}

function handleQualityChange(q) {
  currentQuality.value = q
  if (youTubePlayer && typeof youTubePlayer.setPlaybackQuality === 'function') {
    youTubePlayer.setPlaybackQuality(q)
  }
}

function handleSpeedChange(speed) {
  playbackSpeed.value = speed

  // Apply to YouTube if present
  if (youTubePlayer && typeof youTubePlayer.setPlaybackRate === 'function') {
    youTubePlayer.setPlaybackRate(speed)
  }

  // Apply to HTML5 video element if present
  if (videoElement.value) {
    videoElement.value.playbackRate = speed
  }
}

function handleFullscreenChange(isFullscreen) {
  // Fullscreen handled by PlayerControls component
  if (playerContainer.value) {
    if (isFullscreen) {
      // PlayerControls handles requestFullscreen
    }
  }
}



function teardownProgressTracking() {
  if (progressInterval) {
    clearInterval(progressInterval)
    progressInterval = null
  }

  const videoEl = document.getElementById(`course-video-${moduleId.value}`)
  if (videoEl) {
    videoEl.removeEventListener('timeupdate', handleHtml5TimeUpdate)
    videoEl.removeEventListener('ended', handleHtml5Ended)
    videoEl.removeEventListener('pause', handleHtml5Pause)
    videoEl.removeEventListener('play', handleHtml5Play)
  }

  // Stop preview if active
  if (previewTimer.value) {
    clearTimeout(previewTimer.value)
    previewTimer.value = null
  }
  const previewContainer = document.getElementById('preview-container')
  if (previewContainer) {
    while (previewContainer.firstChild) previewContainer.removeChild(previewContainer.firstChild)
  }
  previewPlaying.value = false
  showPreviewOverlay.value = false

  if (youTubePlayer && typeof youTubePlayer.destroy === 'function') {
    try {
      youTubePlayer.destroy()
    } catch (e) {
      console.warn('Error destroying YouTube player:', e)
    }
  }
  youTubePlayer = null
}

async function setupProgressTracking() {
  teardownProgressTracking()

  if (!module.value || !module.value.id) return

  await nextTick()

  // PDF modules: no video tracking needed; auto-complete on first open
  if (isPdfModule.value) {
    if (!isModuleCompleted.value) {
      await maybeSendProgress(100, true)
    }
    return
  }

  if (isYouTube.value) {
    await initYouTubePlayer()
  } else {
    attachHtml5Tracking()
  }
}

function attachHtml5Tracking() {
  const videoEl = document.getElementById(`course-video-${moduleId.value}`)
  if (!videoEl) return

  // Resume progress
  const duration = (module.value?.duration_minutes || 0) * 60
  if (duration > 0 && lastSentProgress.value > 0 && lastSentProgress.value < 100) {
    videoEl.currentTime = (lastSentProgress.value / 100) * duration
  }

  videoEl.addEventListener('timeupdate', handleHtml5TimeUpdate)
  videoEl.addEventListener('ended', handleHtml5Ended)
  videoEl.addEventListener('pause', handleHtml5Pause)
  videoEl.addEventListener('play', handleHtml5Play)
}

function handleHtml5TimeUpdate(event) {
  const videoEl = event.target
  if (!videoEl.duration || Number.isNaN(videoEl.duration)) return

  currentVideoTime.value = videoEl.currentTime
  videoDuration.value = videoEl.duration

  const percent = (videoEl.currentTime / videoEl.duration) * 100
  maybeSendProgress(percent)
}

function handleHtml5Ended() {
  // Force send progress at the end to ensure completion flow runs
  maybeSendProgress(100, true)
  const videoEl = document.getElementById(`course-video-${moduleId.value}`)
  if (videoEl && !videoEl.paused) videoEl.pause()
  
  // Show pause overlay
  playerOverlayVisible.value = true
  overlayTitle.value = 'Video Selesai'
  overlayMessage.value = 'Selamat! Kamu telah menyelesaikan video ini.'
  overlayIcon.value = 'fa-check-circle'
  
  if (nextModule.value) {
    showPreviewOverlay.value = true
    // Auto-play preview like YouTube if it's HTML5
    playPreviewVideo()
  }
}

function handleHtml5Play() {
  isVideoPlaying.value = true
  playerOverlayVisible.value = false
}

function handleHtml5Pause() {
  isVideoPlaying.value = false
  playerOverlayVisible.value = true
  overlayTitle.value = 'Video Dijeda'
  overlayMessage.value = 'Klik untuk melanjutkan menonton'
  overlayIcon.value = 'fa-pause'
}

function ensureYouTubeApi() {
  return new Promise((resolve) => {
    if (window.YT && window.YT.Player) {
      resolve()
      return
    }

    const previousOnReady = window.onYouTubeIframeAPIReady
    window.onYouTubeIframeAPIReady = () => {
      if (previousOnReady) previousOnReady()
      resolve()
    }

    if (!document.getElementById('youtube-iframe-api')) {
      const tag = document.createElement('script')
      tag.src = 'https://www.youtube.com/iframe_api'
      tag.id = 'youtube-iframe-api'
      document.body.appendChild(tag)
    } else if (window.YT && window.YT.initialized) {
        resolve()
    }
  })
}

function playPreviewVideo() {
  if (!nextModule.value) return
  if (previewPlaying.value) return

  try { if (youTubePlayer && youTubePlayer.pauseVideo) youTubePlayer.pauseVideo() } catch(e) {}

  previewPlaying.value = true
  const previewContainer = document.getElementById('preview-container')
  if (!previewContainer) return

  while (previewContainer.firstChild) previewContainer.removeChild(previewContainer.firstChild)

  const url = nextModule.value.video_url || nextModule.value.video_path || ''
  if (!url) return

  const isYT = ['youtube.com', 'youtu.be'].some(p => url.includes(p))

  if (isYT) {
    const iframe = document.createElement('iframe')
    iframe.width = '100%'
    iframe.height = '100%'
    iframe.allow = 'autoplay; encrypted-media; picture-in-picture'
    iframe.allowFullscreen = true
    const match = url.match(/(?:v=|\/|live\/)([^&?\/]{11})/)
    const id = match ? match[1] : null
    if (!id) return
    const encodedOrigin = encodeURIComponent(window.location.origin)
    iframe.src = `https://www.youtube.com/embed/${id}?autoplay=1&mute=1&controls=0&rel=0&iv_load_policy=3&origin=${encodedOrigin}&modestbranding=1&playsinline=1`
    iframe.className = 'w-full h-full'
    previewContainer.appendChild(iframe)
  } else {
    const vid = document.createElement('video')
    vid.className = 'w-full h-full object-cover'
    vid.muted = true
    vid.playsInline = true
    vid.src = url.startsWith('http') ? url : `/storage/${url}`
    vid.autoplay = true
    previewContainer.appendChild(vid)
    previewElement.value = vid
    vid.play().catch(() => {})
  }

  previewTimer.value = setTimeout(() => {
    stopPreview()
  }, PREVIEW_DURATION * 1000)
}

function stopPreview() {
  previewPlaying.value = false
  if (previewTimer.value) {
    clearTimeout(previewTimer.value)
    previewTimer.value = null
  }
  const previewContainer = document.getElementById('preview-container')
  if (previewContainer) {
    while (previewContainer.firstChild) previewContainer.removeChild(previewContainer.firstChild)
  }
  previewElement.value = null
  showPreviewOverlay.value = false
}

async function initYouTubePlayer() {
  try {
    await ensureYouTubeApi()
    const iframeId = 'youtube-player-element'
    if (!document.getElementById(iframeId)) return

    // eslint-disable-next-line no-undef
    youTubePlayer = new YT.Player(iframeId, {
      playerVars: {
        rel: 0,
        modestbranding: 1,
        controls: 0, // Disable YouTube controls - using custom controls instead
        iv_load_policy: 3,
        enablejsapi: 1,
        disablekb: 1,
        origin: window.location.origin,
        playsinline: 1
      },
      events: {
        onReady: onYouTubeReady,
        onStateChange: onYouTubeStateChange,
      }
    })
  } catch (error) {
    console.error('YouTube Player Init Error:', error)
  }
}

async function loadCertificate(force = false) {
  if (!course.value || !course.value.id) return
  // If we've already checked for this course, skip (do not re-fetch on tab reopen/module change)
  if (!force && certLoadedForCourseId.value === course.value.id) return

  certLoading.value = true
  certData.value = null
  try {
    const res = await fetch(`/ecourse/api/course/${course.value.id}/certificate/status`, { credentials: 'same-origin' })
    if (res.ok) {
      const json = await res.json()
      const d = json.data || {}
      if (d.certificate) {
        certData.value = d.certificate
      } else {
        certData.value = null
      }
      certIsCompleted.value = !!d.is_completed
      certHasActiveSubscription.value = !!d.has_active_subscription
      certRemaining.value = d.certificate_remaining ?? 0
      certCanGenerate.value = !!d.can_generate
      // mark as checked
      certLoadedForCourseId.value = course.value.id

      // Prompt user to generate certificate if course is completed, no cert yet, and quota remains
      maybePromptCertificate()
    } else if (res.status === 401) {
      window.location.href = '/login?redirect=' + window.location.pathname
    } else {
      console.warn('Unexpected response while loading certificate:', res.status)
      certData.value = null
    }
  } catch (error) {
    console.error('Failed to load certificate:', error)
    certData.value = null
  } finally {
    certLoading.value = false
  }
}

function maybePromptCertificate() {
  if (!course.value?.id) return
  // Only show once per course load
  if (certPromptShownForCourseId.value === course.value.id) return

  if (certIsCompleted.value && !certData.value && certRemaining.value > 0) {
    showCertPrompt.value = true
    certPromptShownForCourseId.value = course.value.id
  }
}

async function generateCertificate() {
  if (!course.value || !course.value.id) return
  certLoading.value = true
  try {
    const res = await fetch(`/ecourse/api/course/${course.value.id}/certificate/generate`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken() || ''
      }
    })

    if (res.ok) {
      const json = await res.json()
      const d = json.data || {}
      certData.value = d
      certIsCompleted.value = true
      certCanGenerate.value = false
      certRemaining.value = Math.max(0, certRemaining.value - 1)
      // Open modal to view
      showCertModal.value = true
    } else if (res.status === 402) {
      alert('Kuota sertifikat tidak tersedia. Silakan beli subscription untuk menambah kuota.');
    } else if (res.status === 401) {
      window.location.href = '/login?redirect=' + window.location.pathname
    } else {
      const text = await res.text()
      console.warn('Failed to generate certificate:', res.status, text)
      alert('Gagal membuat sertifikat. Silakan coba lagi nanti.');
    }
  } catch (error) {
    console.error('Failed to generate certificate:', error)
    alert('Gagal membuat sertifikat. Silakan coba lagi nanti.')
  } finally {
    certLoading.value = false
  }
}

function promptGenerateCertificate() {
  showCertPrompt.value = false
  generateCertificate()
}

async function downloadMaterial(material) {
  if (!material || !module.value) return
  
  isDownloading.value = true
  downloadingId.value = material.id
  
  try {
    const url = `/ecourse/api/module/${module.value.id}/material/${material.id}/download`
    const res = await fetch(url, {
      credentials: 'same-origin',
      headers: {
        'X-CSRF-TOKEN': csrfToken() || ''
      }
    })

    if (res.ok) {
      // Get filename from response headers
      const contentDisposition = res.headers.get('content-disposition')
      let filename = material.title
      if (contentDisposition) {
        const filenameMatch = contentDisposition.match(/filename="?([^"]+)"?/)
        if (filenameMatch) filename = filenameMatch[1]
      }

      // Create blob and download
      const blob = await res.blob()
      const blobUrl = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = blobUrl
      link.download = filename
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(blobUrl)

      // Show success toast
      toastTitle.value = 'Sukses'
      toastMessage.value = `${material.title} berhasil diunduh.`
      toastType.value = 'success'
      toastVisible.value = true
    } else if (res.status === 401) {
      // Unauthorized - redirect to login
      window.location.href = '/login?redirect=' + window.location.pathname
    } else if (res.status === 403) {
      // Permission denied
      toastTitle.value = 'Akses Ditolak'
      toastMessage.value = 'Anda tidak memiliki akses untuk mengunduh material ini. Silakan beli course atau berlangganan untuk mengakses.'
      toastType.value = 'error'
      toastVisible.value = true
    } else if (res.status === 404) {
      toastTitle.value = 'File Tidak Ditemukan'
      toastMessage.value = 'File material tidak ditemukan di server.'
      toastType.value = 'error'
      toastVisible.value = true
    } else {
      const errorText = await res.text()
      console.warn('Download failed:', res.status, errorText)
      toastTitle.value = 'Gagal Mengunduh'
      toastMessage.value = 'Terjadi kesalahan saat mengunduh file. Silakan coba lagi.'
      toastType.value = 'error'
      toastVisible.value = true
    }
  } catch (error) {
    console.error('Download error:', error)
    toastTitle.value = 'Gagal Mengunduh'
    toastMessage.value = 'Terjadi kesalahan jaringan. Silakan coba lagi.'
    toastType.value = 'error'
    toastVisible.value = true
  } finally {
    isDownloading.value = false
    downloadingId.value = null
  }
}

function onYouTubeReady(event) {
  if (progressInterval) clearInterval(progressInterval)
  
  // Update video duration
  const duration = event.target.getDuration()
  videoDuration.value = duration
  
  // fetch and expose quality levels
  try {
    let quals = youTubePlayer.getAvailableQualityLevels?.() || []
    if (!quals || quals.length === 0) {
      // some embeds don't return levels; provide sensible defaults
      quals = ['360p','480p','720p','1080p']
    }
    availableQualities.value = quals
    currentQuality.value = youTubePlayer.getPlaybackQuality?.() || quals[0]
  } catch (err) {
    console.warn('YT quality levels error', err)
    // fallback to defaults as above
    const quals = ['360p','480p','720p','1080p']
    availableQualities.value = quals
    currentQuality.value = quals[0]
  }
  
  if (duration > 0 && lastSentProgress.value > 0 && lastSentProgress.value < 100) {
    const seekToSeconds = (lastSentProgress.value / 100) * duration
    event.target.seekTo(seekToSeconds, true)
  }

  progressInterval = setInterval(() => {
    if (!youTubePlayer || typeof youTubePlayer.getDuration !== 'function') return
    
    const currentDuration = youTubePlayer.getDuration()
    if (!currentDuration || currentDuration <= 0) return
    
    const currentTime = youTubePlayer.getCurrentTime()
    currentVideoTime.value = currentTime
    const percent = (currentTime / currentDuration) * 100
    maybeSendProgress(percent)
  }, 5000)
}

function onYouTubeStateChange(event) {
  // States: -1 (unstarted), 0 (ended), 1 (playing), 2 (paused), 3 (buffering), 5 (cued)
  if (event?.data === 0) {
    // Video ended
    isVideoPlaying.value = false
    playerOverlayVisible.value = true
    overlayTitle.value = 'Video Selesai'
    overlayMessage.value = 'Selamat! Kamu telah menyelesaikan video ini.'
    overlayIcon.value = 'fa-check-circle'
    
    // Force send progress at the end to ensure completion flow runs
    maybeSendProgress(100, true)
    try { if (youTubePlayer && youTubePlayer.pauseVideo) youTubePlayer.pauseVideo() } catch(e) {}
    if (nextModule.value) {
      showPreviewOverlay.value = true
      playPreviewVideo()
    }
  } else if (event?.data === 2) {
    // Video paused
    isVideoPlaying.value = false
    playerOverlayVisible.value = true
    overlayTitle.value = 'Video Dijeda'
    overlayMessage.value = 'Klik untuk melanjutkan menonton'
    overlayIcon.value = 'fa-pause'
  } else if (event?.data === 1) {
    // Video playing
    isVideoPlaying.value = true
    playerOverlayVisible.value = false
  } else if (event?.data === 3) {
    // Buffering
    isVideoPlaying.value = false
  }
}

function applyLocalProgressUpdate(modId, progress) {
  const idx = allModules.value.findIndex(m => m.id === modId)
  if (idx !== -1) {
    const existing = allModules.value[idx]
    allModules.value[idx] = {
      ...existing,
      progress,
      is_completed: existing.is_completed || progress >= 95
    }
  }

  if (module.value && module.value.id === modId) {
    module.value.progress = progress
    // Don't mark module as completed locally here; wait for server confirmation to mark completion
  }

  const total = allModules.value.length || totalModules.value || 1
  const sum = allModules.value.reduce((s, m) => s + (Number(m.progress) || 0), 0)
  courseProgress.value = total ? Math.round(sum / total) : 0
  completedModules.value = allModules.value.filter(m => m.is_completed).length
}

async function maybeSendProgress(percent, force = false) {
  if (!module.value) return
  const clamped = Math.max(0, Math.min(100, percent))

  // If nothing changed and not forced, skip
  if (clamped === lastSentProgress.value && !force) return

  const now = Date.now()
  // Only send at most every PROGRESS_SEND_INTERVAL unless forced or it's the end
  if (!force && clamped < 100 && (now - lastSentAt.value) < PROGRESS_SEND_INTERVAL) return

  try {
    await postJson(`/module/${module.value.id}/progress`, { progress_percentage: clamped })
    // Update last sent markers only on success
    lastSentProgress.value = clamped
    lastSentAt.value = now

    applyLocalProgressUpdate(module.value.id, clamped)

    // If near completion, trigger complete flow
    if (clamped >= 95 && !isModuleCompleted.value && !isCompleting.value) {
      await completeModule()
    }

    // If exactly 100% and there's a next module, show preview (handled in ended handlers)
  } catch (error) {
    console.error('Failed to update progress:', error)
  }
}

async function syncFromProps(forceRetrack = false) {
  const oldModuleId = module.value?.id
  const oldCourseId = course.value?.id
  
  course.value = props.player_data.course || {}
  module.value = props.player_data.module || {}
  allModules.value = props.player_data.modules || []
  courseProgress.value = props.player_data.progress || 0
  completedModules.value = props.player_data.completed_modules || 0
  totalModules.value = props.player_data.total_modules || allModules.value.length
  
  if (forceRetrack || oldModuleId !== module.value?.id) {
    lastSentProgress.value = module.value?.progress || 0
    isModuleCompleted.value = module.value?.is_completed || false
    // Reset toast shown flag when navigating to new module
    toastShownForModule.value = null
    await nextTick()
    setupProgressTracking()

    // If course changed while the certificate tab is active, fetch certificate for new course
    if (oldCourseId !== course.value?.id && activeTab.value === 'certificate') {
      await loadCertificate()
    }
  } else if (module.value?.is_completed !== undefined) {
    isModuleCompleted.value = module.value.is_completed
  }
}

onMounted(async () => {
  await syncFromProps(true)
  // Always check certificate status so we can prompt users to generate it when eligible
  await loadCertificate()
})

// Watch for tab change to certificate and load certificate data when activated
watch(() => activeTab.value, async (val) => {
  if (val === 'certificate') await loadCertificate()
})

onBeforeUnmount(() => {
  teardownProgressTracking()
})

watch(() => props.module_slug, async () => {
  await syncFromProps(true)
})

watch(() => props.player_data, async () => {
  await syncFromProps(false)
}, { deep: true })

const certIframeSrc = computed(() => {
  return certData.value ? (certData.value.view_url + '?embed=1') : ''
})
</script>
