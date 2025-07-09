<template>
  <div class="space-y-2">
    <label v-if="label" :for="inputId" class="form-label">
      {{ label }}
      <span v-if="required" class="text-danger-500 ml-1">*</span>
    </label>
    
    <div class="relative">
      <div v-if="$slots['icon-left']" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <slot name="icon-left" />
      </div>
      
      <input
        :id="inputId"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :readonly="readonly"
        :class="inputClasses"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
        v-bind="$attrs"
      />
      
      <div v-if="$slots['icon-right']" class="absolute inset-y-0 right-0 pr-3 flex items-center">
        <slot name="icon-right" />
      </div>
    </div>
    
    <div v-if="error" class="form-error">
      {{ error }}
    </div>
    
    <div v-if="hint && !error" class="text-secondary-500 text-sm">
      {{ hint }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, useSlots } from 'vue'

interface Props {
  modelValue?: string | number
  type?: string
  label?: string
  placeholder?: string
  required?: boolean
  disabled?: boolean
  readonly?: boolean
  error?: string
  hint?: string
  size?: 'sm' | 'md' | 'lg'
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  size: 'md'
})

const emit = defineEmits<{
  'update:modelValue': [value: string | number]
  'blur': [event: FocusEvent]
  'focus': [event: FocusEvent]
}>()

const inputId = ref(`input-${Math.random().toString(36).substr(2, 9)}`)
const focused = ref(false)
const slots = useSlots()

const inputClasses = computed(() => {
  const baseClasses = ['form-input']
  
  if (slots['icon-left']) {
    baseClasses.push('pl-10')
  }
  
  if (slots['icon-right']) {
    baseClasses.push('pr-10')
  }
  
  if (props.error) {
    baseClasses.push('border-danger-300', 'focus:border-danger-500', 'focus:ring-danger-500')
  }
  
  if (props.disabled) {
    baseClasses.push('bg-secondary-50', 'cursor-not-allowed')
  }
  
  switch (props.size) {
    case 'sm':
      baseClasses.push('py-2', 'text-sm')
      break
    case 'lg':
      baseClasses.push('py-4', 'text-lg')
      break
    default:
      baseClasses.push('py-3')
  }
  
  return baseClasses.join(' ')
})

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  emit('update:modelValue', target.value)
}

const handleBlur = (event: FocusEvent) => {
  focused.value = false
  emit('blur', event)
}

const handleFocus = (event: FocusEvent) => {
  focused.value = true
  emit('focus', event)
}
</script> 