<template>
  <div class="min-h-screen bg-white py-4">
    <div class="container mx-auto px-4 max-w-2xl">
      <div v-if="statusMessage" :class="['mb-4 p-3 rounded', statusMessageType === 'error' ? 'bg-red-50 border border-red-100 text-red-800' : statusMessageType === 'success' ? 'bg-green-50 border border-green-100 text-green-800' : 'bg-primary/5 border border-primary/20 text-primary']">
        {{ statusMessage }}
      </div>
      <!-- Success State -->
      <div v-if="status === 'success'" class="space-y-6">
        <!-- Success Card -->
        <div class="bg-white rounded-3xl shadow-lg overflow-hidden border border-gray-100">
          <div class="bg-gradient-to-r from-primary to-purple-700 h-2"></div>
          <div class="p-8 text-center">
            <!-- Success Icon -->
            <div class="inline-flex items-center justify-center w-20 h-20 bg-primary/10 rounded-full">
              <svg class="w-10 h-10 text-primary" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
            </div>

            <h1 class="text-3xl font-bold text-gray-800 mb-2">Pembayaran Berhasil!</h1>
            <p class="text-sm text-gray-500 mb-6">Anda akan dialihkan dalam <span class="font-bold text-primary">{{ countdown }}</span> detik...</p>

            <!-- Transaction Details -->
            <div v-if="transaction" class="bg-gray-50 rounded-2xl p-6 mb-8 text-left border border-gray-100">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-gray-600 text-xs md:text-sm font-medium">Nominal Pembayaran</p>
                  <p class="text-sm md:text-md font-bold text-gray-800">{{ formatPrice(Number(transaction.amount)) }}</p>
                  <p class="text-gray-500 text-xs mt-1">{{ transaction.currency }}</p>
                </div>
                <div>
                  <p class="text-gray-600 text-xs md:text-sm font-medium">Tipe Pembelian</p>
                  <p class="text-sm md:text-md font-bold text-gray-800 capitalize">{{ transaction.meta?.type || 'E-Course' }}</p>
                </div>
              </div>
              <div v-if="displayProductName" class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-gray-600 text-xs md:text-sm font-medium">Nama</p>
                <p class="text-sm md:text-md font-bold text-gray-800">{{ displayProductName }}</p>
                <p v-if="displayProductDuration" class="text-xs text-gray-500 mt-1">{{ displayProductDuration }}</p>
              </div>
              <div v-if="displayPurchaseTime" class="mt-4">
                <p class="text-gray-600 text-xs md:text-sm font-medium">Waktu Pembelian</p>
                <p class="text-xs md:text-sm font-bold text-gray-800">{{ displayPurchaseTime }}</p>
              </div>
              <div v-if="displaySubscriptionEnd" class="mt-2">
                <p class="text-gray-600 text-xs md:text-sm font-medium">Berakhir Hingga</p>
                <p class="text-xs md:text-sm font-bold text-gray-800">{{ displaySubscriptionEnd }}</p>
              </div>
              <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-gray-600 text-xs">Order ID : <span class="font-mono text-gray-800">{{ order_id }}</span></p>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
              <!-- Top button: go to course show page if available -->
              <a 
                v-if="courseShowUrl"
                :href="courseShowUrl" 
                class="block w-full px-6 py-3 bg-gradient-to-r from-primary to-purple-700 text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-200"
              >
                Lihat Course →
              </a>

              <!-- Bottom button: go to first module if available, otherwise fallback to course or dashboard -->
              <Link 
                :href="firstModuleUrl || courseShowUrl || '/ecourse/dashboard'" 
                class="block w-full px-6 py-3 bg-gray-100 text-gray-800 font-semibold rounded-xl hover:bg-gray-200 transition-all duration-200"
              >
                Mulai Belajar
              </Link>
            </div>
          </div>
        </div>

        <!-- Info Box -->
        <div class="bg-primary/5 border border-primary/20 rounded-2xl p-4">
          <div class="flex gap-3">
            <svg class="w-5 h-5 text-primary flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <div class="text-sm text-primary">
              <p v-if="props.transaction?.meta?.type === 'package'" class="mt-1">Langganan Anda sudah aktif. Akses seluruh materi yang termasuk selama masa langganan.</p>
              <p v-else class="font-semibold">Akses E-Course sudah aktif</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Pending State -->
      <div v-else-if="status === 'pending'" class="space-y-6">
        <div class="bg-white rounded-3xl shadow-lg overflow-hidden border border-gray-100">
          <div class="bg-gradient-to-r from-amber-400 to-orange-500 h-2"></div>
          <div class="p-8 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-amber-100 rounded-full mb-6">
              <svg class="w-10 h-10 text-amber-500 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
            </div>

            <h1 class="text-3xl font-bold text-gray-800 mb-2">Pembayaran Diproses</h1>
            <p class="text-gray-600 mb-6">Pembayaran Anda sedang diproses. Ini biasanya memakan waktu beberapa menit.</p>

            <div v-if="transaction" class="bg-gray-50 rounded-2xl p-6 mb-8 text-left border border-gray-100">
              <p class="text-gray-600 text-sm">Nominal: <span class="font-bold text-gray-800">{{ formatPrice(transaction.amount) }} {{ transaction.currency }}</span></p>
              <p class="text-gray-600 text-sm mt-2">Order ID: <span class="font-mono text-xs text-gray-800">{{ order_id }}</span></p>
            </div>

            <div class="space-y-3">
              <button
                v-if="transaction && transaction.meta?.snap_token"
                @click="continuePayment"
                class="w-full px-6 py-3 bg-gradient-to-r from-primary to-purple-700 text-white font-semibold rounded-xl hover:shadow-lg transition-all cursor-pointer"
              >
                Lanjutkan Pembayaran
              </button>

              <Link 
                href="/ecourse/dashboard" 
                class="block w-full px-6 py-3 bg-gray-100 text-gray-800 font-semibold rounded-xl hover:bg-gray-200 transition-all"
              >
                Kembali ke Dashboard
              </Link>
            </div>
          </div>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
          <div class="flex gap-3">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <div class="text-sm text-amber-700">
              <p class="font-semibold">Tunggu Konfirmasi</p>
              <p class="mt-1">Jangan tutup halaman ini. Kami akan menampilkan konfirmasi setelah proses selesai atau Anda dapat kembali ke dashboard.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Failed State -->
      <div v-else class="space-y-6">
        <div class="bg-white rounded-3xl shadow-lg overflow-hidden border border-gray-100">
          <div class="bg-gradient-to-r from-red-400 to-pink-500 h-2"></div>
          <div class="p-8 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-red-100 rounded-full mb-6">
              <svg class="w-10 h-10 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>

            <h1 class="text-3xl font-bold text-gray-800 mb-2">Pembayaran Gagal</h1>
            <p class="text-gray-600 mb-6">Sayangnya pembayaran Anda tidak berhasil diproses.</p>

            <div v-if="transaction" class="bg-gray-50 rounded-2xl p-6 mb-8 text-left border border-gray-100">
              <p class="text-gray-600 text-sm">Nominal: <span class="font-bold text-gray-800">{{ formatPrice(transaction.amount) }} {{ transaction.currency }}</span></p>
              <p class="text-gray-600 text-sm mt-2">Order ID: <span class="font-mono text-xs text-gray-800">{{ order_id }}</span></p>
            </div>

            <div class="space-y-3">
              <button 
                @click="retryPayment"
                class="w-full px-6 py-3 bg-gradient-to-r from-primary to-purple-700 text-white font-semibold rounded-xl hover:shadow-lg transition-all"
              >
                Coba Lagi
              </button>
              <Link 
                href="/ecourse/dashboard" 
                class="block w-full px-6 py-3 bg-gray-100 text-gray-800 font-semibold rounded-xl hover:bg-gray-200 transition-all"
              >
                Kembali ke Dashboard
              </Link>
            </div>
          </div>
        </div>

        <div class="bg-red-50 border border-red-200 rounded-2xl p-4">
          <div class="flex gap-3">
            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <div class="text-sm text-red-700">
              <p class="font-semibold">Hubungi Support</p>
              <p class="mt-1">Jika masalah terus berlanjut, hubungi tim support kami untuk bantuan.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
  status: { type: String, default: 'pending' },
  order_id: { type: String, default: null },
  transaction: { type: Object, default: null },
  redirect_url: { type: String, default: null },
  redirect_module_url: { type: String, default: null },
  first_module_slug: { type: String, default: null },
  course_name: { type: String, default: null },
  purchase_time: { type: String, default: null },
})

