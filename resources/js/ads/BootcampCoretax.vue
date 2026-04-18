<script setup>
import { ref, onMounted, watch } from 'vue'
import SearchableSelect from './SearchableSelect.vue'

/* ─── Page Config (injected by Blade shell) ──────────────── */
const cfg = window.__PAGE_CONFIG__ ?? {}
const registrationUrl = cfg.registrationUrl ?? '#'
const whatsappUrl     = cfg.whatsappUrl     ?? '#'

/* ─── FAQ State ───────────────────────────────────────────── */
const openFaq = ref(null)
function toggleFaq(i) {
    openFaq.value = openFaq.value === i ? null : i
}

/* ─── Sticky CTA State ────────────────────────────────────── */
const heroHidden = ref(false)

/* ─── Static Data ─────────────────────────────────────────── */
const currentYear = new Date().getFullYear()

const heroBadges = [
    { label: '11 Apr – 25 Apr 2026', icon: 'fa-calendar-alt' },
    { label: '09:00–12:00 WIB',      icon: 'fa-clock' },
    { label: 'via Zoom Online',       icon: 'fa-laptop' },
]

const trustBadges = [
    { icon: 'fa-shield-halved', text: 'Sertifikat Resmi 12 SKP' },
    { icon: 'fa-check-double',  text: 'Materi Praktis & Aplikatif' },
    { icon: 'fa-users',         text: '100.000+ Alumni' },
    { icon: 'fa-rotate',        text: 'Garansi Gratis Mengulang' },
    { icon: 'fa-infinity',      text: 'Akses Rekaman Selamanya' },
    { icon: 'fa-headset',       text: 'Mentor 10+ Tahun' },
    { icon: 'fa-building',      text: 'Channel Lowongan Kerja' },
]
// Duplikasi untuk seamless marquee loop
const loopedTrustBadges = [...trustBadges, ...trustBadges]

const painPoints = [
    { icon: 'fa-circle-question',        text: 'Bingung navigasi sistem Coretax yang baru?' },
    { icon: 'fa-file-circle-xmark',      text: 'Takut salah input SPT dan kena sanksi?' },
    { icon: 'fa-magnifying-glass-dollar',text: 'Belum paham mekanisme PPN & faktur pajak?' },
    { icon: 'fa-clock-rotate-left',      text: 'Tidak punya waktu belajar mandiri dari nol?' },
    { icon: 'fa-person-chalkboard',      text: 'Mentor sulit ditemukan, konsultan mahal?' },
    { icon: 'fa-certificate',            text: 'Ingin punya sertifikat kompetensi perpajakan?' },
]

const stats = [
    { value: '100.000+', label: 'Alumni Terlatih' },
    { value: '12 SKP',   label: 'Sertifikat Resmi' },
    { value: '10+ Tahun',label: 'Pengalaman Mentor' },
    { value: '4.9 ★',   label: 'Rating Peserta' },
]

const targetAudience = [
    { icon: 'fa-user-tie',       text: 'Fresh Graduate jurusan Akuntansi / Perpajakan / Ekonomi' },
    { icon: 'fa-person-digging', text: 'Staf keuangan, akuntansi, atau admin perusahaan' },
    { icon: 'fa-store',          text: 'Pelaku UMKM yang ingin lapor pajak sendiri' },
    { icon: 'fa-graduation-cap', text: 'Siapapun yang ingin menguasai Coretax dari nol' },
    { icon: 'fa-rotate',         text: 'Tax professional yang butuh update era digital' },
]

const benefits = [
    { icon: 'fa-certificate',  label: 'E-Certificate',    desc: '12 SKP resmi diakui' },
    { icon: 'fa-briefcase',    label: 'Portofolio',       desc: 'Real case study' },
    { icon: 'fa-video',        label: 'Live via Zoom',    desc: 'Mentor profesional' },
    { icon: 'fa-circle-play',  label: 'Recording',        desc: 'Akses rekaman kelas' },
    { icon: 'fa-film',         label: 'Video Materi',     desc: 'Selamanya' },
    { icon: 'fa-comments',     label: 'QnA & Praktik',   desc: 'Langsung dengan mentor' },
    { icon: 'fa-users',        label: 'Community',        desc: 'Grup eksklusif alumni' },
    { icon: 'fa-headset',      label: 'Konsultasi',       desc: 'Tanya jawab mentor' },
    { icon: 'fa-rotate',       label: 'Gratis Mengulang', desc: 'Sampai batch berikutnya' },
    { icon: 'fa-building',     label: 'Lowongan Kerja',   desc: 'Channel khusus alumni' },
]

const materials = [
    { num: '01', topic: 'Coretax',        sub: 'Pengenalan sistem baru DJP' },
    { num: '02', topic: 'PPh Pasal 21',   sub: 'Hitung & lapor pajak karyawan' },
    { num: '03', topic: 'PPh Pasal 22',   sub: 'Pajak transaksi impor & belanja' },
    { num: '04', topic: 'PPh Pasal 23',   sub: 'Jasa, royalti & sewa' },
    { num: '05', topic: 'PPh Pasal 4(2)', sub: 'PPh final & passive income' },
    { num: '06', topic: 'SPT Badan',      sub: 'Pelaporan badan usaha' },
    { num: '07', topic: 'SPT OP',         sub: 'Pelaporan orang pribadi' },
    { num: '08', topic: 'PPN',            sub: 'BKP & JKP, mekanisme PPN' },
    { num: '09', topic: 'SPT Masa PPN',   sub: 'Pelaporan bulanan PPN' },
    { num: '10', topic: 'Faktur Pajak',   sub: 'Buat & upload e-faktur' },
]

// Jadwal (uncomment di template untuk menampilkan section ini)
const schedule = [
    { pertemuan: 'Pertemuan 1–2',  topik: 'Pengenalan Coretax & Navigasi Sistem DJP Terbaru' },
    { pertemuan: 'Pertemuan 3–4',  topik: 'PPh Pasal 21, 22, dan 23 — Praktik Hitung & Setor' },
    { pertemuan: 'Pertemuan 5–6',  topik: 'PPh Pasal 4(2), PPN & Faktur Pajak Digital' },
    { pertemuan: 'Pertemuan 7–8',  topik: 'Pelaporan SPT Orang Pribadi via Coretax' },
    { pertemuan: 'Pertemuan 9–10', topik: 'Pelaporan SPT Badan via Coretax' },
    { pertemuan: 'Pertemuan 11',   topik: 'SPT Masa PPN & Rekonsiliasi' },
    { pertemuan: 'Pertemuan 12',   topik: 'Simulasi Lengkap & Workshop Submit Akhir' },
]

const mentorBadges = ['10+ Tahun Pengalaman', 'Brevet A, B & C', '700+ Sesi Mengajar', 'Konsultan Bersertifikat']

const testimonials = [
    { name: 'Rina S.', role: 'Staf Akuntansi', text: 'Awalnya bingung sama Coretax, tapi setelah bootcamp ini akhirnya paham alur lengkapnya. Mentor sabar banget jelasinnya!' },
    { name: 'Budi H.', role: 'Fresh Graduate',  text: 'Materi lengkap banget dari PPh 21 sampai SPT Badan. Recording-nya membantu banget buat review ulang.' },
    { name: 'Dewi A.', role: 'Pemilik UMKM',    text: 'Sekarang bisa lapor pajak sendiri tanpa takut salah. Worth it banget investasinya!' },
]

