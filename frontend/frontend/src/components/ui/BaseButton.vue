<template>
  <component
    :is="tag"
    :class="buttonClasses"
    :disabled="disabled || loading"
    v-bind="$attrs"
  >
    <div v-if="loading" class="loading-spinner mr-2"></div>
    <slot v-if="!loading" name="icon-left" />
    <span v-if="$slots.default">
      <slot />
    </span>
    <slot v-if="!loading" name="icon-right" />
  </component>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  variant?: 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger' | 'success'
  size?: 'sm' | 'md' | 'lg' | 'xl'
  disabled?: boolean
  loading?: boolean
  fullWidth?: boolean
  tag?: string
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'primary',
  size: 'md',
  disabled: false,
  loading: false,
  fullWidth: false,
  tag: 'button'
})

const buttonClasses = computed(() => {
  const baseClasses = ['btn']
  
  // Variant classes
  switch (props.variant) {
    case 'primary':
      baseClasses.push('btn-primary')
      break
    case 'secondary':
      baseClasses.push('btn-secondary')
      break
    case 'outline':
      baseClasses.push('btn-outline')
      break
    case 'ghost':
      baseClasses.push('btn-ghost')
      break
    case 'danger':
      baseClasses.push('btn-danger')
      break
    case 'success':
      baseClasses.push('btn-success')
      break
  }
  
  // Size classes
  switch (props.size) {
    case 'sm':
      baseClasses.push('btn-sm')
      break
    case 'lg':
      baseClasses.push('btn-lg')
      break
    case 'xl':
      baseClasses.push('btn-xl')
      break
  }
  
  // Full width
  if (props.fullWidth) {
    baseClasses.push('w-full')
  }
  
  // Disabled state
  if (props.disabled || props.loading) {
    baseClasses.push('opacity-50', 'cursor-not-allowed')
  }
  
  return baseClasses.join(' ')
})
</script> 