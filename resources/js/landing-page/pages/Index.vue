<template>
  <div class="h-screen flex flex-col overflow-hidden bg-gray-100">
    <!-- Top Bar -->
    <header class="h-12 bg-white border-b flex items-center justify-between px-4 shrink-0 z-20">
      <div class="flex items-center gap-3">
        <a href="/upanel" class="text-gray-500 hover:text-gray-800 transition text-sm">
          <i class="fas fa-arrow-left mr-1"></i> Admin
        </a>
        <span class="text-gray-300">|</span>
        <span class="font-semibold text-gray-800 text-sm">Landing Page Builder</span>
      </div>
      <div class="flex items-center gap-2">
        <span v-if="isDirty" class="text-xs text-amber-600 mr-2"><i class="fas fa-circle text-[6px] mr-1"></i>Belum disimpan</span>
        <button v-if="selected" @click="openPreview" :disabled="!selected.slug"
                class="px-3 py-1.5 text-xs bg-white text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition flex items-center gap-1.5 disabled:opacity-50">
          <i class="fas fa-external-link-alt"></i>
          Preview
        </button>
        <button v-if="selected" @click="copyPreviewUrl" :disabled="!selected.slug"
                class="px-3 py-1.5 text-xs bg-white text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition flex items-center gap-1.5 disabled:opacity-50">
          <i class="fas fa-copy"></i>
          Salin Alamat
        </button>
        <button v-if="selected" @click="savePage" :disabled="saving"
                class="px-4 py-1.5 text-xs bg-primary text-white rounded-lg hover:bg-secondary transition flex items-center gap-1.5 disabled:opacity-50">
          <i :class="saving ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i>
          {{ saving ? 'Menyimpan...' : 'Simpan' }}
        </button>
      </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
      <!-- LEFT PANEL: Page List -->
      <aside class="w-60 bg-white border-r flex flex-col shrink-0">
        <div class="p-3 border-b">
          <button @click="createPage" :disabled="creating"
                  class="w-full py-2 text-xs bg-primary text-white rounded-lg hover:bg-secondary transition flex items-center justify-center gap-1.5 disabled:opacity-50">
            <i :class="creating ? 'fas fa-spinner fa-spin' : 'fas fa-plus'"></i>
            {{ creating ? 'Membuat...' : 'Tambah Halaman' }}
          </button>
        </div>
        <div class="flex-1 overflow-y-auto">
          <div v-for="page in pages" :key="page.id"
               @click="selectPage(page)"
               class="px-3 py-2.5 border-b border-gray-50 cursor-pointer transition text-sm"
               :class="selected?.id === page.id ? 'bg-primary/5 border-l-2 border-l-primary' : 'hover:bg-gray-50'">
            <div class="font-medium text-gray-800 truncate text-xs">{{ page.name }}</div>
            <div class="flex items-center gap-2 mt-0.5">
              <span class="text-[10px] px-1.5 py-0.5 rounded-full"
                    :class="page.status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                {{ page.status }}
              </span>
              <span class="text-[10px] text-gray-400">{{ page.clicks_count || 0 }} clicks</span>
            </div>
          </div>
          <div v-if="!pages.length" class="p-6 text-center text-gray-400 text-xs">
            Belum ada halaman
          </div>
        </div>
      </aside>

      <!-- CENTER: Preview -->
      <main class="flex-1 flex items-center justify-center overflow-auto p-6 bg-gray-100">
        <div v-if="selected && editData" class="relative">
          <!-- Phone frame -->
          <div class="w-[480px] min-h-[720px] bg-white rounded-[2.5rem] shadow-2xl border-[6px] border-gray-800 overflow-hidden relative">
            <div class="h-6 bg-gray-800 flex items-center justify-center">
              <div class="w-16 h-3 bg-gray-700 rounded-full"></div>
            </div>
            <div class="overflow-y-auto" style="max-height: 640px;">
              <Preview :data="editData" />
            </div>
          </div>
        </div>
        <div v-else class="text-center text-gray-400">
          <i class="fas fa-mobile-alt text-5xl mb-4 opacity-30"></i>
          <p class="text-sm">Pilih atau buat landing page untuk melihat preview</p>
        </div>
      </main>

      <!-- RIGHT PANEL: Tools -->
      <aside v-if="selected && editData" class="w-100 bg-white border-l overflow-y-auto shrink-0">
        <ToolPanel
          :data="editData"
          :page-id="selected.id"
          @upload-image="handleUploadImage"
          @remove-image="handleRemoveImage"
          @delete-page="deletePage"
          @duplicate-page="duplicatePage"
        />
      </aside>
    </div>

    <!-- Toast -->
    <Transition name="toast">
      <div v-if="toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-5 py-2.5 rounded-lg shadow-lg text-sm text-white"
           :class="toast.type === 'error' ? 'bg-red-600' : 'bg-gray-800'">
        {{ toast.message }}
      </div>
    </Transition>

    <!-- Delete Confirm -->
    <Transition name="fade">
      <div v-if="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showDeleteConfirm = false">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm mx-4">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Hapus Landing Page?</h3>
          <p class="text-sm text-gray-600 mb-6">Halaman <strong>{{ selected?.name }}</strong> akan dihapus permanen.</p>
          <div class="flex justify-end gap-3">
            <button @click="showDeleteConfirm = false" class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition">Batal</button>
            <button @click="confirmDelete" class="px-4 py-2 text-sm bg-red-600 text-white hover:bg-red-700 rounded-lg transition">Hapus</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, nextTick } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import Preview from '../components/Preview.vue'