const countdown = ref(5)
// Toggle automatic redirect after successful payment via env (Vite): VITE_ENABLE_AUTO_REDIRECT_PAYMENT=true
const ENABLE_AUTO_REDIRECT = import.meta.env.VITE_ENABLE_AUTO_REDIRECT_PAYMENT === 'true'

const statusMessage = ref(null)
const statusMessageType = ref('info')
const pollingInterval = ref(null)

function setMessage(msg, type = 'info') {
  statusMessage.value = msg
  statusMessageType.value = type
  setTimeout(() => {
    if (statusMessage.value === msg) statusMessage.value = null
  }, 6000)
}

async function checkStatus() {
  if (!props.order_id) return
  try {
    const res = await fetch(`/ecourse/api/payments/${props.order_id}/status`, { credentials: 'same-origin' })
    const body = await res.json().catch(() => ({}))
    if (!res.ok) return
    if (body.status && body.status !== 'pending') {
      // status changed from pending -> reload the page to show the updated status
      router.visit(`/ecourse/payment?status=${encodeURIComponent(body.status)}&order_id=${encodeURIComponent(props.order_id)}`)
    }
  } catch (e) {
    console.error('Status poll error:', e)
  }
}

onBeforeUnmount(() => {
  if (pollingInterval.value) {
    clearInterval(pollingInterval.value)
    pollingInterval.value = null
  }
})

