<template>
  <div :class="cardClasses">
    <div v-if="$slots.header" class="card-header">
      <slot name="header" />
    </div>
    
    <div class="card-body">
      <slot />
    </div>
    
    <div v-if="$slots.footer" class="card-footer">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  hover?: boolean
  noPadding?: boolean
  shadow?: 'soft' | 'medium' | 'large'
}

const props = withDefaults(defineProps<Props>(), {
  hover: false,
  noPadding: false,
  shadow: 'soft'
})

const cardClasses = computed(() => {
  const baseClasses = ['card']
  
  if (props.hover) {
    baseClasses.push('card-hover')
  }
  
  if (props.noPadding) {
    baseClasses.push('p-0')
  }
  
  switch (props.shadow) {
    case 'medium':
      baseClasses.push('shadow-medium')
      break
    case 'large':
      baseClasses.push('shadow-large')
      break
    default:
      baseClasses.push('shadow-soft')
  }
  
  return baseClasses.join(' ')
})
</script> 