const registerSteps = [
    { num: '1', title: 'Klik Tombol Daftar Sekarang', desc: 'Isi formulir pendaftaran online — cukup 2 menit',                            icon: 'fa-cursor' },
    { num: '2', title: 'Lakukan Pembayaran',           desc: 'Transfer sesuai nominal & upload bukti bayar',                               icon: 'fa-credit-card' },
    { num: '3', title: 'Akses Kelas Langsung',         desc: 'Link Zoom & semua materi dikirim ke email sebelum kelas mulai',              icon: 'fa-rocket' },
]

const faqs = [
    {
        q: 'Apakah bootcamp ini cocok untuk pemula?',
        a: 'Sangat cocok! Materi dirancang dari nol. Mentor memandu step-by-step mulai dari pengenalan Coretax hingga submit SPT. Tidak perlu latar belakang perpajakan.',
    },
    {
        q: 'Apakah ada rekaman kelasnya?',
        a: 'Ya. Setiap sesi live direkam dan Anda mendapat akses Recording Zoom + video materi yang bisa ditonton selamanya, kapanpun dan dimanapun.',
    },
    {
        q: 'Apakah peserta mendapat sertifikat?',
        a: 'Ya. Peserta yang menyelesaikan bootcamp mendapatkan E-Certificate 12 SKP yang bisa digunakan sebagai bukti kompetensi profesional.',
    },
    {
        q: 'Apakah bisa konsultasi dengan mentor?',
        a: 'Tentu! Konsultasi bisa dilakukan saat sesi live, sesi QnA khusus, maupun melalui community group eksklusif alumni.',
    },
    {
        q: 'Apakah membahas SPT OP dan Badan sekaligus?',
        a: 'Ya, bootcamp ini membahas lengkap pelaporan SPT Tahunan Orang Pribadi DAN Badan secara end-to-end, termasuk praktik submit via Coretax.',
    },
    {
        q: 'Bagaimana jika belum paham setelah bootcamp?',
        a: 'Tidak perlu khawatir! Ada garansi gratis mengulang di batch berikutnya sampai Anda benar-benar paham dan percaya diri.',
    },
]

const guarantees = ['E-Certificate 12 SKP', 'Garansi Mengulang', 'Mentor Profesional', 'Akses Rekaman Selamanya']

/* ─── Meta Pixel Helpers ──────────────────────────────────── */
function trackFb(event, params) {
    try { if (typeof fbq === 'function') fbq('track', event, params ?? {}) } catch { /* noop */ }
}
function trackFbCustom(event, params) {
    try { if (typeof fbq === 'function') fbq('trackCustom', event, params ?? {}) } catch { /* noop */ }
}
function onDaftar() {
    trackFb('InitiateCheckout', { content_name: 'Bootcamp Coretax', content_category: 'Bootcamp', value: 125000, currency: 'IDR' })
}
function onWhatsapp() {
    trackFb('Contact', { content_name: 'Bootcamp Coretax' })
    trackFbCustom('WhatsAppClick', { content_name: 'Bootcamp Coretax' })
}

/* ─── Modal Pendaftaran ───────────────────────────────────── */
const showModal      = ref(false)
const packets        = ref([])
const packetsLoading = ref(false)
const formLoading    = ref(false)
const formError      = ref('')

const professionOptions = ['Mahasiswa', 'Profesional', 'Wirausaha', 'Pegawai Pemerintah', 'Lainnya']
const provinceOptions = ref([])
const cityOptions = ref([])
const provinceLoading = ref(false)
const cityLoading = ref(false)
const cityEmptyText = ref('Pilih provinsi dulu')
const provinceCodeByName = ref({})
const cityOptionsCache = {}
const channelOptions = [
    'IG UNLOCK', 'IG Teman', 'WhatsApp Message UNLOCK',
    'WhatsApp Message Teman', 'Status WhatsApp Teman/Kolega',
    'Group WhatsApp UNLOCK', 'Group WhatsApp Teman',
    'Email', 'Website', 'Universitas',
]

const form = ref({
    event_id:            cfg.eventId ?? 193,
    packet_id:           cfg.packetId ?? 107,
    name:                '',
    email:               '',
    whatsapp:            '',
    gender:              '',
    age:                 '',
    province:            '',
    city:                '',
    profession:          '',
    channel_information: [],
})

function normalizeAreaName(value) {
    return String(value ?? '')
        .trim()
        .toLowerCase()
        .replace(/^(kabupaten|kota)\s+/i, '')
        .replace(/\s+/g, ' ')
}

function findMatchingAreaName(options, targetName) {
    const normalizedTarget = normalizeAreaName(targetName)
    if (!normalizedTarget) return ''

    return options.find(option => normalizeAreaName(option) === normalizedTarget) ?? ''
}

async function fetchProvinces() {
    if (provinceLoading.value || provinceOptions.value.length > 0) return

    provinceLoading.value = true
    try {
        const res = await window.axios.get('/registration/provinces')
        provinceOptions.value = res.data.map(item => item.nama)
        provinceCodeByName.value = res.data.reduce((acc, item) => {
            acc[normalizeAreaName(item.nama)] = item.kode
            return acc
        }, {})
    } catch {
        provinceOptions.value = []
    } finally {
        provinceLoading.value = false
    }
}

async function fetchCitiesByProvince(provinceName) {
    if (!provinceName) {
        cityOptions.value = []
        cityEmptyText.value = 'Pilih provinsi dulu'
        return
    }

    await fetchProvinces()

    const provinceCode = provinceCodeByName.value[normalizeAreaName(provinceName)]
    if (!provinceCode) {
        cityOptions.value = []
        cityEmptyText.value = 'Provinsi tidak ditemukan'
        return
    }

    if (cityOptionsCache[provinceCode]) {
        cityOptions.value = cityOptionsCache[provinceCode]
        cityEmptyText.value = 'Tidak ada kota/kabupaten'
        return
    }

    cityLoading.value = true
    try {
        const res = await window.axios.get(`/registration/regencies/${provinceCode}`)
        const cities = res.data.map(item => item.nama)
        cityOptionsCache[provinceCode] = cities
        cityOptions.value = cities
        cityEmptyText.value = 'Tidak ada kota/kabupaten'
    } catch {
        cityOptions.value = []
        cityEmptyText.value = 'Gagal memuat kota/kabupaten'
    } finally {
        cityLoading.value = false
    }
}

function handleProvinceOpen() {
    fetchProvinces()
}

async function handleCityOpen() {
    if (!form.value.province) {
        cityOptions.value = []
        cityEmptyText.value = 'Pilih provinsi dulu'
        return
    }

    await fetchCitiesByProvince(form.value.province)
}

watch(() => form.value.province, (newProvince, oldProvince) => {
    if (!showModal.value || newProvince === oldProvince) return

    form.value.city = ''
    cityOptions.value = []
    cityEmptyText.value = newProvince ? 'Klik untuk memuat kota/kabupaten' : 'Pilih provinsi dulu'
})

