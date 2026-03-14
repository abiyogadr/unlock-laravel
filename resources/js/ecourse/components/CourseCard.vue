<template>
  <Link :href="`/ecourse/course/${safeCourse.slug}`" class="group block flex-shrink-0 w-42 md:w-48">
    <div class="bg-white rounded-2xl overflow-hidden shadow-lg border-4 border-transparent hover:border-primary/60 hover:shadow-xl transition-all duration-300 flex flex-col">
      <!-- Image -->
      <div class="relative aspect-video bg-gradient-to-br from-gray-200/80 to-gray-100/80 flex items-center justify-center overflow-hidden">
        <img v-if="safeCourse.thumbnail_url" :src="safeCourse.thumbnail_url" :alt="safeCourse.title" class="w-full h-full object-cover">
        <i v-else class="fas fa-book text-white text-5xl opacity-30"></i>
        
        <!-- Owned Badge -->
        <div v-if="safeCourse.is_owned && showPrice" class="absolute top-3 left-3 bg-primary/90 text-white px-3 py-1 rounded-full text-2xs font-bold">
          <i class="fas fa-check mr-1"></i> Terdaftar
        </div>
      </div>

      <!-- Content -->
      <div class="px-2 md:px-4 py-2 flex flex-col flex-1 min-h-0">
        <!-- Category Badge -->
        <div v-if="safeCourse.categories && safeCourse.categories.length > 0" class="mb-1">
          <span class="inline-block px-2 py-1 bg-primary/10 text-primary text-3xs font-semibold rounded-full">
            {{ safeCourse.categories[0].name }}
          </span>
        </div>

        <!-- Title -->
        <h3 class="font-semibold text-gray-800 mb-1 line-clamp-2 group-hover:text-primary transition text-2xs md:text-xs min-h-[2rem]">
          {{ safeCourse.title }}
        </h3>

        <!-- Speaker -->
        <p v-if="safeCourse.speaker" class="text-2xs md:text-xs text-gray-600 mb-1 flex items-center">
          <i class="fas fa-user-tie mr-2 text-primary"></i>
          {{ safeCourse.speaker.name }}
        </p>

        <!-- Description -->
        <p class="text-2xs md:text-xs text-gray-600 mb-1 line-clamp-2">{{ safeCourse.short_description }}</p>

        <!-- Footer -->
        <div v-if="showPrice" class="flex items-center justify-between mt-1 pt-2 border-t border-gray-100">
          <!-- Price -->
          <div>
            <p v-if="safeCourse.is_free || safeCourse.price === 0" class="font-bold text-green-600 text-lg">
              Gratis
            </p>
            <div v-else class="space-y-1">
              <p class="font-bold text-primary text-sm md:text-lg">
                Rp {{ formatPrice(safeCourse.price) }}
              </p>
            </div>
          </div>
          <!-- Level Badge -->
          <!-- <span v-if="safeCourse.credit_cost > 0" class="text-2xs text-yellow-600 flex items-center">
            <i class="fas fa-coins mr-1"></i> {{ safeCourse.credit_cost }} USTAR
          </span> -->
          
          <!-- Level Badge -->
          <!-- <span v-if="showLevel" :class="['text-2xs font-semibold px-3 py-1 rounded-full', levelClass]">
            {{ levelLabel }}
          </span> -->
        </div>

        <!-- Progress Bar (if provided) -->
        <div v-if="progress !== null && progress !== undefined" class="mt-4">
          <div class="bg-gray-200 rounded-full h-2 overflow-hidden">
            <div class="bg-green-500 h-full rounded-full transition-all" :style="{ width: progress + '%' }"></div>
          </div>
          <p class="text-xs text-gray-600 mt-2">{{ Math.round(Number(progress)) }}% Selesai</p>
        </div>
      </div>
    </div>
  </Link>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  course: {
    type: Object,
    required: true,
  },
  progress: {
    type: [Number, String],
    default: null
  },
  showPrice: {
    type: Boolean,
    default: true
  },
  showLevel: {
    type: Boolean,
    default: true
  },
  showOwned: {
    type: Boolean,
    default: true
  }
})

// Provide safe defaults if course data is missing
const safeCourse = computed(() => ({
  id: props.course?.id,
  title: props.course?.title || 'Untitled Course',
  slug: props.course?.slug || '',
  short_description: props.course?.short_description || '',
  thumbnail_url: props.course?.thumbnail_url || null,
  level: props.course?.level || 'beginner',
  price: props.course?.price !== undefined ? props.course.price : 0,
  credit_cost: props.course?.credit_cost ?? 0,
  is_free: props.course?.is_free !== undefined ? props.course.is_free : (props.course?.price === 0),
  is_owned: props.course?.is_owned || false,
  categories: Array.isArray(props.course?.categories) ? props.course.categories : [],
  speaker: props.course?.speaker || null,
}))

const levelLabel = computed(() => {
  const level = safeCourse.value?.level?.toLowerCase() || 'beginner'
  switch (level) {
    case 'advanced':
      return 'Advanced'
    case 'intermediate':
      return 'Intermediate'
    case 'beginner':
    default:
      return 'Beginner'
  }
})

const levelClass = computed(() => {
  const level = safeCourse.value?.level?.toLowerCase() || 'beginner'
  switch (level) {
    case 'advanced':
      return 'bg-red-100 text-red-700'
    case 'intermediate':
      return 'bg-yellow-100 text-yellow-700'
    case 'beginner':
    default:
      return 'bg-green-100 text-green-700'
  }
})

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID').format(price)
}
</script>
