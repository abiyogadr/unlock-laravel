<template>
  <div class="min-h-full" :style="pageStyle">
    <div class="mx-auto px-4 py-6" :style="containerStyle">
      <div class="bg-white border border-white shadow-2xl rounded-[2rem] overflow-hidden">
        <div class="flex items-center justify-center px-5 py-3 border-b border-gray-100 bg-white/95 backdrop-blur">
          <div class="text-sm font-semibold text-gray-900">Unlock</div>
        </div>

        <div class="px-5 py-6">
          <div v-if="s.use_cover !== false && data.cover_image" class="w-full h-36 overflow-hidden rounded-3xl mb-5 shadow-sm">
            <img :src="storageUrl(data.cover_image)" class="w-full h-full object-cover" />
          </div>

          <div v-if="s.use_avatar !== false && data.avatar" class="flex justify-center mb-4">
            <div class="border-4 border-white shadow-lg overflow-hidden" :class="[avatarSizeCls, avatarRoundedCls]">
              <img :src="storageUrl(data.avatar)" class="w-full h-full object-cover" />
            </div>
          </div>

          <h1 v-if="data.title" :style="titleStyle" class="mb-2 leading-tight">{{ data.title }}</h1>
          <div v-if="data.bio" :style="bioStyle" class="mb-5 leading-relaxed">{{ data.bio }}</div>

          <div class="space-y-3">
            <template v-for="(el, i) in activeElements" :key="i">
              <div v-if="el.type === 'text'" class="w-full" :style="textBlockStyle(el)" v-html="el.content"></div>

              <div v-else-if="el.type === 'image' && el.image_path" class="w-full" :style="{ textAlign: getES(el, 'align', 'center') }">
                <img :src="storageUrl(el.image_path)" :style="imageStyle(el)" class="max-w-full inline-block" alt="" />
              </div>

              <div v-else-if="el.type === 'catalog'" class="w-full" :style="catalogShellStyle(el)">
                <div v-if="getES(el, 'catalog_main_card', true)" class="rounded-3xl overflow-hidden border border-white/60 shadow-lg p-4">
                  <div v-if="el.label" class="text-sm font-semibold leading-tight mb-4">{{ el.label }}</div>
                  <div :class="catalogGridClass(el)" class="grid gap-3">
                      <a v-for="(item, itemIdx) in catalogItems(el)" :key="itemIdx"
                        :href="normalizeUrl(item.url)"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="overflow-hidden rounded-2xl bg-white/90 text-gray-900 shadow-sm border border-black/5 block transition-all duration-200 hover:-translate-y-1 hover:shadow-xl hover:border-purple-200/50">
                      <div class="aspect-square bg-gray-50 overflow-hidden">
                        <img v-if="item.image_path" :src="storageUrl(item.image_path)" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                          <i class="fas fa-box text-2xl"></i>
                        </div>
                      </div>
                      <div class="p-3">
                        <div class="text-xs font-semibold leading-snug mb-1" :style="{ minHeight: '2.4em' }">{{ item.label || 'Produk' }}</div>
                        <div v-if="getES(el, 'catalog_show_price', true)" class="text-[11px] font-bold text-orange-600 mb-2">{{ item.price || 'Rp 0' }}</div>
                      </div>
                    </a>
                  </div>
                </div>

                <div v-else class="w-full space-y-3">
                  <div :class="catalogGridClass(el)" class="grid gap-3">
                      <a v-for="(item, itemIdx) in catalogItems(el)" :key="itemIdx"
                        :href="normalizeUrl(item.url)"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="overflow-hidden rounded-2xl bg-white/90 text-gray-900 shadow-sm border border-black/5 block transition-all duration-200 hover:-translate-y-1 hover:shadow-xl hover:border-purple-200/50">
                      <div class="aspect-square bg-gray-50 overflow-hidden">
                        <img v-if="item.image_path" :src="storageUrl(item.image_path)" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                          <i class="fas fa-box text-2xl"></i>
                        </div>
                      </div>
                      <div class="p-3">
                        <div class="text-xs font-semibold leading-snug mb-1" :style="{ minHeight: '2.4em' }">{{ item.label || 'Produk' }}</div>
                        <div v-if="getES(el, 'catalog_show_price', true)" class="text-[11px] font-bold text-orange-600 mb-2">{{ item.price || 'Rp 0' }}</div>
                      </div>
                    </a>
                  </div>
                </div>
              </div>

                <a v-else
                  :href="normalizeUrl(el.url)"
                  :target="isExternalUrl(el.url) ? '_blank' : null"
                  :rel="isExternalUrl(el.url) ? 'noopener noreferrer' : null"
                  @click="onLinkClick(el, $event)"
                 class="w-full flex items-center gap-3 transition-all hover:-translate-y-0.5"
                 :style="linkStyle(el)"
                 :class="shadowCls(el)">
                <template v-if="el.thumbnail">
                  <div class="w-8 h-8 rounded overflow-hidden shrink-0">
                    <img :src="storageUrl(el.thumbnail)" class="w-full h-full object-cover" />
                  </div>
                </template>
                <i v-else-if="el.icon" :class="el.icon" class="w-5 text-center shrink-0 text-sm"></i>
                <span class="flex-1 text-[13px] font-medium text-center">{{ el.label }}</span>
                <i class="fas fa-arrow-right text-[10px] opacity-40 shrink-0"></i>
              </a>
            </template>
          </div>

          <div v-if="!activeElements.length" class="w-full text-center py-10 text-gray-300 text-xs">
            Belum ada elemen
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({ data: Object })
const s = computed(() => props.data?.style || {})
const activeElements = computed(() => (props.data?.links || []).filter(l => l.is_active !== false))

