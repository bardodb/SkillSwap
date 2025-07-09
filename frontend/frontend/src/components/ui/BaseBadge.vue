<template>
  <span :class="badgeClasses">
    <slot />
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  variant?: 'primary' | 'secondary' | 'success' | 'warning' | 'danger'
  size?: 'sm' | 'md' | 'lg'
  rounded?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'primary',
  size: 'md',
  rounded: true
})

const badgeClasses = computed(() => {
  const baseClasses = ['badge']
  
  // Variant classes
  switch (props.variant) {
    case 'primary':
      baseClasses.push('badge-primary')
      break
    case 'secondary':
      baseClasses.push('bg-secondary-100', 'text-secondary-800')
      break
    case 'success':
      baseClasses.push('badge-success')
      break
    case 'warning':
      baseClasses.push('badge-warning')
      break
    case 'danger':
      baseClasses.push('badge-danger')
      break
  }
  
  // Size classes
  switch (props.size) {
    case 'sm':
      baseClasses.push('text-xs', 'px-2', 'py-0.5')
      break
    case 'lg':
      baseClasses.push('text-base', 'px-4', 'py-2')
      break
    default:
      baseClasses.push('text-sm', 'px-3', 'py-1')
  }
  
  // Rounded
  if (!props.rounded) {
    baseClasses.push('rounded-lg')
  }
  
  return baseClasses.join(' ')
})
</script> 