import ToolPanel from '../components/ToolPanel.vue'

// Keep the local defaults above any function that uses them.
const DEFAULT_STYLE = {
  bg_type: 'solid',
  bg_color: '#ffffff',
  bg_gradient_from: '#667eea',
  bg_gradient_to: '#764ba2',
  font_family: 'sans',
  page_max_width: 'sm',
  title_align: 'center',
  title_size: '2xl',
  title_color: '#1f2937',
  title_bold: true,
  bio_align: 'center',
  bio_size: 'sm',
  bio_color: '#6b7280',
  bio_bold: false,
  avatar_size: 'md',
  avatar_rounded: 'full',
  use_avatar: true,
  use_cover: true,
}

function createCatalogItems() {
  return Array.from({ length: 6 }, (_, index) => ({
    label: `Produk ${index + 1}`,
    price: 'Rp 99.000',
    url: '',
    image_path: null,
  }))
}

function createDefaultElemStyle(type = 'link') {
  if (type === 'text') {
    return { align: 'left', size: 'sm', color: '#374151', bold: false }
  }
  if (type === 'image') {
    return { align: 'center', scale: 100, border: false, shadow: false, rounded: 'lg' }
  }
  if (type === 'link') {
    return { variant: 'solid', bg_color: '#111827', text_color: '#ffffff', rounded: 'lg', shadow: false }
  }
  if (type === 'catalog') {
    return {
      bg_color: '#f8fafc',
      text_color: '#111827',
      rounded: 'xl',
      shadow: false,
      catalog_layout: 4,
      catalog_main_card: true,
      catalog_show_price: true,
      catalog_items: createCatalogItems(),
    }
  }
  return {}
}

const inertiaPage = usePage()

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || ''
const api = axios.create({
  headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
})

// ── State ────────────────────────────────────────────────────
const pages = ref([...(inertiaPage.props.pages || [])])
const selected = ref(pages.value[0] || null)
const editData = ref(null)
const saving = ref(false)
const creating = ref(false)
const toast = ref(null)
const showDeleteConfirm = ref(false)
const originalJson = ref('')

// ── Computed ─────────────────────────────────────────────────
const isDirty = computed(() => {
  if (!editData.value || !originalJson.value) return false
  return JSON.stringify(serializeForSave(editData.value)) !== originalJson.value
})

// ── Init ─────────────────────────────────────────────────────
if (selected.value) loadEditData(selected.value)

function loadEditData(page) {
  const style = { ...DEFAULT_STYLE, ...(page.resolved_style || page.style || {}) }
  editData.value = reactive({
    name: page.name,
    slug: page.slug,
    title: page.title,
    bio: page.bio || '',
    avatar: page.avatar,
    cover_image: page.cover_image,
    status: page.status,
    meta_title: page.meta_title || '',
    meta_description: page.meta_description || '',
    style,
    links: (page.links || []).map(l => ({
      ...l,
      type: l.type === 'button' ? 'link' : (l.type || 'link'),
      elem_style: l.elem_style && Object.keys(l.elem_style).length
        ? { ...createDefaultElemStyle(l.type === 'catalog' ? 'catalog' : (l.type === 'button' ? 'link' : l.type || 'link')), ...l.elem_style, ...(l.type === 'image' ? { scale: Number(l.elem_style.scale) || 100 } : {}), ...(l.type === 'catalog' ? {
            catalog_layout: Number(l.elem_style.catalog_layout) || 4,
            catalog_main_card: l.elem_style.catalog_main_card !== false,
            catalog_show_price: l.elem_style.catalog_show_price !== false,
          } : {}) }
        : { ...createDefaultElemStyle(l.type === 'catalog' ? 'catalog' : (l.type === 'button' ? 'link' : l.type || 'link')) },
    })),
  })
  originalJson.value = JSON.stringify(serializeForSave(editData.value))
}