async function openModal() {
    onDaftar() // pixel tracking
    formError.value  = ''

    // Pre-fill dari user yang sudah login
    if (cfg.user) {
        form.value.name       = cfg.user.name       ?? ''
        form.value.email      = cfg.user.email      ?? ''
        form.value.whatsapp   = cfg.user.whatsapp   ?? ''
        form.value.gender     = cfg.user.gender     ?? ''
        form.value.age        = cfg.user.age != null ? String(cfg.user.age) : ''
        form.value.profession = cfg.user.profession ?? ''
        form.value.province   = cfg.user.province ?? ''
        form.value.city       = ''
    }

    cityOptions.value = []
    cityEmptyText.value = form.value.province ? 'Klik untuk memuat kota/kabupaten' : 'Pilih provinsi dulu'

    showModal.value = true
    document.body.style.overflow = 'hidden'

    if (cfg.user?.province) {
        await fetchCitiesByProvince(cfg.user.province)
        form.value.city = findMatchingAreaName(cityOptions.value, cfg.user.city)
    }

    // Load paket sekali saja
    if (cfg.eventId && packets.value.length === 0) {
        packetsLoading.value = true
        try {
            const res = await window.axios.get(`/registration/packets/${cfg.eventId}`)
            const selectedPacketId = String(cfg.packetId ?? 107)
            packets.value = res.data.filter(packet => String(packet.id) === selectedPacketId)
            const matchedPacket = packets.value.find(packet => String(packet.id) === selectedPacketId)
            if (matchedPacket) {
                form.value.packet_id = String(matchedPacket.id)
            }
        } catch { /* silently fail */ } finally {
            packetsLoading.value = false
        }
    }
}

function closeModal() {
    showModal.value = false
    document.body.style.overflow = ''
}

async function submitForm() {
    formError.value  = ''
    formLoading.value = true
    try {
        const res = await window.axios.post('/ad/register', form.value)
        trackFb('CompleteRegistration', { content_name: 'Bootcamp Coretax', value: 125000, currency: 'IDR' })
        window.location.href = res.data.redirect_url
    } catch (err) {
        const data = err.response?.data
        if (data?.errors) {
            const firstKey = Object.keys(data.errors)[0]
            formError.value = data.errors[firstKey][0]
        } else {
            formError.value = data?.message ?? 'Terjadi kesalahan. Silakan coba lagi.'
        }
    } finally {
        formLoading.value = false
    }
}

/* ─── Lifecycle ───────────────────────────────────────────── */
onMounted(() => {
    // Setup CSRF token untuk axios agar POST request tidak ditolak
    const csrfMeta = document.querySelector('meta[name="csrf-token"]')
    if (csrfMeta) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfMeta.getAttribute('content')
    }

    // Scroll-reveal via IntersectionObserver
    const revealEls = document.querySelectorAll('.reveal')
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target) }
            })
        }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' })
        revealEls.forEach(el => io.observe(el))
    } else {
        revealEls.forEach(el => el.classList.add('in'))
    }

    // Hero visibility untuk sticky mobile CTA
    const hero = document.getElementById('hero')
    if (hero && 'IntersectionObserver' in window) {
        const heroOb = new IntersectionObserver(([e]) => { heroHidden.value = !e.isIntersecting }, { threshold: 0.15 })
        heroOb.observe(hero)
    } else {
        heroHidden.value = true
    }
})
</script>