const FONT_MAP = {
  sans: 'ui-sans-serif, system-ui, sans-serif',
  serif: 'ui-serif, Georgia, Cambria, serif',
  mono: 'ui-monospace, SFMono-Regular, monospace',
  rounded: 'ui-rounded, system-ui, sans-serif',
}
const WIDTH_MAP = { sm: '480px', md: '540px', lg: '720px' }
const SM = { xs:'0.75rem', sm:'0.875rem', base:'1rem', lg:'1.125rem', xl:'1.25rem', '2xl':'1.5rem', '3xl':'1.875rem' }
const ROUNDED_MAP = { none:'0', sm:'4px', md:'6px', lg:'8px', xl:'12px', full:'9999px' }

function storageUrl(path) {
  if (!path) return null
  if (path.startsWith('http') || path.startsWith('blob:')) return path
  return `/storage/${path}`
}

function normalizeUrl(url) {
  const value = String(url || '').trim()
  if (!value) return '#'
  if (/^(https?:|mailto:|tel:|#|\/)/i.test(value)) return value
  return `https://${value}`
}

function isExternalUrl(url) {
  const value = normalizeUrl(url)
  return /^https?:\/\//i.test(value)
}

function onLinkClick(el, event) {
  const url = normalizeUrl(el.url)
  event.preventDefault()
  if (url && isExternalUrl(url)) {
    window.open(url, '_blank', 'noopener,noreferrer')
  }
}

function hexToRgba(hex, alpha = 1) {
  const clean = String(hex || '').replace('#', '')
  if (clean.length !== 6) return hex || `rgba(0,0,0,${alpha})`
  const num = parseInt(clean, 16)
  const r = (num >> 16) & 255
  const g = (num >> 8) & 255
  const b = num & 255
  return `rgba(${r}, ${g}, ${b}, ${alpha})`
}

const pageStyle = computed(() => ({
  background: s.value.bg_type === 'gradient'
    ? `linear-gradient(135deg, ${s.value.bg_gradient_from ?? '#667eea'}, ${s.value.bg_gradient_to ?? '#764ba2'})`
    : (s.value.bg_color || '#ffffff'),
  fontFamily: FONT_MAP[s.value.font_family] || FONT_MAP.sans,
}))
const containerStyle = computed(() => ({ maxWidth: WIDTH_MAP[s.value.page_max_width] || WIDTH_MAP.sm }))
const titleStyle = computed(() => ({
  display: 'block', textAlign: s.value.title_align || 'center', fontSize: SM[s.value.title_size] || '1.5rem', color: s.value.title_color || '#1f2937', fontWeight: s.value.title_bold ? '700' : '400'
}))
const bioStyle = computed(() => ({
  display: 'block', textAlign: s.value.bio_align || 'center', fontSize: SM[s.value.bio_size] || '0.875rem', color: s.value.bio_color || '#6b7280', fontWeight: s.value.bio_bold ? '700' : '400'
}))
const avatarSizeCls = computed(() => ({ sm:'w-14 h-14', md:'w-20 h-20', lg:'w-28 h-28' }[s.value.avatar_size] || 'w-20 h-20'))
const avatarRoundedCls = computed(() => s.value.avatar_rounded === 'full' ? 'rounded-full' : 'rounded-xl')

function getES(el, key, fallback) {
  return (el.elem_style && el.elem_style[key] !== undefined) ? el.elem_style[key] : fallback
}
function imageStyle(el) {
  return {
    width: `${getES(el, 'scale', 100)}%`,
    borderRadius: ROUNDED_MAP[getES(el, 'rounded', 'lg')] || '8px',
    border: getES(el, 'border') ? '1px solid rgba(229, 231, 235, 1)' : 'none',
    boxShadow: getES(el, 'shadow') ? '0 6px 20px rgba(0,0,0,.12)' : 'none',
  }
}
function linkStyle(el) {
  const variant = getES(el, 'variant', 'solid')
  const bgColor = getES(el, 'bg_color', '#111827')
  const textColor = getES(el, 'text_color', '#ffffff')
  let backgroundColor = bgColor
  let color = textColor
  let border = 'none'

  if (variant === 'outline') {
    backgroundColor = 'transparent'
    color = bgColor
    border = `1px solid ${bgColor}`
  } else if (variant === 'soft') {
    backgroundColor = hexToRgba(bgColor, 0.12)
    color = bgColor
  } else if (variant === 'ghost') {
    backgroundColor = 'transparent'
    color = bgColor
  }

  return {
    backgroundColor,
    color,
    border,
    borderRadius: ROUNDED_MAP[getES(el, 'rounded', 'lg')] || '16px',
    padding: '12px 16px',
    display: 'flex',
    textDecoration: 'none',
  }
}
function catalogShellStyle(el) {
  const mainCard = getES(el, 'catalog_main_card', true)
  if (!mainCard) return {}
  const shadow = getES(el, 'shadow') ? '0 10px 28px rgba(0,0,0,.14)' : 'none'
  return {
    background: getES(el, 'bg_color', '#f8fafc'),
    color: getES(el, 'text_color', '#111827'),
    borderRadius: ROUNDED_MAP[getES(el, 'rounded', 'xl')] || '12px',
    boxShadow: shadow,
  }
}
function shadowCls(el) { return getES(el, 'shadow') ? 'shadow-md' : '' }
function catalogGridClass(el) {
  const count = Math.min(6, Math.max(1, parseInt(getES(el, 'catalog_layout', '4'), 10) || 4))
  return count === 1 ? 'grid grid-cols-1' : 'grid grid-cols-2'
}
function catalogItems(el) {
  const items = getES(el, 'catalog_items', []) || []
  const count = Math.min(6, Math.max(1, parseInt(getES(el, 'catalog_layout', '4'), 10) || 4))
  return items.slice(0, count)
}
function textBlockStyle(el) {
  return {
    textAlign: getES(el, 'align', 'left'),
    fontSize: SM[getES(el, 'size', 'sm')] || '0.875rem',
    color: getES(el, 'color', '#374151'),
    fontWeight: getES(el, 'bold') ? '700' : '400',
    lineHeight: '1.6',
    whiteSpace: 'normal',
  }
}
</script>

<style scoped>
.mini-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 12px;
  border-radius: 9999px;
  background: rgba(255,255,255,0.18);
  color: inherit;
  font-size: 12px;
  font-weight: 600;
  text-decoration: none;
}
</style>
