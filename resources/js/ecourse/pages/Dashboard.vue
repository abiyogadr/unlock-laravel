<template>
  <div class="min-h-screen bg-white text-slate-900">
    <div class="w-full mx-auto">
      <!-- Welcome Message -->
      <section class="px-4 lg:px-8 xl:px-16 py-6">
        <div class="bg-gradient-to-r from-primary to-purple-700 rounded-3xl p-8 text-white shadow-lg relative overflow-hidden">
      <div class="relative z-10">
        <h1 class="text-lg md:text-xl font-bold mb-2">Halo, {{ userName }}! 👋</h1>
        <p class="text-sm text-white/80 max-w-md">Senang melihatmu kembali. Lanjutkan belajarmu dan tingkatkan skill profesionalmu hari ini.</p>
      </div>
      <i class="fas fa-graduation-cap absolute -right-10 -bottom-10 text-[200px] text-white/10 rotate-12"></i>
        </div>
      </section>

      <!-- Combined Summary Card (Prominent Dashboard Highlight) -->
      <section class="px-4 lg:px-8 xl:px-16">
        <div class="bg-white rounded-3xl p-0 shadow-2xl overflow-hidden">
          <div class="flex flex-col md:flex-row gap-6 items-stretch">
            <!-- Left: prominent colored panel -->
            <div class="lg:flex-[0_0_45%] bg-gradient-to-br from-primary to-purple-600 text-white p-6 rounded-3xl relative overflow-hidden flex items-center">
              <div class="w-full flex items-center justify-between">
                <div class="pr-4">
                  <p class="text-xs font-semibold uppercase tracking-wider text-white/80">Total E-Course</p>
                  <h3 class="text-4xl md:text-5xl font-extrabold leading-tight mt-2">{{ stats.total_owned || 0 }}</h3>
                  <p class="mt-3 text-sm text-white/90">Selesai <span class="font-semibold">{{ stats.completed || 0 }}</span> • Berjalan <span class="font-semibold">{{ stats.in_progress || 0 }}</span></p>
                  <div class="mt-4 flex items-center gap-3">
                    <div class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-semibold">Sisa Sertifikat: <span class="ml-2">{{ stats.certificate_remaining || 0 }}</span></div>
                    <div class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-semibold">Total Sertifikat: <span class="ml-2">{{ stats.certificates || 0 }}</span></div>
                  </div>
                  <div v-if="stats.subscription_end" class="mt-2 text-xs text-white/80">
                    Subscription aktif hingga: <span class="font-semibold">{{ stats.subscription_end }}</span>
                  </div>
                </div>

                <div class="flex items-center justify-center">
                  <!-- Donut progress -->
                  <div class="w-24 h-24 rounded-full bg-white/10 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" aria-hidden>
                      <circle cx="32" cy="32" r="28" class="text-white/20" fill="none" :stroke="'rgba(255,255,255,0.14)'" stroke-width="6" />
                      <circle cx="32" cy="32" r="28" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round" :stroke-dasharray="circle.c" :stroke-dashoffset="circle.dashoffset" class="transition-all duration-700" />
                    </svg>
                    <div class="absolute text-center">
                      <div class="text-sm font-semibold">{{ overallCompletion }}%</div>
                      <div class="text-xs opacity-80">Selesai</div>
                    </div>
                  </div>
                </div>
              </div>
              <i class="fas fa-layer-group text-white/6 absolute -right-8 -bottom-8 text-[140px] rotate-12"></i>
            </div>

            <!-- Right: stat cards + category breakdown -->
            <div class="lg:flex-1 bg-white p-4 rounded-3xl">
              <!-- <p class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">E-Course per Kategori</p> -->
              <div class="grid grid-cols-2 gap-4">
                <div v-for="cat in categoryStats" :key="cat.id" class="p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors duration-200">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-xs lg:text-sm font-medium text-gray-800">{{ cat.name }}</p>
                      <p class="text-xs lg:text-sm text-gray-700 mt-1">Total <span class="font-semibold text-gray-800">{{ cat.total }}</span></p>
                    </div>
                    <div class="text-right">
                      <p class="text-xs lg:text-sm text-gray-700">Selesai <span class="font-semibold text-gray-800">{{ cat.completed }}</span></p>
                      <p class="text-xs lg:text-sm text-gray-700">Berjalan <span class="font-semibold text-gray-800">{{ cat.in_progress }}</span></p>
                    </div>
                  </div>

                  <div class="mt-3 h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-primary to-purple-600 rounded-full transition-all duration-500" :style="{ width: (cat.avg_progress || 0) + '%' }"></div>
                  </div>
                  <div class="mt-1 text-xs text-gray-700">Rata-rata progres: <span class="font-semibold text-gray-800">{{ cat.avg_progress || 0 }}%</span></div>
                </div>
                <div v-if="categoryStats.length === 0" class="text-sm text-gray-700 italic sm:col-span-2">Belum ada e-course terpasang pada kategori manapun.</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Latest/My Courses -->
      <section class="px-4 lg:px-8 xl:px-16 py-6">
          <div class="space-y-6">
          <div class="flex items-center justify-between gap-4">
        <div class="min-w-0">
          <h2 class="text-lg font-bold text-gray-800">Lanjutkan Belajar</h2>
        </div>
        <Link href="/ecourse/my-journey" class="px-6 py-3 bg-primary text-white rounded-xl font-semibold text-sm hover:bg-purple-800 transition inline-flex items-center whitespace-nowrap">
          My Journey <i class="fas fa-arrow-right ml-2 text-xs"></i>
        </Link>
      </div>

      <!-- Recent Courses Grid -->
      <div v-if="recentCourses.length > 0" class="relative w-full max-w-full overflow-hidden mb-4">
        <button
          @click="scrollLeft"
          class="absolute left-0 top-1/2 transform -translate-y-1/2 z-10 bg-white bg-opacity-80 hover:bg-opacity-100 rounded-full p-2 shadow-md transition-opacity duration-200"
          :class="{ 'opacity-0 pointer-events-none': !canScrollLeft, 'opacity-100': canScrollLeft }"
        >
          <i class="fas fa-chevron-left text-gray-600"></i>
        </button>
        <div
          ref="scroller"
          class="overflow-x-auto scrollbar-hide pb-4"
          @scroll="updateScrollButtons"
        >
          <div class="flex gap-4 w-max">
            <div v-for="course in recentCourses" :key="course.id">
              <CourseCard 
                :course="course.course"
                :progress="course.progress"
                :show-price="false"
              />
            </div>
          </div>
        </div>
        <button
          @click="scrollRight"
          class="absolute right-0 top-1/2 transform -translate-y-1/2 z-10 bg-white bg-opacity-80 hover:bg-opacity-100 rounded-full p-2 shadow-md transition-opacity duration-200"
          :class="{ 'opacity-0 pointer-events-none': !canScrollRight, 'opacity-100': canScrollRight }"
        >
          <i class="fas fa-chevron-right text-gray-600"></i>
        </button>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-3xl p-12 text-center border border-dashed border-gray-200">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-inbox text-3xl text-gray-300"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-800 mb-2">Mulai Belajar Sekarang</h3>
        <p class="text-gray-500 mb-6">Belum ada e-course yang kamu ambil. Jelajahi katalog kami dan temukan e-course yang sesuai!</p>
        <Link href="/ecourse/catalog" class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-xl font-bold text-sm hover:bg-purple-800 transition">
          <i class="fas fa-book mr-2"></i> Jelajahi Katalog
        </Link>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { Link } from '@inertiajs/vue3'
