<template>
  <component :is="layout">
    <component :is="component" v-bind="props" />
  </component>
</template>

<script setup>
import EcourseLayout from './layouts/EcourseLayout.vue'

defineProps({
  component: String,
  layout: String,
  props: Object,
})

// Set default layout
const layout = EcourseLayout
</script>

<style>
/* Global styles for App */
</style>
