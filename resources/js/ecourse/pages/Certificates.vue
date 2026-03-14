<template>
  <div class="min-h-screen bg-white text-slate-900">
    <div class="w-full mx-auto">
      <section class="px-4 lg:pl-8 xl:pl-16 py-4">

      <!-- Certificates List -->
      <div v-if="certificates.data && certificates.data.length > 0" class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="divide-y divide-gray-200">
          <div class="px-6 py-4 flex items-center justify-between bg-gray-50">
            <div class="flex items-center gap-4">
              <h3 class="text-md font-semibold text-gray-800">Sertifikat : {{ certificates.total }}</h3>
              <!-- <span class="text-sm text-gray-800">{{ certificates.total }} total</span> -->
            </div>
          </div>

          <ul>
            <li v-for="cert in certificates.data" :key="cert.id" class="px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between">
              <div class="flex items-start md:items-center gap-3 flex-1 min-w-0">
                <div v-if="cert.thumbnail_url" class="w-24 aspect-video rounded-md overflow-hidden bg-gray-100">
                  <img :src="cert.thumbnail_url" alt="Thumbnail" class="w-full h-full object-cover">
                </div>
                <div v-else class="w-24 aspect-video bg-yellow-400 rounded-md flex items-center justify-center text-white">
                  <i class="fas fa-certificate text-lg"></i>
                </div>
                <div class="truncate flex-1">
                  <div class="text-sm font-semibold text-gray-800 truncate">{{ cert.course_title }}</div>
                  <div class="text-xs text-gray-500">Issued at: <span class="font-medium text-gray-700">{{ formatDate(cert.issued_at) }}</span></div>
                  <div class="text-xs text-gray-500">No: <span class="font-semibold text-gray-800">{{ cert.certificate_number }}</span></div>
                </div>
              </div>

              <div class="flex items-center gap-3 mt-3 sm:ml-6 md:mt-0">
                <button @click="openPreview(cert.certificate_number)" class="px-3 py-1 bg-primary text-white rounded-md text-sm hover:bg-primary/90 cursor-pointer">Preview</button>
                <a :href="`/certificate/${cert.certificate_number}`" target="_blank" class="px-3 py-1 bg-primary text-white rounded-md text-sm hover:bg-primary/90 cursor-pointer">Buka</a>
              </div>
            </li>
          </ul>
        </div>
      </div>

      <!-- Preview Modal -->
      <div v-if="showPreview" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-3">
        <div class="bg-white w-full max-w-4xl h-[80vh] rounded-lg overflow-hidden shadow-lg">
          <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200">
            <div class="font-semibold">Preview Sertifikat</div>
            <button @click="closePreview" class="px-3 py-1 rounded bg-gray-100 cursor-pointer">Tutup</button>
          </div>
          <iframe :src="previewUrl" class="w-full h-[calc(100%-48px)]" frameborder="0"></iframe>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!certificates.data || certificates.data.length === 0" class="bg-white rounded-3xl p-12 text-center border border-dashed border-gray-200">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-award text-3xl text-gray-300"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-800 mb-2">Belum Ada Sertifikat</h3>
        <p class="text-gray-500 mb-6">Selesaikan kursus untuk mendapatkan sertifikat!</p>
        <Link href="/ecourse/catalog" class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-xl font-bold text-sm hover:bg-purple-800 transition">
          <i class="fas fa-book mr-2"></i> Jelajahi Katalog
        </Link>
      </div>

      <!-- Pagination -->
      <div v-if="certificates.data && certificates.data.length > 0 && (certificates.prev_page_url || certificates.next_page_url)" class="mt-8 flex justify-center gap-2">
        <Link 
          v-if="certificates.prev_page_url"
          :href="certificates.prev_page_url"
          class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
        >
          <i class="fas fa-chevron-left"></i>
        </Link>
        <span class="px-4 py-2 bg-primary text-white rounded-lg">
          {{ certificates.current_page }} / {{ certificates.last_page }}
        </span>
        <Link 
          v-if="certificates.next_page_url"
          :href="certificates.next_page_url"
          class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
        >
          <i class="fas fa-chevron-right"></i>
        </Link>
      </div>
    </section>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  certificates: Object,
})

const showPreview = ref(false)
const previewUrl = ref('')

function formatDate(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', { 
    day: 'numeric', 
    month: 'long', 
    year: 'numeric' 
  })
}

function openPreview(id) {
  // load embed mode so iframe contains only certificate content (no header/sidebar)
  previewUrl.value = `/certificate/${id}?embed=1`
  showPreview.value = true
}

function closePreview() {
  showPreview.value = false
  previewUrl.value = ''
}
</script>
