<template>
  <div class="border-t border-secondary-100 p-4 shrink-0">
    <p v-if="disabled && disabledReason" class="text-sm text-secondary-600 mb-2">
      {{ disabledReason }}
    </p>
    <form class="flex gap-2 items-end" @submit.prevent="submit">
      <div class="flex-1 min-w-0">
        <BaseInput
          v-model="draft"
          placeholder="Digite sua mensagem..."
          :disabled="disabled || sending"
          class="mb-0"
          @keydown.enter.exact.prevent="submit"
        />
      </div>
      <BaseButton
        type="submit"
        variant="primary"
        :disabled="disabled || sending || !draft.trim()"
        :loading="sending"
      >
        Enviar
      </BaseButton>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseButton from '@/components/ui/BaseButton.vue'

defineProps<{
  disabled: boolean
  disabledReason?: string
  sending: boolean
}>()

const emit = defineEmits<{
  send: [content: string]
}>()

const draft = ref('')

function submit() {
  const content = draft.value.trim()
  if (!content) return
  emit('send', content)
  draft.value = ''
}
</script>
