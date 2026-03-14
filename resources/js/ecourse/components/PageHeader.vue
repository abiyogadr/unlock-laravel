<template>
  <div class="mb-2">
    <h1 class="text-lg font-bold text-gray-800">{{ title }}</h1>
    <p v-if="subtitle" class="text-xs text-gray-600">{{ subtitle }}</p>
  </div>
</template>

<script setup>
const props = defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
})
</script>