import CourseCard from '../components/CourseCard.vue'

const props = defineProps({
  user_name: {
    type: String,
    required: true,
  },
  stats: {
    type: Object,
    required: true,
  },
  recent_courses: {
    type: Array,
    default: () => [],
  },
  category_stats: {
    type: Array,
    default: () => [],
  },
})

const userName = props.user_name
const stats = props.stats
const recentCourses = props.recent_courses || []
const categoryStats = props.category_stats || []

const overallCompletion = computed(() => {
  const total = Number(stats.total_owned || 0)
  if (total === 0) return 0
  return Math.round((Number(stats.completed || 0) / total) * 100)
})

const circle = computed(() => {
  const r = 28
  const c = 2 * Math.PI * r
  const dashoffset = c * (1 - overallCompletion.value / 100)
  return { r, c, dashoffset }
})

const scroller = ref(null)
const canScrollLeft = ref(false)
const canScrollRight = ref(false)

onMounted(async () => {
  await nextTick()
  updateScrollButtons()
})

function updateScrollButtons() {
  const el = scroller.value
  if (el) {
    canScrollLeft.value = el.scrollLeft > 0
    canScrollRight.value = el.scrollLeft < el.scrollWidth - el.clientWidth - 1
  }
}

function scrollLeft() {
  const el = scroller.value
  if (el) {
    el.scrollBy({ left: -300, behavior: 'smooth' })
  }
}

function scrollRight() {
  const el = scroller.value
  if (el) {
    el.scrollBy({ left: 300, behavior: 'smooth' })
  }
}
</script>

<style scoped>
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
</style>
