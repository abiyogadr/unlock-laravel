<template>
  <div class="mx-auto px-4 sm:px-6 lg:px-8 py-2">
      <!-- Header -->
      <PageHeader
        title="Catalog E-Course"
        subtitle="Pilih e-course yang sesuai dengan kebutuhan profesionalmu"
      />

      <!-- Combined Search and Category (desktop: search left, chips right) -->
      <div class="py-2 mb-2">
        <div class="flex flex-col md:flex-row gap-4 items-stretch">
          <!-- Search Input (left) -->
          <div class="md:w-80">
            <div class="relative">
              <input 
                v-model="form.search" 
                type="text" 
                placeholder="Cari"
                @input="debounceSearch"
                class="w-full px-4 py-2 pr-10 border border-gray-200 rounded-lg focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm bg-white"
              />
              <i class="fas fa-search absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
          </div>

          <!-- Category Chips (right, scrollable) -->
          <div class="flex-1 min-w-0">
            <div class="flex space-x-2 overflow-x-auto pb-1 custom-scrollbar">
              <button 
                v-for="category in categories" 
                :key="category.id" 
                @click="applyCategoryFilter(category.slug)"
                :class="{'bg-primary text-white font-bold shadow-lg scale-105': (props.filters?.category || '') === category.slug, 'bg-white text-gray-700 hover:bg-gray-50': (props.filters?.category || '') !== category.slug}"
                class="flex-shrink-0 px-4 py-2 rounded-full cursor-pointer font-semibold text-xs transition-all duration-200 ease-in-out shadow-sm border border-gray-100"
              >
                {{ category.name }} ({{ category.courses?.length || 0 }})
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Results View -->
      <div v-if="hasFilters" class="mb-4 flex justify-between items-center">
        <h2 class="text-sm font-bold text-gray-800">
          Hasil Pencarian <span class="text-primary">({{ courses?.length || 0 }})</span>
        </h2>
        
        <button 
          @click="resetFilters" 
          class="flex items-center text-sm text-red-500 hover:text-red-700 transition"
        >
          <i class="fas fa-times-circle mr-1"></i> Bersihkan Filter
        </button>
      </div>

      <!-- Courses Grid (search results) -->
      <div v-if="hasFilters && courses && courses.length > 0" class="flex flex-wrap gap-6 mb-8 justify-start">
        <CourseCard
          v-for="course in courses"
          :key="course.id"
          :course="course"
        />
      </div>

      <!-- Empty State for search -->
      <div v-else-if="hasFilters && (!courses || courses.length === 0)" class="bg-white rounded-3xl p-12 text-center border border-dashed border-gray-200">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-search text-3xl text-gray-300"></i>
        </div>
        <h3 class="text-sm font-bold text-gray-800 mb-2">Tidak ada hasil</h3>
        <p class="text-sm text-gray-500 mb-6">Coba ubah filter pencarian Anda</p>
        <button @click="resetFilters" class="px-6 py-3 bg-primary text-white rounded-xl font-bold text-sm hover:bg-purple-800 transition cursor-pointer">
          Reset Filter
        </button>
      </div>

      <!-- Horizontal Scrollers By Category (else branch) -->
      <div v-else class="space-y-6 mb-8">
        <div>
          <section v-for="category in categories" :key="category.id" class="mb-4 last:mb-0 max-w-full overflow-hidden">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-sm font-bold text-gray-800">{{ category.name }}</h2>
            </div>
            <div class="relative w-full max-w-full overflow-hidden">
              <button
                @click="scrollLeft(category.id)"
                class="absolute left-0 top-1/2 transform -translate-y-1/2 z-10 bg-white bg-opacity-80 hover:bg-opacity-100 rounded-full p-2 shadow-md transition-opacity duration-200"
                :class="{ 'opacity-0 pointer-events-none': !(scrollStates[category.id]?.canScrollLeft), 'opacity-100': scrollStates[category.id]?.canScrollLeft }"
              >
                <i class="fas fa-chevron-left text-gray-600"></i>
              </button>
              <div
                :ref="el => setScrollerRef(category.id, el)"
                class="overflow-x-auto scrollbar-hide pb-4"
                @scroll="updateScrollButtons(category.id)"
              >
                <div class="flex gap-4 w-max">
                  <div v-for="course in category.courses" :key="course.id">
                    <CourseCard
                      :course="course"
                      :show-level="false"
                    />
                  </div>
                </div>
              </div>
              <button
                @click="scrollRight(category.id)"
                class="absolute right-0 top-1/2 transform -translate-y-1/2 z-10 bg-white bg-opacity-80 hover:bg-opacity-100 rounded-full p-2 shadow-md transition-opacity duration-200"
                :class="{ 'opacity-0 pointer-events-none': !(scrollStates[category.id]?.canScrollRight), 'opacity-100': scrollStates[category.id]?.canScrollRight }"
              >
                <i class="fas fa-chevron-right text-gray-600"></i>
              </button>
            </div>
          </section>
        </div>
      </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import CourseCard from '../components/CourseCard.vue'
import PageHeader from '../components/PageHeader.vue'

const props = defineProps({
  courses: Object,
  categories: Array,
  filters: Object,
})

const form = ref({
  search: props.filters?.search || '',
})

const hasFilters = computed(() => {
  return form.value.search || (props.filters?.category || '')
})

let debounceTimer = null
const scrollers = ref({})
const scrollStates = ref({})

function updateAllScrollButtons() {
  props.categories?.forEach(category => {
    updateScrollButtons(category.id)
  })
}

onMounted(async () => {
  await nextTick()
  updateAllScrollButtons()
})

function setScrollerRef(id, el) {
  if (el) {
    if (scrollers.value[id] !== el) {
      scrollers.value[id] = el
      updateScrollButtons(id)
    }
  }
}

function updateScrollButtons(id) {
  const el = scrollers.value[id]
  if (el) {
    scrollStates.value[id] = {
      canScrollLeft: el.scrollLeft > 0,
      canScrollRight: el.scrollLeft < el.scrollWidth - el.clientWidth - 1
    }
  }
}

function scrollLeft(id) {
  const el = scrollers.value[id]
  if (el) {
    el.scrollBy({ left: -300, behavior: 'smooth' })
  }
}

function scrollRight(id) {
  const el = scrollers.value[id]
  if (el) {
    el.scrollBy({ left: 300, behavior: 'smooth' })
  }
}

function debounceSearch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    applyFilters()
  }, 700)
}

function applyFilters() {
  const category = props.filters?.category || ''
  router.get('/ecourse/catalog', { search: form.value.search, category: category }, {
    preserveState: true,
    preserveScroll: true,
  })
}

function applyCategoryFilter(categorySlug) {
  const currentCategory = props.filters?.category || ''
  const newCategory = currentCategory === categorySlug ? '' : categorySlug
  
  router.get('/ecourse/catalog', { search: form.value.search, category: newCategory }, {
    preserveState: true,
    preserveScroll: true,
  })
}

function resetFilters() {
  form.value.search = ''
  router.get('/ecourse/catalog')
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
