<template>
  <div :class="containerClasses">
    <div :class="spinnerClasses"></div>
    <p v-if="message" :class="messageClasses">{{ message }}</p>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  size?: 'sm' | 'md' | 'lg' | 'xl'
  message?: string
  fullscreen?: boolean
  overlay?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  fullscreen: false,
  overlay: false
})

const containerClasses = computed(() => {
  const baseClasses = ['flex', 'flex-col', 'items-center', 'justify-center']
  
  if (props.fullscreen) {
    baseClasses.push('fixed', 'inset-0', 'z-50')
  }
  
  if (props.overlay) {
    baseClasses.push('bg-white', 'bg-opacity-80', 'backdrop-blur-sm')
  }
  
  if (props.message) {
    baseClasses.push('space-y-4')
  }
  
  return baseClasses.join(' ')
})

const spinnerClasses = computed(() => {
  const baseClasses = ['loading-spinner']
  
  switch (props.size) {
    case 'sm':
      baseClasses.push('h-4', 'w-4', 'border-2')
      break
    case 'lg':
      baseClasses.push('h-12', 'w-12', 'border-4')
      break
    case 'xl':
      baseClasses.push('h-16', 'w-16', 'border-4')
      break
    default:
      baseClasses.push('h-8', 'w-8', 'border-2')
  }
  
  return baseClasses.join(' ')
})

const messageClasses = computed(() => {
  const baseClasses = ['text-secondary-600', 'text-center']
  
  switch (props.size) {
    case 'sm':
      baseClasses.push('text-sm')
      break
    case 'lg':
      baseClasses.push('text-lg')
      break
    case 'xl':
      baseClasses.push('text-xl')
      break
    default:
      baseClasses.push('text-base')
  }
  
  return baseClasses.join(' ')
})
</script> 