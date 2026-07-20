<template>
  <div class="flex flex-col h-full min-h-0">
    <div
      v-if="partner"
      class="px-4 py-3 border-b border-secondary-100 flex items-center gap-3 shrink-0"
    >
      <div
        class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-semibold"
        aria-hidden="true"
      >
        {{ initials(partner.name) }}
      </div>
      <div class="min-w-0">
        <h2 class="font-semibold text-secondary-900 truncate">{{ partner.name }}</h2>
        <p v-if="partner.email" class="text-xs text-secondary-500 truncate">{{ partner.email }}</p>
      </div>
    </div>

    <div v-if="loading" class="flex-1 flex items-center justify-center p-6">
      <BaseLoading size="md" message="Carregando mensagens..." />
    </div>

    <div
      v-else-if="!partner"
      class="flex-1 flex flex-col items-center justify-center p-6 text-center text-secondary-600"
    >
      <p class="font-medium text-secondary-800">Selecione uma conversa</p>
      <p class="text-sm mt-1">Escolha alguém na lista para ver o histórico.</p>
    </div>

    <div
      v-else-if="!messages.length"
      class="flex-1 flex flex-col items-center justify-center p-6 text-center text-secondary-600"
    >
      <p class="font-medium text-secondary-800">Nenhuma mensagem</p>
      <p class="text-sm mt-1">Envie a primeira mensagem abaixo.</p>
    </div>

    <div
      v-else
      ref="scrollContainer"
      class="flex-1 overflow-y-auto p-4 flex flex-col gap-3 min-h-0"
    >
      <div
        v-for="msg in messages"
        :key="msg.id"
        class="max-w-[85%] rounded-2xl px-4 py-2 text-sm break-words"
        :class="
          msg.sender_id === currentUserId
            ? 'ml-auto bg-primary-600 text-white'
            : 'mr-auto bg-secondary-100 text-secondary-900'
        "
      >
        <p>{{ msg.content }}</p>
        <p
          class="text-xs mt-1 opacity-80"
          :class="msg.sender_id === currentUserId ? 'text-primary-100' : 'text-secondary-500'"
        >
          {{ formatTime(msg.created_at) }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { nextTick, ref, watch } from 'vue'
import BaseLoading from '@/components/ui/BaseLoading.vue'

export interface ThreadPartner {
  id: number
  name: string
  avatar?: string | null
  email?: string
}

export interface ThreadMessage {
  id: number
  content: string
  sender_id: number
  receiver_id?: number
  created_at: string
}

const props = defineProps<{
  messages: ThreadMessage[]
  currentUserId: number
  partner: ThreadPartner | null
  loading: boolean
}>()

const scrollContainer = ref<HTMLElement | null>(null)

function initials(name: string): string {
  return name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase()
}

function formatTime(value: string): string {
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleString('pt-BR')
}

async function scrollToBottom() {
  await nextTick()
  const el = scrollContainer.value
  if (el) {
    el.scrollTop = el.scrollHeight
  }
}

watch(
  () => [props.messages.length, props.loading] as const,
  () => {
    if (!props.loading && props.messages.length) {
      scrollToBottom()
    }
  },
  { flush: 'post' }
)
</script>