<template>
<div class="bg-white text-gray-800 font-[var(--font-sans)] antialiased overflow-x-hidden">

    <!-- ═══════════════════════════════════════════════════════════
         TOP NAV (minimal, conversion-only)
         ═══════════════════════════════════════════════════════════ -->
    <header class="sticky top-0 z-40 bg-[var(--c-900)]/95 backdrop-blur-md border-b border-white/[.07]">
        <div class="max-w-[960px] mx-auto px-4 h-14 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-purple-500 to-orange-500 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-calculator text-white text-xs"></i>
                </div>
                <span class="text-white font-bold text-sm tracking-wide truncate">BOOTCAMP CORETAX</span>
            </div>
            <button id="btn-daftar" type="button" @click="openModal"
               class="btn-shimmer inline-flex items-center gap-1.5 text-white text-xs font-bold px-4 py-2.5 rounded-full whitespace-nowrap shadow-lg shadow-orange-500/30">
                Daftar Sekarang <i class="fas fa-arrow-right text-[10px]"></i>
            </button>
        </div>
    </header>


    <!-- ═══════════════════════════════════════════════════════════
         1. HERO
         ═══════════════════════════════════════════════════════════ -->
    <section id="hero" class="relative overflow-hidden bg-[var(--c-900)] pt-14 pb-0">
        <div class="absolute inset-0 grid-pattern pointer-events-none"></div>
        <div class="particle w-80 h-80 bg-purple-500 -top-24 -right-24" style="--dur:7s;--dly:0s"></div>
        <div class="particle w-56 h-56 bg-orange-400 bottom-0 -left-16"  style="--dur:6s;--dly:-2s"></div>
        <div class="particle w-36 h-36 bg-violet-400 top-1/2 left-1/3"   style="--dur:5s;--dly:-1s"></div>

        <div class="relative z-10 max-w-[960px] mx-auto px-4 text-center">

            <!-- Top badge -->
            <div class="fade-up" style="animation-delay:.06s">
                <div class="inline-flex items-center gap-2 bg-orange-500/15 border border-orange-400/25 text-orange-300 text-xs sm:text-sm font-bold px-5 py-2 rounded-full mb-7 uppercase tracking-widest">
                    <span class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></span>
                    Bootcamp Online Live · 11–25 April 2026
                </div>
            </div>

            <!-- Title -->
            <h1 class="fade-up text-[2.8rem] sm:text-6xl lg:text-7xl font-black text-white leading-none tracking-tighter mb-4" style="animation-delay:.16s">
                BOOTCAMP<br>
                <span class="text-gradient">CORETAX</span>
            </h1>

            <!-- Headline -->
            <p class="fade-up text-base sm:text-xl lg:text-2xl font-semibold text-white/80 max-w-2xl mx-auto mb-3 leading-snug" style="animation-delay:.26s">
                Kupas Tuntas SPT Tahunan Orang Pribadi &amp; Badan<br class="hidden sm:block">
                Era Coretax — Dari Nol Sampai Submit
            </p>

            <!-- Sub-headline -->
            <p class="fade-up text-sm sm:text-base text-white/65 max-w-lg mx-auto mb-8" style="animation-delay:.33s">
                Bootcamp live online + praktik langsung bersama mentor profesional berpengalaman 10+ tahun di bidang perpajakan
            </p>

            <!-- Badges row -->
            <div class="fade-up flex flex-wrap justify-center gap-2 mb-8" style="animation-delay:.39s">
                <span v-for="b in heroBadges" :key="b.label"
                      class="inline-flex items-center gap-2 bg-white/[.07] border border-white/[.1] text-white/75 text-xs sm:text-sm px-4 py-2.5 rounded-xl font-medium">
                    <i :class="`far ${b.icon} text-orange-400`"></i> {{ b.label }}
                </span>
            </div>

            <!-- Price -->
            <div class="fade-up mb-9" style="animation-delay:.45s">
                <div class="inline-flex flex-col items-center bg-white/[.05] border border-white/[.09] rounded-2xl px-8 py-5 gap-1.5">
                    <span class="text-white/45 line-through text-sm tracking-wide">Normal Rp525.000</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-4xl sm:text-5xl font-black text-white">Rp125</span>
                        <span class="text-4xl sm:text-5xl font-black text-orange-400">.000</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="bg-gradient-to-r from-orange-500 to-amber-400 text-white text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-widest">
                            Hemat Rp400.000!
                        </span>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="fade-up flex flex-col sm:flex-row items-center justify-center gap-3 mb-12" style="animation-delay:.51s">
                <button type="button" @click="openModal"
                   class="btn-shimmer pulse-ring text-white font-extrabold px-10 py-4 rounded-2xl min-h-[52px] text-base sm:text-lg w-full sm:w-auto inline-flex items-center justify-center gap-2 shadow-xl shadow-orange-500/25">
                    <i class="fas fa-bolt"></i> Daftar Sekarang
                </button>
                <a id="btn-whatsapp" :href="whatsappUrl" target="_blank" rel="noopener" @click="onWhatsapp"
                   class="group inline-flex items-center justify-center gap-2 bg-[#1eb257] hover:bg-[#17a04e] active:scale-[.97] text-white font-bold px-8 py-4 rounded-2xl min-h-[52px] text-base w-full sm:w-auto transition-all duration-200 shadow-lg shadow-green-600/25">
                    <i class="fab fa-whatsapp text-xl transition-transform group-hover:scale-110"></i> Tanya via WhatsApp
                </a>
            </div>

            <!-- Marquee trust bar -->
            <div class="fade-up -mx-4 overflow-hidden border-t border-white/[.06] py-3.5" style="animation-delay:.56s">
                <div class="marquee-track flex gap-8 w-max">
                    <span v-for="(tb, idx) in loopedTrustBadges" :key="idx"
                          class="inline-flex items-center gap-2 text-white/55 text-xs font-medium whitespace-nowrap">
                        <i :class="`fas ${tb.icon} text-orange-400/50`"></i>
                        {{ tb.text }}<span class="text-white/10 ml-5">·</span>
                    </span>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════════════════════════
         2. PAIN POINTS
         ═══════════════════════════════════════════════════════════ -->
    <section class="bg-[var(--c-50)] py-14 md:py-16">
        <div class="max-w-[960px] mx-auto px-4">
            <div class="reveal text-center mb-10">
                <span class="text-xs font-extrabold tracking-widest uppercase text-purple-500 mb-3 block">Apakah kamu mengalami ini?</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800">
                    Coretax Baru — Tapi Masih Bingung?
                </h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 stagger">
                <div v-for="pain in painPoints" :key="pain.text"
                     class="reveal flex items-start gap-3 bg-white border border-purple-100 rounded-2xl p-4 card-lift">
                    <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0">
                        <i :class="`fas ${pain.icon} text-sm`"></i>
                    </div>
                    <p class="text-sm text-gray-700 font-semibold leading-snug pt-1.5">{{ pain.text }}</p>
                </div>
            </div>
            <div class="reveal mt-8 text-center">
                <p class="text-base sm:text-lg font-semibold text-gray-700">
                    Kalau iya, <span class="text-orange-500 font-extrabold">Bootcamp Coretax ini solusinya.</span>
                </p>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════════════════════════
         3. STATS STRIP
         ═══════════════════════════════════════════════════════════ -->
    <section class="py-10 bg-gradient-to-r from-[var(--c-800)] via-[var(--c-700)] to-orange-600">
        <div class="max-w-[960px] mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center stagger">
                <div v-for="s in stats" :key="s.label" class="reveal">
                    <div class="text-2xl sm:text-3xl font-extrabold text-white mb-0.5">{{ s.value }}</div>
                    <div class="text-white/55 text-xs sm:text-sm font-medium">{{ s.label }}</div>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════════════════════════
         4. TARGET AUDIENCE
         ═══════════════════════════════════════════════════════════ -->
    <section class="py-14 md:py-20 bg-white">
        <div class="max-w-[960px] mx-auto px-4">
            <div class="reveal text-center mb-10">
                <span class="text-xs font-extrabold tracking-widest uppercase text-orange-500 mb-3 block">Cocok untuk siapa?</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">
                    Bootcamp Ini Dirancang Untuk Kamu
                </h2>
                <p class="text-gray-500 text-sm sm:text-base max-w-sm mx-auto">
                    Tidak perlu background perpajakan. Yang penting: mau belajar.
                </p>
            </div>
            <div class="max-w-2xl mx-auto space-y-3 stagger">
                <div v-for="t in targetAudience" :key="t.text"
                     class="reveal flex items-center gap-4 bg-gradient-to-r from-[var(--c-50)] to-white border border-purple-100/50 rounded-2xl px-5 py-4 card-lift">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[var(--c-800)] to-[var(--c-600)] text-white flex items-center justify-center flex-shrink-0 shadow-md shadow-purple-200/60">
                        <i :class="`fas ${t.icon} text-sm`"></i>
                    </div>
                    <p class="text-sm sm:text-base font-semibold text-gray-700 flex-1">{{ t.text }}</p>
                    <i class="fas fa-check-circle text-green-500 flex-shrink-0"></i>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════════════════════════
         5. BENEFITS
         ═══════════════════════════════════════════════════════════ -->
    <section id="benefits" class="py-14 md:py-20 bg-[var(--c-50)]">
        <div class="max-w-[960px] mx-auto px-4">
            <div class="reveal text-center mb-10">
                <span class="text-xs font-extrabold tracking-widest uppercase text-purple-500 mb-3 block">Yang kamu dapatkan</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">
                    Paket Lengkap untuk Karier Perpajakan
                </h2>
                <p class="text-gray-500 text-sm sm:text-base max-w-md mx-auto">
                    Bukan sekadar kelas biasa — ekosistem belajar yang memastikan kamu benar-benar paham
                </p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 stagger">
                <div v-for="b in benefits" :key="b.label"
                     class="reveal group flex flex-col items-center text-center gap-2.5 bg-white border border-purple-100/40 hover:border-purple-300/50 rounded-2xl p-4 card-lift shadow-sm shadow-purple-50/50">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-700 flex items-center justify-center group-hover:bg-gradient-to-br group-hover:from-[var(--c-800)] group-hover:to-[var(--c-600)] group-hover:text-white transition-all duration-300 shadow-sm group-hover:shadow-lg group-hover:shadow-purple-300/30">
                        <i :class="`fas ${b.icon} text-lg`"></i>
                    </div>
                    <div>
                        <p class="text-xs sm:text-sm font-bold text-gray-800 leading-snug">{{ b.label }}</p>
                        <p class="text-[11px] text-gray-400 mt-0.5 leading-tight">{{ b.desc }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════════════════════════
         6. MATERI
         ═══════════════════════════════════════════════════════════ -->
    <section id="materi" class="py-14 md:py-20 bg-white">
        <div class="max-w-[960px] mx-auto px-4">
            <div class="reveal text-center mb-10">
                <span class="text-xs font-extrabold tracking-widest uppercase text-orange-500 mb-3 block">Kurikulum</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">
                    10 Materi Lengkap Era Coretax
                </h2>
                <p class="text-gray-500 text-sm sm:text-base max-w-md mx-auto">
                    Semua yang dibutuhkan untuk pelaporan pajak di era sistem DJP terbaru
                </p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 stagger">
                <div v-for="m in materials" :key="m.num"
                     class="reveal flex items-center gap-4 bg-gray-50 hover:bg-[var(--c-50)] border border-gray-100 hover:border-purple-200 rounded-2xl px-5 py-4 transition-all duration-200 card-lift group">
                    <span class="text-sm font-extrabold text-purple-200 group-hover:text-purple-400 transition-colors w-7 flex-shrink-0 font-mono">{{ m.num }}</span>
                    <div class="w-px h-8 bg-purple-100 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-800 group-hover:text-[var(--c-700)] transition-colors">{{ m.topic }}</p>
                        <p class="text-xs text-gray-400 mt-0.5 truncate">{{ m.sub }}</p>
                    </div>
                    <i class="fas fa-chevron-right text-xs text-purple-200 group-hover:text-purple-400 group-hover:translate-x-1 transition-all duration-200 flex-shrink-0"></i>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════════════════════════
         7. JADWAL / TIMELINE
         Uncomment section di bawah untuk menampilkan jadwal
         ═══════════════════════════════════════════════════════════ -->
    <!--
    <section class="py-14 md:py-20 bg-[var(--c-50)]">
        <div class="max-w-[720px] mx-auto px-4">
            <div class="reveal text-center mb-10">
                <span class="text-xs font-extrabold tracking-widest uppercase text-purple-500 mb-3 block">Jadwal Bootcamp</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-1.5">
                    12 Pertemuan, 11–25 April 2026
                </h2>
                <p class="text-gray-500 text-sm">Senin, Rabu & Jumat · 09:00–12:00 WIB via Zoom</p>
            </div>
            <div class="relative stagger">
                <div class="absolute left-[19px] sm:left-[21px] top-5 bottom-5 w-0.5 timeline-line rounded-full z-0"></div>
                <div class="space-y-3">
                    <div v-for="(s, i) in schedule" :key="i" class="reveal flex items-start gap-4 relative z-10">
                        <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center font-extrabold text-xs z-10 transition-all duration-200"
                             :class="i === 0 ? 'bg-gradient-to-br from-[var(--c-800)] to-[var(--c-600)] text-white shadow-lg shadow-purple-400/30 ring-4 ring-purple-200/40' : 'bg-white border-2 border-purple-200 text-purple-400 hover:border-purple-400 hover:text-purple-600'">
                            {{ i + 1 }}
                        </div>
                        <div class="flex-1 bg-white border border-purple-100/70 rounded-2xl px-4 py-3.5 shadow-sm card-lift">
                            <span class="text-[10px] font-extrabold text-orange-500 uppercase tracking-widest">{{ s.pertemuan }}</span>
                            <p class="text-sm font-semibold text-gray-800 mt-0.5 leading-snug">{{ s.topik }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    -->


    <!-- ═══════════════════════════════════════════════════════════
         8. MENTOR / SPEAKER
         ═══════════════════════════════════════════════════════════ -->
    <section id="speaker" class="py-14 md:py-20 bg-white">
        <div class="max-w-[960px] mx-auto px-4">
            <div class="reveal text-center mb-10">
                <span class="text-xs font-extrabold tracking-widest uppercase text-purple-500 mb-3 block">Mentor</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800">Belajar dari Praktisi Terbaik</h2>
            </div>
            <div class="max-w-lg mx-auto">
                <div class="reveal card-lift relative overflow-hidden rounded-3xl bg-gradient-to-br from-[var(--c-900)] via-[var(--c-800)] to-[var(--c-700)] p-8 text-center">
                    <div class="absolute -top-12 -right-12 w-44 h-44 bg-orange-500/10 rounded-full blur-2xl"></div>
                    <div class="absolute -bottom-12 -left-12 w-44 h-44 bg-purple-400/10 rounded-full blur-2xl"></div>
                    <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>

                    <!-- Avatar -->
                    <div class="relative inline-flex mb-5">
                        <img src="/storage/avatars/1640232585295.jpg" alt="Lili Fajri Dailimi" class="w-28 h-28 rounded-full object-cover shadow-2xl shadow-purple-900/50 animate-float ring-4 ring-white/10" />
                        <div class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full bg-green-400 border-2 border-[var(--c-800)] flex items-center justify-center">
                            <i class="fas fa-check text-white text-xs"></i>
                        </div>
                    </div>

                    <h3 class="text-xl font-extrabold text-white mb-1">Lili Fajri Dailimi SEI, MA, BKP</h3>
                    <p class="text-orange-400 font-semibold text-sm mb-5">Tax Practitioner & Certified Tax Consultant</p>

                    <div class="flex flex-wrap justify-center gap-2 relative z-10">
                        <span v-for="badge in mentorBadges" :key="badge"
                              class="inline-flex items-center gap-1.5 bg-white/[.08] border border-white/[.12] text-white/75 text-xs font-semibold px-3.5 py-1.5 rounded-full">
                            <i class="fas fa-check text-orange-400 text-[10px]"></i> {{ badge }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════════════════════════
         9. TESTIMONIALS
         ═══════════════════════════════════════════════════════════ -->
    <section class="py-14 md:py-20 bg-[var(--c-50)]">
        <div class="max-w-[960px] mx-auto px-4">
            <div class="reveal text-center mb-10">
                <span class="text-xs font-extrabold tracking-widest uppercase text-orange-500 mb-3 block">Kata Alumni</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800">Mereka Sudah Membuktikannya</h2>
                <p class="text-gray-500 text-sm mt-2">Lebih dari 100.000 peserta telah belajar bersama kami</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 stagger">
                <div v-for="t in testimonials" :key="t.name"
                     class="reveal flex flex-col gap-4 bg-white rounded-2xl p-6 border border-purple-100/50 shadow-sm shadow-purple-50/80 card-lift">
                    <div class="flex gap-0.5">
                        <i v-for="n in 5" :key="n" class="fas fa-star text-amber-400 text-xs"></i>
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed flex-1">"{{ t.text }}"</p>
                    <div class="flex items-center gap-3 border-t border-gray-50 pt-4">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-purple-200 to-purple-100 flex items-center justify-center text-sm font-extrabold text-purple-700 flex-shrink-0">
                            {{ t.name.charAt(0) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800 leading-tight">{{ t.name }}</p>
                            <p class="text-xs text-gray-400">{{ t.role }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════════════════════════
         10. CARA DAFTAR
         ═══════════════════════════════════════════════════════════ -->
    <section class="py-14 md:py-20 bg-white">
        <div class="max-w-[700px] mx-auto px-4">
            <div class="reveal text-center mb-10">
                <span class="text-xs font-extrabold tracking-widest uppercase text-purple-500 mb-3 block">Cara Daftar</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800">Mudah dalam 3 Langkah</h2>
            </div>
            <div class="space-y-3.5 stagger">
                <div v-for="step in registerSteps" :key="step.num"
                     class="reveal flex items-start gap-4 bg-gradient-to-r from-purple-50/60 to-white border border-purple-100 rounded-2xl px-5 py-[18px] card-lift">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[var(--c-800)] to-[var(--c-600)] text-white font-black text-lg flex items-center justify-center flex-shrink-0 shadow-lg shadow-purple-300/30">
                        {{ step.num }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-gray-800 text-sm sm:text-base leading-snug">{{ step.title }}</p>
                        <p class="text-xs sm:text-sm text-gray-500 mt-0.5">{{ step.desc }}</p>
                    </div>
                    <i :class="`fas ${step.icon} text-purple-200 mt-1 flex-shrink-0`"></i>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════════════════════════
         11. FAQ
         ═══════════════════════════════════════════════════════════ -->
    <section id="faq" class="py-14 md:py-20 bg-[var(--c-50)]">
        <div class="max-w-[700px] mx-auto px-4">
            <div class="reveal text-center mb-10">
                <span class="text-xs font-extrabold tracking-widest uppercase text-purple-500 mb-3 block">FAQ</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800">Pertanyaan yang Sering Ditanyakan</h2>
            </div>

            <div class="space-y-2.5 stagger">
                <div v-for="(faq, i) in faqs" :key="i"
                     class="reveal bg-white rounded-2xl border border-purple-100/60 shadow-sm overflow-hidden card-lift">
                    <button @click="toggleFaq(i)"
                            class="w-full flex items-start justify-between gap-4 px-5 py-4 text-left cursor-pointer group">
                        <span class="font-semibold text-sm sm:text-base text-gray-800 group-hover:text-[var(--c-700)] transition-colors duration-200 leading-snug pt-0.5">
                            {{ faq.q }}
                        </span>
                        <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 transition-all duration-300"
                             :class="openFaq === i ? 'bg-purple-100 rotate-180' : 'bg-gray-50 group-hover:bg-purple-50'">
                            <i class="fas fa-chevron-down text-xs text-purple-400"></i>
                        </div>
                    </button>
                    <Transition name="faq">
                        <div v-if="openFaq === i"
                             class="px-5 pb-5 pt-1 text-sm text-gray-600 leading-relaxed border-t border-purple-50">
                            {{ faq.a }}
                        </div>
                    </Transition>
                </div>
            </div>

            <div class="reveal mt-8 text-center">
                <p class="text-sm text-gray-500 mb-3">Masih ada pertanyaan lain?</p>
                <a :href="whatsappUrl" target="_blank" rel="noopener" @click="onWhatsapp"
                   class="inline-flex items-center gap-2 bg-[#1eb257] hover:bg-[#17a04e] text-white font-bold px-6 py-3 rounded-xl text-sm min-h-[44px] transition-all duration-200 shadow-lg shadow-green-600/20 hover:-translate-y-0.5">
                    <i class="fab fa-whatsapp text-lg"></i> Tanya Langsung via WhatsApp
                </a>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════════════════════════
         12. FINAL CTA
         ═══════════════════════════════════════════════════════════ -->
    <section id="daftar" class="relative overflow-hidden bg-[var(--c-900)] py-20 md:py-28">
        <div class="absolute inset-0 dot-pattern pointer-events-none opacity-60"></div>
        <div class="absolute top-0 left-0 w-80 h-80 bg-purple-600/15 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-80 h-80 bg-orange-500/10 rounded-full blur-3xl translate-x-1/2 translate-y-1/2"></div>
        <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>

        <div class="relative z-10 max-w-[700px] mx-auto px-4 text-center">
            <div class="reveal">
                <span class="inline-flex items-center gap-2 bg-orange-500/15 border border-orange-500/20 text-orange-400 text-xs font-bold px-4 py-2 rounded-full uppercase tracking-widest mb-6">
                    <i class="fas fa-clock animate-pulse"></i> Penawaran Terbatas — Jangan Lewatkan!
                </span>
            </div>

            <h2 class="reveal text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white mb-3 leading-tight" style="transition-delay:80ms">
                Kuasai Coretax Sekarang,<br>
                <span class="text-gradient">Sebelum Terlambat.</span>
            </h2>

            <p class="reveal text-white/65 text-sm sm:text-base mb-8 max-w-md mx-auto" style="transition-delay:140ms">
                Deadline pajak terus berjalan. Jangan sampai Coretax jadi hambatan karier dan bisnis kamu.
            </p>

            <div class="reveal mb-8" style="transition-delay:200ms">
                <div class="inline-flex flex-col items-center bg-white/[.05] border border-white/[.09] rounded-2xl px-10 py-5 gap-1.5">
                    <span class="text-white/45 line-through text-xs tracking-wider">Normal Rp525.000</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-4xl font-black text-white">Rp125</span>
                        <span class="text-4xl font-black text-orange-400">.000</span>
                    </div>
                    <span class="text-xs text-orange-400/70 font-semibold">✦ Hemat Rp400.000 — Promo terbatas!</span>
                </div>
            </div>

            <div class="reveal flex flex-col sm:flex-row items-center justify-center gap-3 mb-8" style="transition-delay:260ms">
                <button type="button" @click="openModal"
                   class="btn-shimmer pulse-ring text-white font-extrabold px-10 py-4 rounded-2xl min-h-[52px] text-base sm:text-lg w-full sm:w-auto inline-flex items-center justify-center gap-2 shadow-xl shadow-orange-500/30">
                    <i class="fas fa-bolt"></i> Daftar Sekarang
                </button>
                <a :href="whatsappUrl" target="_blank" rel="noopener" @click="onWhatsapp"
                   class="inline-flex items-center justify-center gap-2 bg-[#1eb257] hover:bg-[#17a04e] active:scale-[.97] text-white font-bold px-8 py-4 rounded-2xl min-h-[52px] text-base w-full sm:w-auto transition-all duration-200 shadow-lg shadow-green-600/25">
                    <i class="fab fa-whatsapp text-xl"></i> Chat WhatsApp
                </a>
            </div>

            <div class="reveal flex flex-wrap justify-center gap-x-6 gap-y-2" style="transition-delay:320ms">
                <span v-for="g in guarantees" :key="g" class="flex items-center gap-1.5 text-white/45 text-xs">
                    <i class="fas fa-check text-green-400/70 text-[10px]"></i> {{ g }}
                </span>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════════════════════════
         FOOTER MINIMAL
         ═══════════════════════════════════════════════════════════ -->
    <footer class="bg-[var(--c-900)] border-t border-white/[.05] py-6">
        <div class="max-w-[960px] mx-auto px-4 text-center">
            <p class="text-white/70 text-xs">
                © {{ currentYear }} Unlock Indonesia ·
                <a href="/policy" class="hover:text-white/40 transition-colors underline underline-offset-2">Kebijakan Privasi</a>
            </p>
        </div>
    </footer>


    <!-- ═══════════════════════════════════════════════════════════
         STICKY MOBILE CTA
         ═══════════════════════════════════════════════════════════ -->
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-full opacity-0">
        <div v-if="heroHidden" class="fixed bottom-0 inset-x-0 z-50 lg:hidden pb-safe">
            <div class="bg-white/97 backdrop-blur-xl border-t border-gray-200/70 px-3 py-2.5 flex gap-2 shadow-2xl shadow-black/15">
                <button type="button" @click="openModal"
                   class="btn-shimmer flex-1 inline-flex items-center justify-center gap-2 text-white font-extrabold py-3 rounded-xl text-sm min-h-[44px]">
                    <i class="fas fa-bolt text-xs"></i> Daftar Sekarang
                </button>
                <a :href="whatsappUrl" target="_blank" rel="noopener" @click="onWhatsapp"
                   class="inline-flex items-center justify-center bg-[#1eb257] hover:bg-[#17a04e] text-white font-bold px-4 py-3 rounded-xl text-sm min-h-[44px] transition-colors w-14 flex-shrink-0">
                    <i class="fab fa-whatsapp text-xl"></i>
                </a>
            </div>
        </div>
    </Transition>

    <!-- Sticky bar spacer -->
    <div class="h-[68px] lg:hidden"></div>


    <!-- ═══════════════════════════════════════════════════════════
         FLOATING WHATSAPP BUTTON (Desktop)
         ═══════════════════════════════════════════════════════════ -->
    <a :href="whatsappUrl" target="_blank" rel="noopener" @click="onWhatsapp"
       class="hidden lg:inline-flex fixed bottom-6 right-6 z-50 items-center gap-2.5 bg-[#1eb257] hover:bg-[#17a04e] text-white font-bold px-5 py-3.5 rounded-2xl shadow-2xl shadow-green-600/35 transition-all duration-200 hover:-translate-y-1 hover:shadow-green-600/45 active:scale-95">
        <i class="fab fa-whatsapp text-xl"></i>
        <span class="text-sm">Chat WhatsApp</span>
    </a>


    <!-- ═══════════════════════════════════════════════════════════
         MODAL PENDAFTARAN
         ═══════════════════════════════════════════════════════════ -->
    <Transition name="modal-bd">
        <div v-if="showModal"
             class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center"
             @keydown.esc.window="closeModal">

            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal"></div>

            <!-- Dialog -->
            <Transition name="modal-slide">
                <div v-if="showModal"
                     class="relative w-full sm:max-w-md max-h-[95dvh] sm:max-h-[88vh] flex flex-col bg-white sm:rounded-3xl rounded-t-3xl shadow-2xl overflow-hidden">

                    <!-- Header -->
                    <div class="relative bg-gradient-to-br from-[var(--c-900)] via-[var(--c-800)] to-[var(--c-700)] px-6 py-5 flex-shrink-0">
                        <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
                        <button @click="closeModal" type="button"
                                class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-colors" aria-label="Tutup">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center flex-shrink-0 shadow-lg">
                                <i class="fas fa-calculator text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="text-orange-300 text-[10px] font-bold uppercase tracking-widest">Pendaftaran Bootcamp</p>
                                <h3 class="text-white font-extrabold text-base leading-tight">Bootcamp Coretax 2026</h3>
                            </div>
                        </div>
                    </div>

                    <!-- ── FORM ─── -->
                    <form @submit.prevent="submitForm" novalidate class="flex-1 overflow-y-auto flex flex-col min-h-0">
                        <div class="p-5 space-y-4 flex-1">

                            <!-- Paket (hanya tampil jika ada) -->
                            <div v-if="!packetsLoading && packets.length > 0">
                                <label class="block text-xs font-bold text-gray-700 mb-2">Pilih Paket <span class="text-red-500">*</span></label>
                                <div class="space-y-2">
                                    <label v-for="p in packets" :key="p.id"
                                           class="flex items-center gap-3 border-2 rounded-xl p-3.5 cursor-pointer transition-all duration-200"
                                           :class="String(form.packet_id) === String(p.id) ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-300'">
                                        <input type="radio" :value="String(p.id)" v-model="form.packet_id" class="accent-purple-600 flex-shrink-0" required>
                                        <p class="font-bold text-sm text-gray-800 flex-1 min-w-0">{{ p.packet_name }}</p>
                                    </label>
                                </div>
                            </div>

                            <!-- Nama -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input v-model="form.name" type="text" required placeholder="Masukkan nama lengkap Anda"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-purple-400 focus:ring-2 focus:ring-purple-100 transition">
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                                <input v-model="form.email" type="email" required placeholder="email@contoh.com"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-purple-400 focus:ring-2 focus:ring-purple-100 transition">
                            </div>

                            <!-- WhatsApp -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Nomor WhatsApp <span class="text-red-500">*</span></label>
                                <input v-model="form.whatsapp" type="tel" required placeholder="08xxxxxxxxxx"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-purple-400 focus:ring-2 focus:ring-purple-100 transition">
                            </div>

                            <!-- Gender -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                                <div class="flex gap-3">
                                    <label class="flex-1 flex items-center gap-2.5 border-2 rounded-xl px-4 py-3 cursor-pointer transition-all duration-200"
                                           :class="form.gender === 'male' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-300'">
                                        <input type="radio" value="male" v-model="form.gender" class="accent-purple-600">
                                        <span class="text-sm font-semibold text-gray-700">Laki-laki</span>
                                    </label>
                                    <label class="flex-1 flex items-center gap-2.5 border-2 rounded-xl px-4 py-3 cursor-pointer transition-all duration-200"
                                           :class="form.gender === 'female' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-300'">
                                        <input type="radio" value="female" v-model="form.gender" class="accent-purple-600">
                                        <span class="text-sm font-semibold text-gray-700">Perempuan</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Usia -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Usia <span class="text-red-500">*</span></label>
                                <input v-model="form.age" type="number" required min="10" max="100" placeholder="Usia Anda"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-purple-400 focus:ring-2 focus:ring-purple-100 transition">
                            </div>

                            <!-- Provinsi -->
                            <SearchableSelect v-model="form.province" :options="provinceOptions"
                                              label="Provinsi" placeholder="Cari provinsi..." :required="true"
                                              :loading="provinceLoading" empty-text="Provinsi tidak ditemukan"
                                              @open="handleProvinceOpen" />

                            <!-- Kota -->
                            <SearchableSelect v-model="form.city" :options="cityOptions"
                                              label="Kota/Kabupaten" placeholder="Cari kota..." :required="true"
                                              :loading="cityLoading" :empty-text="cityEmptyText"
                                              @open="handleCityOpen" />

                            <!-- Profesi -->
                            <SearchableSelect v-model="form.profession" :options="professionOptions"
                                              label="Profesi" placeholder="Pilih profesi..." :required="true" />

                            <!-- Sumber Info -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2">
                                    Darimana tahu event ini? <span class="text-red-500">*</span>
                                    <span class="text-gray-400 font-normal ml-1">(min. 1)</span>
                                </label>
                                <div class="grid grid-cols-2 gap-1.5">
                                    <label v-for="ch in channelOptions" :key="ch"
                                           class="flex items-center gap-2 rounded-xl px-3 py-2 cursor-pointer border transition-all duration-150 text-xs"
                                           :class="form.channel_information.includes(ch) ? 'border-purple-300 bg-purple-50 text-purple-700 font-semibold' : 'border-gray-100 hover:border-purple-200 text-gray-600'">
                                        <input type="checkbox" :value="ch" v-model="form.channel_information" class="accent-purple-600 flex-shrink-0">
                                        {{ ch }}
                                    </label>
                                </div>
                            </div>

                            <!-- Error alert -->
                            <div v-if="formError" class="flex items-start gap-2.5 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
                                <i class="fas fa-exclamation-circle text-red-400 mt-0.5 flex-shrink-0"></i>
                                <p class="text-sm text-red-600">{{ formError }}</p>
                            </div>

                        </div>

                        <!-- Footer sticky -->
                        <div class="sticky bottom-0 bg-white border-t border-gray-100 px-5 py-4 flex-shrink-0">
                            <button type="submit" :disabled="formLoading || packetsLoading"
                                    class="btn-shimmer w-full inline-flex items-center justify-center gap-2.5 text-white font-extrabold py-4 rounded-2xl text-base shadow-lg shadow-orange-500/25 disabled:opacity-70 disabled:cursor-not-allowed transition-opacity">
                                <i v-if="formLoading" class="fas fa-circle-notch fa-spin"></i>
                                <i v-else class="fas fa-bolt"></i>
                                {{ formLoading ? 'Mendaftar...' : 'Daftar Sekarang — Rp125.000' }}
                            </button>
                            <p class="text-center text-[10px] text-gray-400 mt-2">
                                <i class="fas fa-lock text-[9px]"></i> Data kamu aman &amp; tidak disebarkan
                            </p>
                        </div>
                    </form>

                </div>
            </Transition>
        </div>
    </Transition>

</div>
</template>

<style>
/* ── Color primitives ── */
:root {
    --c-900: #2D004A;
    --c-800: #46006D;
    --c-700: #5B1A87;
    --c-600: #7c3aed;
    --c-100: #f3e8ff;
    --c-50:  #faf5ff;
    --orange: #F97316;
}

/* ── Scroll-reveal ── */
.reveal {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity .7s cubic-bezier(.22,.61,.36,1),
                transform .7s cubic-bezier(.22,.61,.36,1);
}
.reveal.in { opacity: 1; transform: translateY(0); }

/* ── Hero on-load ── */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
}
.fade-up { animation: fadeUp .75s cubic-bezier(.22,.61,.36,1) both; }

/* ── Float ── */
@keyframes float {
    0%,100% { transform: translateY(0px); }
    50%      { transform: translateY(-12px); }
}
.animate-float { animation: float 5s ease-in-out infinite; }

/* ── Shimmer button ── */
@keyframes shimmer {
    0%   { background-position: -200% center; }
    100% { background-position:  200% center; }
}
.btn-shimmer {
    background: linear-gradient(90deg,#f97316 0%,#fb923c 35%,#fbbf24 50%,#fb923c 65%,#f97316 100%);
    background-size: 200% auto;
    animation: shimmer 3s linear infinite;
    transition: transform .15s ease, box-shadow .15s ease;
}
.btn-shimmer:hover  { animation-duration: 1.1s; transform: translateY(-2px); box-shadow: 0 12px 32px -6px rgba(249,115,22,.5); }
.btn-shimmer:active { transform: scale(.97); }

/* ── Pulse ring ── */
@keyframes ring {
    0%   { box-shadow: 0 0 0 0 rgba(249,115,22,.5); }
    70%  { box-shadow: 0 0 0 16px rgba(249,115,22,0); }
    100% { box-shadow: 0 0 0 0   rgba(249,115,22,0); }
}
.pulse-ring { animation: ring 2.4s ease-out infinite; }

/* ── Marquee ── */
@keyframes marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }
.marquee-track { animation: marquee 24s linear infinite; }
.marquee-track:hover { animation-play-state: paused; }

/* ── Card hover lift ── */
.card-lift {
    transition: transform .25s cubic-bezier(.22,.61,.36,1),
                box-shadow .25s cubic-bezier(.22,.61,.36,1);
}
.card-lift:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -12px rgba(70,0,109,.15); }

/* ── Gradient text ── */
.text-gradient {
    background: linear-gradient(120deg, #c084fc 0%, #f97316 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* ── Dot pattern ── */
.dot-pattern {
    background-image: radial-gradient(rgba(249,115,22,.22) 1px, transparent 1px);
    background-size: 28px 28px;
}
.grid-pattern {
    background-image:
        linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
    background-size: 40px 40px;
}

/* ── Stagger ── */
.stagger .reveal:nth-child(1)  { transition-delay:  40ms; }
.stagger .reveal:nth-child(2)  { transition-delay:  90ms; }
.stagger .reveal:nth-child(3)  { transition-delay: 140ms; }
.stagger .reveal:nth-child(4)  { transition-delay: 190ms; }
.stagger .reveal:nth-child(5)  { transition-delay: 240ms; }
.stagger .reveal:nth-child(6)  { transition-delay: 290ms; }
.stagger .reveal:nth-child(7)  { transition-delay: 340ms; }
.stagger .reveal:nth-child(8)  { transition-delay: 390ms; }
.stagger .reveal:nth-child(9)  { transition-delay: 440ms; }
.stagger .reveal:nth-child(10) { transition-delay: 490ms; }

/* ── Timeline ── */
.timeline-line { background: linear-gradient(to bottom, var(--c-800), var(--orange)); }

/* ── Safe area bottom ── */
.pb-safe { padding-bottom: env(safe-area-inset-bottom, 0px); }

/* ── Particle ── */
.particle {
    position: absolute; border-radius: 50%; pointer-events: none;
    opacity: .07;
    animation: float var(--dur,6s) ease-in-out infinite;
    animation-delay: var(--dly,0s);
}

/* ── FAQ transition ── */
.faq-enter-active { transition: opacity 0.25s ease, transform 0.25s ease; }
.faq-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.faq-enter-from   { opacity: 0; transform: translateY(-4px); }
.faq-leave-to     { opacity: 0; transform: translateY(-4px); }

/* ── Modal backdrop transition ── */
.modal-bd-enter-active { transition: opacity 0.25s ease; }
.modal-bd-leave-active { transition: opacity 0.2s ease; }
.modal-bd-enter-from, .modal-bd-leave-to { opacity: 0; }

/* ── Modal slide-up (mobile) / scale-in (desktop) transition ── */
.modal-slide-enter-active { transition: transform 0.3s cubic-bezier(.22,.61,.36,1), opacity 0.25s ease; }
.modal-slide-leave-active { transition: transform 0.2s ease, opacity 0.18s ease; }
.modal-slide-enter-from   { transform: translateY(100%); opacity: 0; }
.modal-slide-leave-to     { transform: translateY(60px); opacity: 0; }
@media (min-width: 640px) {
    .modal-slide-enter-from { transform: scale(0.95) translateY(8px); }
    .modal-slide-leave-to   { transform: scale(0.97) translateY(4px); }
}
</style>