// Get query parameters for additional data
const urlParams = new URLSearchParams(window.location.search)
const queryCourseName = urlParams.get('course_name')
const queryCourseSlug = urlParams.get('course_slug')
const queryFirstModuleSlug = urlParams.get('first_module_slug')
const queryPurchaseTime = urlParams.get('purchase_time')
const queryRedirectUrl = urlParams.get('redirect_url')

// Use props or query params
const displayCourseName = computed(() => props.course_name || queryCourseName)
const displayPurchaseTime = computed(() => props.purchase_time || queryPurchaseTime)
const effectiveRedirectUrl = computed(() => {
  // priority: explicit prop redirect -> query redirect -> query course slug -> meta course slug -> package my-journey -> null
  if (props.redirect_url) return props.redirect_url
  if (queryRedirectUrl) return queryRedirectUrl
  if (queryCourseSlug) return `/ecourse/course/${queryCourseSlug}`
  const metaSlug = props.transaction?.meta?.course_slug
  if (metaSlug) return `/ecourse/course/${metaSlug}`
  // default redirect for package/subscription purchases
  if (props.transaction?.meta?.type === 'package') return '/ecourse/my-journey'
  return null
})

const displaySubscriptionEnd = computed(() => {
  const meta = props.transaction?.meta || {}
  return meta.subscription_end || null
})

const displayProductName = computed(() => {
  if (props.transaction?.meta?.type === 'package') {
    return props.transaction.meta.item_name || `Paket #${props.transaction.meta.package_id}`
  }
  return displayCourseName.value
})

const displayProductDuration = computed(() => {
  const meta = props.transaction?.meta || {}
  if (meta.plan_duration) {
    if (meta.plan_duration === '1_month') return 'Berlangganan: 1 bulan'
    if (meta.plan_duration === '3_months') return 'Berlangganan: 3 bulan'
    if (meta.plan_duration === '6_months') return 'Berlangganan: 6 bulan'
  }
  if (meta.duration_days) {
    return `Berlangganan: ${meta.duration_days} hari`
  }
  return null
})

