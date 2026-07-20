<template>
  <div class="flex flex-col h-full min-h-0" data-testid="conversation-list">
    <div class="px-4 py-3 border-b border-secondary-100">
      <h2 class="text-sm font-semibold text-secondary-900">Conversas</h2>
    </div>

    <div v-if="loading" class="flex-1 flex items-center justify-center p-6">
      <BaseLoading size="md" message="Carregando conversas..." />
    </div>

    <div
      v-else-if="!conversations.length"
      class="flex-1 flex flex-col items-center justify-center p-6 text-center text-secondary-600"
    >
      <p class="font-medium text-secondary-800">Nenhuma conversa ainda</p>
      <p class="text-sm mt-1">
        Inicie um chat a partir de um perfil ou de uma troca ativa (pendente, aceita ou agendada).
      </p>
    </div>

    <ul v-else class="flex-1 overflow-y-auto divide-y divide-secondary-100">
      <li v-for="item in conversations" :key="item.partner.id">
        <button
          type="button"
          class="w-full flex items-center gap-3 px-4 py-3 text-left transition-colors hover:bg-secondary-50"
          :class="activePartnerId === item.partner.id ? 'bg-primary-50' : ''"
          :data-testid="`conversation-item-${item.partner.id}`"
          @click="emit('select', item.partner.id)"
        >
          <div
            class="shrink-0 w-10 h-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-semibold"
            aria-hidden="true"
          >
            {{ initials(item.partner.name) }}
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-2">
              <span class="font-medium text-secondary-900 truncate">{{ item.partner.name }}</span>
              <BaseBadge v-if="item.unread_count > 0" variant="primary" size="sm">
                {{ item.unread_count }}
              </BaseBadge>
            </div>
            <p v-if="item.last_message" class="text-sm text-secondary-600 truncate mt-0.5">
              {{ item.last_message.content }}
            </p>
            <p v-else class="text-sm text-secondary-400 italic mt-0.5">Sem mensagens</p>
          </div>
        </button>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import BaseBadge from '@/components/ui/BaseBadge.vue'
import BaseLoading from '@/components/ui/BaseLoading.vue'

export interface ConversationPartner {
  id: number
  name: string
  avatar?: string | null
  email?: string
}

export interface ConversationListItem {
  partner: ConversationPartner
  last_message: {
    id: number
    content: string
    sender_id: number
    created_at: string
    is_read: boolean
  } | null
  unread_count: number
  can_message: boolean
}

defineProps<{
  conversations: ConversationListItem[]
  activePartnerId: number | null
  loading: boolean
}>()

const emit = defineEmits<{
  select: [partnerId: number]
}>()

function initials(name: string): string {
  return name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase()
}
</script>
