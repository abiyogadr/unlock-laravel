<template>
  <div class="min-h-screen bg-white text-slate-900">
    <div class="w-full mx-auto mb-32">
        <section class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-8 px-4 sm:px-8 lg:px-16 xl:px-24 py-6 items-start bg-gradient-to-br from-primary via-primary/90 to-primary/80">
          <div class="md:col-span-2 h-full grid grid-cols-1 lg:grid-cols-2 gap-6 rounded-3xl p-6 text-white shadow-2xl">
            <div class="md:space-y-4">
              <div class="flex flex-wrap items-center gap-2 text-2xs md:text-xs uppercase tracking-[0.2em] text-white/80">
                <span v-if="course.categories?.length" class="font-semibold">•{{ course.categories[0].name }}</span>
              </div>
              <h1 class="text-md lg:text-3xl font-bold leading-tight">
                {{ course.title }}
              </h1>
            </div>
            <div class="space-y-1 md:space-y-4">
              <div class="relative rounded-3xl border border-slate-200 overflow-hidden bg-slate-950 shadow-xl aspect-video bg-gradient-to-br from-gray-200/80 to-gray-100/80">
                <img
                  v-if="course.kv_url"
                  :src="course.kv_url"
                  :alt="course.slug"
                  class="w-full h-full object-cover"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 via-transparent to-transparent"></div>
              </div>
              <p class="text-xs sm:text-sm text-white/80 leading-relaxed">
                {{ course.short_description }}
              </p>
              <div class="mt-2 grid grid-cols-2 gap-4 text-xs uppercase tracking-[0.3em] text-white/70">
                <!-- <div>
                  <p class="text-[10px]">Durasi</p>
                  <p class="text-sm font-semibold text-white">{{ stats.formatted_duration || '23 jam' }}</p>
                </div> -->
                <!-- <div>
                  <p class="text-[10px]">Peserta</p>
                  <p class="text-sm font-semibold text-white">{{ stats.students_count || '850' }}+</p>
                </div> -->
              </div>
            </div>
          </div>
          <div class="flex flex-col justify-center rounded-3xl h-full w-full text-white bg-white/10 shadow-[0_20px_60px_-20px_rgba(15,23,42,0.8)] p-6 space-y-6">
            <!-- HARGA: sembunyikan jika sudah paid directly -->
            <div v-if="!isOwnedDirectly" class="space-y-2">
              <p class="text-2xs lg:text-xs uppercase tracking-[0.3em] text-white">HARGA E-COURSE</p>
              <div v-if="!course.is_free" class="space-y-3">
                <!-- IDR Price -->
                <div>
                  <div v-if="course.original_price" class="text-sm text-white/50 line-through">
                    {{ formatPrice(course.original_price) }}
                  </div>
                  <div class="text-3xl lg:text-4xl font-bold text-white">{{ formatPrice(course.price) }}</div>
                  <div class="flex flex-wrap gap-2 text-xs mt-2">
                    <span v-if="course.discount_percentage" class="bg-green-500/10 text-green-300 px-2 py-1 rounded-full">Diskon {{ course.discount_percentage }}%</span>
                    <span v-if="course.promo_label" class="bg-primary/20 text-primary px-2 py-1 rounded-full">{{ course.promo_label }}</span>
                  </div>
                  <p class="text-xs text-white/70">Berlangganan dan dapatkan akses tanpa batas dengan harga terbaik.</p>
                </div>
              </div>
              <div v-else class="text-3xl font-bold text-white">Gratis</div>
            </div>

            <!-- Jika sudah dibeli langsung: tampilkan badge & keterangan agar tidak kosong -->
            <div v-else class="space-y-2 text-white/80">
              <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center bg-yellow-100/30 text-white border border-yellow-500/80 text-2xs lg:text-xs font-semibold px-2 py-1 rounded-full">Sudah Dibeli</span>
                <div class="text-xs lg:text-lg font-semibold">Akses penuh</div>
              </div>
              <p class="text-xs mt-2 text-white/70">Kamu sudah membeli kursus ini — akses penuh tersedia.</p>
              <p v-if="hasUserCourse" class="text-xs mt-1 text-white/60">Tip: buka materi kapan saja dari halaman "My Journey".</p>
            </div>

            <div v-if="firstModuleSlug" class="space-y-3">
              <!-- 1) Sudah paid directly: hanya tombol Mulai/Lanjutkan -->
              <!-- Jika ada subscription atau sudah dibeli langsung, atau ada user course, tampilkan tombol Mulai/Lanjutkan -->
              <template v-if="subscription || isOwnedDirectly || hasUserCourse">
                <button
                  @click.prevent="ensureAndStart"
                  :disabled="isStarting"
                  :class="['block w-full text-center py-3 rounded-xl font-semibold text-sm transition-colors', isStarting ? 'bg-white/20 text-white opacity-80 pointer-events-none' : 'bg-white/10 text-white hover:bg-white/30 cursor-pointer']"
                >
                  <span v-if="!isStarting">
                    {{ hasCurrentModule ? (userProgress > 0 ? `Lanjutkan Belajar (${Math.round(userProgress)}%)` : 'Lanjutkan Belajar') : 'Mulai Belajar' }}
                  </span>
                  <span v-else>Memuat...</span>
                </button>
              </template>

              <!-- Jika belum dibeli langsung DAN tidak ada subscription DAN tidak ada user course, tampilkan tombol Beli Sekarang -->
              <template v-else-if="!isOwnedDirectly && !subscription && !hasUserCourse">
                <div>
                  <template v-if="page.props.auth && page.props.auth.user">
                    <Link
                      :href="`/ecourse/payment?type=course&course_slug=${course.slug}`"
                      class="block w-full bg-white/10 text-white text-center py-3 rounded-xl font-semibold hover:bg-white/30 transition-colors"
                    >
                      Beli Sekarang
                    </Link>
                  </template>
                  <template v-else>
                    <a
                      :href="`/login?redirect=${encodeURIComponent(`/ecourse/payment?type=course&course_slug=${course.slug}`)}`"
                      class="block w-full bg-white/10 text-white text-center py-3 rounded-xl font-semibold hover:bg-white/30 transition-colors"
                    >
                      Beli Sekarang
                    </a>
                  </template>
                </div>
              </template>
            </div>

            <div v-if="subscription" class="space-y-1 text-xs text-white/70">
              <p>Berlangganan aktif sampai <strong>{{ subscription.ends_at }}</strong></p>
              <p>Sisa sertifikat: <strong>{{ certificateRemaining }}</strong></p>
            </div>
            <div class="space-y-2">
              <p class="text-sm font-semibold text-white">Yang Anda Dapatkan</p>
              <ul class="space-y-1 text-xs md:text-sm text-white">
                <li class="flex items-start gap-2"><i class="fas fa-check text-white text-xs mt-1"></i> Akses seumur hidup</li>
                <li class="flex items-start gap-2"><i class="fas fa-check text-white text-xs mt-1"></i> Update materi berkala</li>
                <li class="flex items-start gap-2"><i class="fas fa-check text-white text-xs mt-1"></i> Sertifikat kelulusan</li>
              </ul>
            </div>
          </div>
        </section>

        <!-- Speaker Detail -->
        <section v-if="course.speaker" class="gap-10 px-4 sm:px-8 lg:px-16 xl:px-24 py-6 bg-white md:space-y-4 border-b border-slate-200">
          <div class="flex items-center justify-between">
            <h2 class="text-lg md:text-xl font-semibold text-slate-900">Pembicara</h2>
          </div>
          <div class="flex flex-col sm:flex-row gap-4">
            <!-- <div class="w-20 h-20 rounded-xl overflow-hidden bg-slate-100 shrink-0">
              <img
                :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(course.speaker.name)}&background=f1f5f9&color=0f172a&size=128&bold=true`"
                class="w-full h-full object-cover"
              />
            </div> -->
            <div class="flex flex-col justify-center space-y-2">
              <div>
                <h3 class="text-sm lg:text-lg font-semibold text-slate-700">{{ course.speaker.name }}</h3>
                <p class="text-xs lg:text-sm font-semibold text-primary">{{ course.speaker.job || 'Pembicara Profesional' }}</p>
              </div>
              <p v-if="course.speaker.bio" class="text-xs lg:text-sm text-slate-600 leading-relaxed">
                {{ course.speaker.bio }}
              </p>
            </div>
          </div>
        </section>

        <!-- Course Description -->
        <section class="md:space-y-4 px-4 sm:px-8 lg:px-16 xl:px-24 py-6 bg-white border-b border-slate-200">
          <h2 class="text-lg md:text-xl font-semibold text-slate-900">Deskripsi Program</h2>
          <div class="text-sm lg:text-lg prose prose-slate max-w-none text-slate-600" v-html="course.description"></div>
        </section>

        <!-- Modules -->
        <section class="md:space-y-4 px-4 sm:px-8 lg:px-16 xl:px-24 py-6 bg-white border-b border-slate-200">
          <div class="flex items-center justify-between">
            <h2 class="text-lg md:text-xl font-semibold text-slate-900">Materi Pembelajaran</h2>
            <span class="text-xs md:text-sm uppercase tracking-[0.2em] text-slate-800">{{ modules.length }} Modul</span>
          </div>
          <div class="divide-y divide-slate-200">
            <div
              v-for="(mod, idx) in modules"
              :key="mod.id"
              class="flex items-center justify-between gap-4 py-4 cursor-pointer hover:bg-slate-50 px-3 transition-colors"
              @click="navigateToPlayer(mod.slug)"
            >
              <div class="flex items-center gap-3 min-w-0">
                <span class="text-sm font-semibold text-slate-400">{{ String(idx + 1).padStart(2, '0') }}</span>
                <div class="min-w-0">
                  <p class="text-sm font-semibold text-slate-900 truncate">{{ mod.title }}</p>
                  <p class="text-xs text-slate-500">{{ mod.formatted_duration || '7 min' }}</p>
                </div>
              </div>
              <div class="flex items-center gap-2 shrink-0">
                <span v-if="mod.is_completed" class="text-[10px] md:text-xs text-green-700 bg-green-50 px-2 py-1 rounded">
                  Selesai
                </span>
                <i class="fas fa-chevron-right text-slate-400"></i>
              </div>
            </div>
          </div>
        </section>

        <!-- Recommendations -->
        <section class="space-y-4 px-4 sm:px-8 lg:px-16 xl:px-24 py-6 bg-white border-b border-slate-200" v-if="course.related_courses?.length">
          <div class="flex items-center justify-between">
            <h2 class="text-lg md:text-xl font-semibold text-slate-900">Saran E-course Lain</h2>
            <span class="text-xs uppercase tracking-[0.2em] text-slate-400">Kategori Sama</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div
              v-for="related in course.related_courses"
              :key="related.id"
              class="rounded-2xl border border-slate-800/50 bg-slate-900/80 text-white p-5 shadow-lg"
            >
              <p class="text-sm font-semibold text-white line-clamp-2">{{ related.title }}</p>
              <p class="text-xs text-white/60 mt-2">{{ related.short_description || 'Materi relevan untuk memperkuat kompetensi Anda.' }}</p>
              <Link
                :href="`/ecourse/${related.slug}`"
                class="text-xs font-semibold text-primary inline-flex items-center gap-2 mt-3"
              >
                Lihat detail <i class="fas fa-arrow-right text-[10px]"></i>
              </Link>
            </div>
          </div>
        </section>
    </div>

    <!-- Certificate Quota Popup -->
    <div v-if="showQuotaPopup" class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg p-6 max-w-sm mx-auto shadow-lg text-slate-900">
        <h3 class="text-lg font-bold">Yah, kuota sertifikat kamu sudah habis</h3>
        <p class="text-md mb-4">Tenang, kamu masih bisa lanjut dengan pilih salah satu opsi di bawah ini.</p>
        <div class="space-y-3">
          <!-- Subscription options table from payment.vue -->
          <div v-if="subscriptionPackages && subscriptionPackages.length" class="mb-4">
            <!-- <h4 class="text-lg font-semibold mb-2">Paket Subscription</h4> -->
            <div class="grid grid-cols-1 gap-2">
              <Link
                v-for="pkg in subscriptionPackages" :key="pkg.id"
                :href="`/ecourse/payment?type=course&course_slug=${course.slug}&package_id=${pkg.id}`"
                class="block p-3 border-2 border-gray-200 rounded-xl hover:bg-gray-50 transition flex justify-between items-center"
              >
                <div>
                  <div class="text-sm font-semibold text-slate-900">{{ pkg.name }}</div>
                  <div v-if="pkg.package_type === 'monthly'" class="text-xs text-gray-500">Monthly - {{ pkg.duration_days }} hari</div>
                  <div v-else-if="pkg.package_type === 'ustar'" class="text-xs text-gray-500">{{ pkg.ustar_credits }} USTAR</div>
                  <div v-else-if="pkg.description" class="text-xs text-gray-500">{{ pkg.description }}</div>
                  <span v-if="Number(pkg.discount_price) && Number(pkg.discount_price) < Number(pkg.price)" class="bg-red-100 text-red-600 text-[10px] font-bold px-1.5 py-0.5 rounded mb-1 uppercase">
                    Hemat {{ Math.round(((Number(pkg.price) - Number(pkg.discount_price)) / Number(pkg.price)) * 100) }}%
                  </span>
                </div>
                <div class="text-right">
                  <div class="text-sm lg:text-base font-bold text-primary">{{ formatPrice(pkg.discount_price || pkg.price) }}</div>
                  <div v-if="Number(pkg.discount_price) && Number(pkg.discount_price) < Number(pkg.price)" class="text-xs text-gray-600 line-through decoration-gray-300">
                    {{ formatPrice(pkg.price) }}
                  </div>
                </div>
              </Link>
            </div>
          </div>
          <span class="text-center block my-4 text-sm text-gray-500">
            atau
          </span>

          <!-- course card style similar to payment.vue package cards -->
          <Link
            :href="`/ecourse/payment?type=course&course_slug=${course.slug}`"
            class="block p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary transition"
          >
            <div class="flex items-start gap-4">
              <div class="flex-1">
                <h3 class="text-sm font-bold text-gray-800 line-clamp-2">{{ course.title }}</h3>
                <p class="text-xs text-gray-500 mt-1">E-course</p>
              </div>
              <div class="text-right">
                <div class="text-sm font-bold text-primary">{{ formatPrice(course.price) }}</div>
              </div>
            </div>
          </Link>
          <button
            @click="continueWithoutCert"
            class="block w-full text-sm bg-primary text-white py-3 rounded-xl font-semibold hover:bg-primary/90 transition-colors cursor-pointer"
          >
            Tetap Lanjutkan Belajar
          </button>
          <button
            @click="showQuotaPopup = false"
            class="block w-full text-sm bg-slate-200 text-slate-700 py-2 rounded-xl hover:bg-slate-300 transition-colors cursor-pointer"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const page = usePage()

const props = defineProps({
  course: {
    type: Object,
    required: true,
  },
  modules: {
    type: Array,
    default: () => [],
  },
  stats: {
    type: Object,
    required: true,
  },
  user_progress: {
    type: [Number, String],
    default: 0,
  },
  first_module_slug: {
    type: String,
    default: null,
  },
  subscription: {
    type: Object,
    default: null,
  },
  has_user_course: {
    type: Boolean,
    default: false,
  },
  is_owned_directly: {
    type: Boolean,
    default: false,
  },
  has_current_module: {
    type: Boolean,
    default: false,
  },
  certificate_remaining: {
    type: Number,
    default: 0,
  },
  packages: {
    type: Array,
    default: () => [],
  },
})

const course = props.course
const modules = props.modules
const stats = props.stats
const firstModuleSlug = props.first_module_slug
const subscription = props.subscription
const userProgress = props.user_progress || 0
const hasUserCourse = props.has_user_course
const isOwnedDirectly = props.is_owned_directly
const hasCurrentModule = props.has_current_module
const certificateQuota = props.certificate_remaining

const subscriptionPackages = computed(() => {
  const pkgs = props.packages || []
  return pkgs.filter(pkg => ['subscription','monthly','ustar'].includes(pkg.package_type))
})

// remaining certificate count
const certificateRemaining = computed(() => {
  return subscription?.certificate_remaining ?? certificateQuota
})

const isStarting = ref(false)
const showQuotaPopup = ref(false)

const levelLabel = computed(() => {
  const level = (course?.level || '').toLowerCase()
  if (level === 'intermediate') return 'Intermediate'
  if (level === 'advanced') return 'Advanced'
  return 'Beginner'
})

function formatPrice(value) {
  return `Rp ${new Intl.NumberFormat('id-ID').format(value || 0)}`
}

function navigateToPlayer(slug) {
  if (course.is_owned) {
    router.get(`/ecourse/player/${course.slug}/${slug}`)
  }
}

function continueWithoutCert() {
  // allow user to start regardless of certificate quota
  showQuotaPopup.value = false
  if (firstModuleSlug) {
    router.get(`/ecourse/player/${course.slug}/${firstModuleSlug}`)
  }
}

async function ensureAndStart() {
  if (isStarting.value) return

  // Show popup only when user does NOT already own the course directly,
  // and either has no subscription or has a subscription with 0 remaining quota.
  if (!isOwnedDirectly && ((subscription && certificateRemaining.value === 0) || !subscription)) {
    showQuotaPopup.value = true
    return
  }

  isStarting.value = true
  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    const res = await fetch(`/ecourse/api/user-courses/${course.id}/ensure`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
      },
    })

    const data = await res.json()
    if (!res.ok) {
      console.error('Ensure error:', data)
      
      // If 403 (no access), show the quota popup so user can see purchase options
      if (res.status === 403) {
        showQuotaPopup.value = true
        isStarting.value = false
        return
      }
      
      // Otherwise show error message
      alert(data.message || 'Gagal memulai kursus. Silakan coba lagi.')
      isStarting.value = false
      return
    }

    if (data.redirect) {
      // Use Inertia router to navigate (keeps SPA behavior)
      router.get(data.redirect)
    }
  } catch (err) {
    console.error('Ensure error:', err)
    alert('Terjadi kesalahan. Silakan coba lagi.')
  } finally {
    isStarting.value = false
  }
}

</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

div {
  font-family: 'Plus Jakarta Sans', sans-serif;
}

.prose :deep(p) {
  font-size: 0.95rem;
  line-height: 1.8;
  margin-bottom: 1rem;
  color: #475569;
}

.prose :deep(h2), .prose :deep(h3) {
  font-weight: 700;
  font-size: 1.1rem;
  margin-top: 1.5rem;
  margin-bottom: 0.75rem;
  color: #0f172a;
}

.prose :deep(ul) {
  list-style: none;
}

::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-thumb {
  background: #cbd5f5;
  border-radius: 999px;
}
</style>