const courseShowUrl = computed(() => {
  // priority: explicit redirect_url -> queryRedirectUrl -> queryCourseSlug -> meta.course_slug -> package my-journey -> null
  if (props.redirect_url) return props.redirect_url
  if (queryRedirectUrl) return queryRedirectUrl
  if (queryCourseSlug) return `/ecourse/course/${queryCourseSlug}`
  // prefer explicit course slug when available
  const slug = props.transaction?.meta?.course_slug
  if (slug) return `/ecourse/course/${slug}`
  // if this was a package purchase, go to my-journey
  if (props.transaction?.meta?.type === 'package') return '/ecourse/my-journey'
  return null
})

const firstModuleUrl = computed(() => {
  // prefer explicit module URL from controller; otherwise try to construct from query, meta, or props
  if (props.redirect_module_url) return props.redirect_module_url
  const slug = props.transaction?.meta?.course_slug || queryCourseSlug
  const firstModuleSlug = props.first_module_slug || props.transaction?.meta?.first_module_slug || queryFirstModuleSlug
  if (slug && firstModuleSlug) return `/ecourse/player/${slug}/${firstModuleSlug}`
  // fallback to courseShowUrl
  return courseShowUrl.value
})

onMounted(() => {
  if (props.status === 'success' && effectiveRedirectUrl.value && ENABLE_AUTO_REDIRECT) {
    const timer = setInterval(() => {
      countdown.value--
      if (countdown.value <= 0) {
        clearInterval(timer)
        window.location.href = effectiveRedirectUrl.value
      }
    }, 1000)
  }

  if (props.status === 'pending' && props.order_id) {
    // start a poll to check pending transaction status every 5 seconds
    pollingInterval.value = setInterval(checkStatus, 5000)
    // run immediately once
    checkStatus()
  }
})

const retryPayment = () => {
  if (props.transaction?.meta?.type === 'course' && props.transaction?.meta?.course_id) {
    router.visit(`/ecourse/payment?type=course&course_id=${props.transaction.meta.course_id}`)
  } else if (props.transaction?.meta?.type === 'package' && props.transaction?.meta?.package_id) {
    router.visit(`/ecourse/payment?type=package&package_id=${props.transaction.meta.package_id}`)
  }
}

async function updateTransactionStatus(orderId, status = 'failed') {
  try {
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]')
    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null

    const res = await fetch('/ecourse/api/payments/update-status', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
      },
      credentials: 'same-origin',
      body: JSON.stringify({ order_id: orderId, status }),
    })

    const body = await res.json().catch(() => ({}))
    if (!res.ok) throw body

    setMessage(body.message || `Transaksi diperbarui: ${status}`, 'success')
  } catch (err) {
    console.error('Update transaction status error:', err)
    setMessage(err?.message || 'Gagal memperbarui status transaksi', 'error')
  }
}

function continuePayment() {
  const snapToken = props.transaction?.meta?.snap_token
  if (!snapToken) {
    // If no snap token is available, redirect back to payment to create one
    retryPayment()
    return
  }

  try {
    window.snap.pay(snapToken, {
      onSuccess: function(resSnap) {
        router.visit(`/ecourse/payment?status=success&order_id=${resSnap.order_id}`)
      },
      onPending: function(resSnap) {
        router.visit(`/ecourse/payment?status=pending&order_id=${resSnap.order_id}`)
      },
      onError: function(err) {
        console.error('Midtrans continue onError:', err)
        setMessage('Pembayaran gagal. Silakan coba lagi.', 'error')
        if (props.order_id) {
          updateTransactionStatus(props.order_id, 'failed')
        }
      },
      onClose: function() {
        // If the user closed the popup while on PaymentStatus, keep transaction pending and reload status page
        if (props.order_id) {
          setMessage('Transaksi disimpan sebagai pending', 'info')
          router.visit(`/ecourse/payment?status=pending&order_id=${props.order_id}`)
        }
      }
    })
  } catch (e) {
    console.error('Continue payment error:', e)
    setMessage('Gagal melanjutkan pembayaran. Coba lagi.', 'error')
  }
}

function formatPrice(value) {
  return `Rp ${new Intl.NumberFormat('id-ID').format(value || 0)}`
}
</script>