function serializeForSave(data) {
  return {
    name: data.name,
    slug: data.slug,
    title: data.title,
    bio: data.bio,
    status: data.status,
    meta_title: data.meta_title,
    meta_description: data.meta_description,
    style: data.style,
    links: data.links.map((l, i) => ({
      id: l.id || null,
      type: l.type === 'button' ? 'link' : (l.type || 'link'),
      label: l.label || '',
      url: l.url || '',
      icon: l.icon || null,
      thumbnail: l.thumbnail || null,
      content: l.content || null,
      image_path: l.image_path || null,
      elem_style: l.elem_style ? {
        ...l.elem_style,
        ...(l.type === 'image' ? { scale: Number(l.elem_style.scale) || 100 } : {}),
        ...(l.type === 'catalog' ? { catalog_layout: Number(l.elem_style.catalog_layout) || 4 } : {}),
      } : null,
      is_active: l.is_active !== false,
      opens_in_new_tab: l.opens_in_new_tab !== false,
    })),
  }
}

// ── Actions ──────────────────────────────────────────────────
function selectPage(page) {
  if (selected.value?.id === page.id) return
  selected.value = page
  loadEditData(page)
}

async function createPage() {
  creating.value = true
  try {
    const { data } = await api.post('/upanel/landing-pages')
    pages.value.unshift(data.page)
    selectPage(data.page)
    showToast('Halaman baru dibuat')
  } catch (e) {
    showToast('Gagal membuat halaman', 'error')
  } finally {
    creating.value = false
  }
}

function previewUrl() {
  if (!selected.value?.slug) return ''
  return `${window.location.origin}/upanel/landing-pages/${selected.value.id}/preview`
}

function publicUrl() {
  if (!selected.value?.slug) return ''
  return `${window.location.origin}/p/${selected.value.slug}`
}

function openPreview() {
  const url = previewUrl()
  if (!url) return
  window.open(url, '_blank')
}

async function copyPreviewUrl() {
  const url = publicUrl()
  if (!url) return
  try {
    await navigator.clipboard.writeText(url)
    showToast('Alamat disalin')
  } catch (e) {
    showToast('Gagal menyalin alamat', 'error')
  }
}

async function savePage() {
  if (!selected.value || !editData.value) return
  saving.value = true
  try {
    const payload = serializeForSave(editData.value)
    const { data } = await api.put(`/upanel/landing-pages/${selected.value.id}`, payload)
    const idx = pages.value.findIndex(p => p.id === selected.value.id)
    if (idx !== -1) pages.value[idx] = data.page
    selected.value = data.page
    loadEditData(data.page)
    showToast('Berhasil disimpan')
  } catch (e) {
    const msg = e.response?.data?.message || Object.values(e.response?.data?.errors || {}).flat()[0] || 'Gagal menyimpan'
    showToast(msg, 'error')
  } finally {
    saving.value = false
  }
}

function deletePage() {
  showDeleteConfirm.value = true
}

async function confirmDelete() {
  showDeleteConfirm.value = false
  try {
    await api.delete(`/upanel/landing-pages/${selected.value.id}`)
    pages.value = pages.value.filter(p => p.id !== selected.value.id)
    selected.value = pages.value[0] || null
    if (selected.value) loadEditData(selected.value)
    else editData.value = null
    showToast('Halaman dihapus')
  } catch (e) {
    showToast('Gagal menghapus', 'error')
  }
}

async function duplicatePage() {
  try {
    const { data } = await api.post(`/upanel/landing-pages/${selected.value.id}/duplicate`)
    pages.value.unshift(data.page)
    selectPage(data.page)
    showToast('Halaman berhasil diduplikasi')
  } catch (e) {
    showToast('Gagal menduplikasi', 'error')
  }
}

async function handleUploadImage({ type, file }) {
  const fd = new FormData()
  fd.append('image', file)
  fd.append('type', type)
  try {
    const { data } = await api.post(`/upanel/landing-pages/${selected.value.id}/upload`, fd)
    const field = type === 'avatar' ? 'avatar' : 'cover_image'
    editData.value[field] = data.path
    const idx = pages.value.findIndex(p => p.id === selected.value.id)
    if (idx !== -1) pages.value[idx][field] = data.path
    showToast('Gambar diupload')
  } catch (e) {
    showToast('Gagal upload gambar', 'error')
  }
}

async function handleRemoveImage(type) {
  try {
    await api.post(`/upanel/landing-pages/${selected.value.id}/remove-image`, { type })
    const field = type === 'avatar' ? 'avatar' : 'cover_image'
    editData.value[field] = null
    const idx = pages.value.findIndex(p => p.id === selected.value.id)
    if (idx !== -1) pages.value[idx][field] = null
    showToast('Gambar dihapus')
  } catch (e) {
    showToast('Gagal menghapus gambar', 'error')
  }
}

// ── Toast ────────────────────────────────────────────────────
let toastTimer = null
function showToast(message, type = 'success') {
  clearTimeout(toastTimer)
  toast.value = { message, type }
  toastTimer = setTimeout(() => { toast.value = null }, 2500)
}

</script>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all .3s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translate(-50%, 20px); }
.fade-enter-active, .fade-leave-active { transition: opacity .2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
