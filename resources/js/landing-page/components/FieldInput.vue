<template>
  <div>
    <label v-if="label" class="block text-sm font-medium text-gray-700 mb-1">{{ label }}</label>
    <input
      :type="type"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      @blur="$emit('blur', $event)"
      :placeholder="placeholder"
      :disabled="disabled"
      class="w-full rounded-lg border px-4 py-2.5 text-sm transition"
      :class="error ? 'border-red-300 focus:ring-red-200 focus:border-red-400' : 'border-gray-200 focus:ring-2 focus:ring-primary/20 focus:border-primary'"
    />
    <p v-if="error" class="text-xs text-red-500 mt-1">{{ error }}</p>
  </div>
</template>

<script setup>
defineProps({
  label: String,
  modelValue: [String, Number],
  type: { type: String, default: 'text' },
  placeholder: String,
  error: String,
  disabled: Boolean,
})

defineEmits(['update:modelValue', 'blur'])
</script>
