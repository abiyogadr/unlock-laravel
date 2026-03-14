<template>
  <div class="min-h-screen bg-white text-slate-900">
    <div class="w-full mx-auto">
      <section class="px-4 lg:px-8 xl:px-16 py-2">
        <!-- Header -->
        <PageHeader
          title="My Journey"
          subtitle="Unlock semua e-course yang telah kamu jelajahi"
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
                  {{ category.name }} ({{ category.courses_count || 0 }})
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Results View -->
        <div v-if="hasFilters" class="mb-2 flex justify-between items-center">
          <h2 class="text-xl font-bold text-gray-800">
            Hasil Pencarian <span class="text-primary">({{ courses?.length || 0 }})</span>
          </h2>
          <button @click="resetFilters" class="text-sm text-red-500 hover:text-red-700 font-medium">
            <i class="fas fa-times-circle mr-1"></i> Bersihkan Filter
          </button>
        </div>
      </section>

      <section class="px-4 lg:pl-8 xl:pl-16 py-2">
        <!-- Courses Grid -->
        <div v-if="courses && courses.length > 0" class="flex flex-wrap gap-4 mb-8">
          <CourseCard 
            v-for="userCourse in courses" 
            :key="userCourse.id" 
            :course="userCourse.course" 
            :progress="userCourse.progress"
            :show-price="false"
            :show-owned="false"
            :class="'w-[47%] sm:w-[31.5%] md:w-[23.25%] lg:w-[23%] xl:w-48'"
          />
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-3xl p-12 text-center border border-dashed border-gray-200">
          <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          </div>
          <h3 class="text-lg font-bold text-gray-800 mb-2">Belum Ada Kursus</h3>
          <p class="text-gray-500 mb-6">Kamu belum memiliki e-course. Yuk mulai belajar!</p>
          <Link href="/ecourse/catalog" class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-xl font-bold text-sm hover:bg-purple-800 transition">
            <i class="fas fa-book mr-2"></i> Jelajahi Katalog
          </Link>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
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

function debounceSearch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    applyFilters()
  }, 700)
}

function applyFilters() {
  const category = props.filters?.category || ''
  router.get('/ecourse/my-journey', { search: form.value.search, category: category }, {
    preserveState: true,
    preserveScroll: true,
  })
}

function applyCategoryFilter(categorySlug) {
  const currentCategory = props.filters?.category || ''
  const newCategory = currentCategory === categorySlug ? '' : categorySlug
  
  router.get('/ecourse/my-journey', { search: form.value.search, category: newCategory }, {
    preserveState: true,
    preserveScroll: true,
  })
}

function resetFilters() {
  form.value.search = ''
  router.get('/ecourse/my-journey')
}
</script>
