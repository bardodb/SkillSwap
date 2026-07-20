<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-secondary-900">Mensagens</h1>
        <p class="text-sm text-secondary-600">Converse com parceiros de troca em tempo real</p>
      </div>

      <div
        v-if="listError"
        class="mb-4 bg-danger-50 border border-danger-200 rounded-xl p-4 text-danger-800 text-sm"
      >
        {{ listError }}
      </div>

      <BaseCard no-padding class="min-h-[70vh] flex flex-col md:flex-row overflow-hidden">
        <div class="w-full md:w-80 border-r border-secondary-100 flex flex-col min-h-[40vh] md:min-h-0">
          <ConversationList
            :conversations="conversations"
            :active-partner-id="activePartnerId"
            :loading="listLoading"
            @select="selectPartner"
          />
        </div>

        <div class="flex-1 flex flex-col min-w-0 min-h-[50vh] md:min-h-0">
          <div
            v-if="echoUnavailable"
            class="bg-amber-50 border-b border-amber-200 px-4 py-3 text-sm text-amber-900"
            role="alert"
          >
            Conexão em tempo real indisponível. As mensagens podem não atualizar automaticamente até a
            conexão voltar.
          </div>
          <div
            v-if="subscriptionPermissionError"
            class="bg-danger-50 border-b border-danger-200 px-4 py-3 text-sm text-danger-800"
            role="alert"
          >
            {{ subscriptionPermissionError }}
          </div>
          <div
            v-if="activePartnerId && !canMessage"
            class="bg-danger-50 border-b border-danger-200 px-4 py-3 text-sm text-danger-800"
            role="alert"
          >
            Só é possível enviar mensagens enquanto houver uma troca ativa (pendente, aceita ou agendada)
            com este usuário.
          </div>

          <MessageThread
            class="flex-1 min-h-0"
            :messages="messages"
            :current-user-id="currentUserId"
            :partner="activePartner"
            :loading="threadLoading"
          />

          <MessageComposer
            :disabled="!activePartnerId || !canMessage"
            :disabled-reason="composerDisabledReason"
            :sending="sending"
            @send="handleSend"
          />
        </div>
      </BaseCard>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import BaseCard from '@/components/ui/BaseCard.vue'
import ConversationList, {
  type ConversationListItem,
  type ConversationPartner,
} from '@/components/chat/ConversationList.vue'
import MessageThread, { type ThreadMessage, type ThreadPartner } from '@/components/chat/MessageThread.vue'
import MessageComposer from '@/components/chat/MessageComposer.vue'
import { messageService } from '@/services/api'
import { conversationChannelName, getEcho } from '@/lib/echo'
import { useAuthStore } from '@/stores/auth'
import type Echo from 'laravel-echo'

interface BroadcastPayload {
  id: number
  content: string
  sender_id: number
  receiver_id: number
  exchange_id?: number | null
  created_at: string
  sender?: ConversationPartner
}

const authStore = useAuthStore()
const route = useRoute()
const router = useRouter()

const conversations = ref<ConversationListItem[]>([])
const messages = ref<ThreadMessage[]>([])
const activePartnerId = ref<number | null>(null)
const activePartner = ref<ThreadPartner | null>(null)
const canMessage = ref(true)
const listLoading = ref(true)
const threadLoading = ref(false)
const listError = ref<string | null>(null)
const sending = ref(false)
const echoUnavailable = ref(false)
const subscriptionPermissionError = ref<string | null>(null)

const activeExchangeHint =
  'enquanto houver uma troca ativa (pendente, aceita ou agendada)'

let subscribedChannelName: string | null = null
let echoChannel: ReturnType<Echo<'reverb'>['private']> | null = null
let connectionBoundEcho: Echo<'reverb'> | null = null

const currentUserId = computed(() => authStore.user?.id ?? 0)

const composerDisabledReason = computed(() => {
  if (!activePartnerId.value) return 'Selecione uma conversa para enviar mensagens.'
  if (!canMessage.value) {
    return `Só é possível enviar mensagens ${activeExchangeHint} com este usuário.`
  }
  return undefined
})

function parseUserQuery(): number | null {
  const raw = route.query.user
  const value = Array.isArray(raw) ? raw[0] : raw
  if (!value) return null
  const id = Number(value)
  return Number.isFinite(id) && id > 0 ? id : null
}

function upsertMessage(message: ThreadMessage) {
  if (messages.value.some((m) => m.id === message.id)) return
  messages.value.push(message)
}

function updateConversationPreview(payload: BroadcastPayload) {
  const partnerId =
    payload.sender_id === currentUserId.value ? payload.receiver_id : payload.sender_id
  const idx = conversations.value.findIndex((c) => c.partner.id === partnerId)
  const preview = {
    id: payload.id,
    content: payload.content,
    sender_id: payload.sender_id,
    created_at: payload.created_at,
    is_read: payload.sender_id === currentUserId.value,
  }
  if (idx >= 0) {
    const item = conversations.value[idx]
    const unread =
      payload.sender_id !== currentUserId.value && activePartnerId.value !== partnerId
        ? item.unread_count + 1
        : activePartnerId.value === partnerId
          ? 0
          : item.unread_count
    conversations.value[idx] = {
      ...item,
      last_message: preview,
      unread_count: unread,
    }
  } else if (payload.sender) {
    const partner =
      payload.sender_id === currentUserId.value
        ? conversations.value.find((c) => c.partner.id === partnerId)?.partner ?? {
            id: partnerId,
            name: 'Usuário',
          }
        : payload.sender
    conversations.value.unshift({
      partner,
      last_message: preview,
      unread_count: payload.sender_id === currentUserId.value ? 0 : 1,
      can_message: true,
    })
  }
  sortConversations()
}

function sortConversations() {
  conversations.value.sort((a, b) => {
    const ta = a.last_message?.created_at ?? ''
    const tb = b.last_message?.created_at ?? ''
    return tb.localeCompare(ta)
  })
}

async function loadConversations() {
  listLoading.value = true
  listError.value = null
  try {
    const response = await messageService.getConversations()
    if (response.data?.success) {
      conversations.value = response.data.data ?? []
      sortConversations()
    }
  } catch {
    listError.value = 'Não foi possível carregar suas conversas.'
  } finally {
    listLoading.value = false
  }
}

function leaveEchoChannel() {
  subscriptionPermissionError.value = null
  if (echoChannel) {
    echoChannel.stopListening('.message.sent')
    echoChannel = null
  }
  if (subscribedChannelName) {
    try {
      getEcho().leave(subscribedChannelName)
    } catch {
      /* ignore */
    }
    subscribedChannelName = null
  }
}

function bindEchoConnectionState() {
  const echo = getEcho()
  if (connectionBoundEcho === echo) return
  const connection = echo.connector?.pusher?.connection
  if (!connection) return
  connectionBoundEcho = echo
  connection.bind('state_change', (states: { current: string }) => {
    echoUnavailable.value = states.current === 'failed' || states.current === 'unavailable'
  })
  const current = connection.state
  if (current === 'failed' || current === 'unavailable') {
    echoUnavailable.value = true
  }
}

function subscribeToPartner(partnerId: number) {
  leaveEchoChannel()
  if (!currentUserId.value) return

  const echo = getEcho()
  bindEchoConnectionState()

  const channelName = conversationChannelName(currentUserId.value, partnerId)
  subscribedChannelName = channelName
  echoChannel = echo.private(channelName)
  echoChannel.error(() => {
    subscriptionPermissionError.value = 'Sem permissão para esta conversa'
  })
  echoChannel.listen('.message.sent', (payload: BroadcastPayload) => {
    const msg: ThreadMessage = {
      id: payload.id,
      content: payload.content,
      sender_id: payload.sender_id,
      receiver_id: payload.receiver_id,
      created_at: payload.created_at,
    }
    if (activePartnerId.value === partnerId) {
      upsertMessage(msg)
    }
    updateConversationPreview(payload)
    if (payload.sender_id !== currentUserId.value && activePartnerId.value !== partnerId) {
      void loadConversations()
    }
  })
}

async function loadThread(partnerId: number) {
  threadLoading.value = true
  messages.value = []
  try {
    const response = await messageService.getConversation(partnerId)
    if (response.data?.success) {
      const data = response.data.data
      activePartner.value = data.partner
      messages.value = data.messages ?? []
      canMessage.value = data.can_message ?? false

      const existing = conversations.value.find((c) => c.partner.id === partnerId)
      if (existing) {
        existing.can_message = canMessage.value
        existing.unread_count = 0
      } else {
        conversations.value.unshift({
          partner: data.partner,
          last_message: messages.value.length
            ? {
                id: messages.value[messages.value.length - 1].id,
                content: messages.value[messages.value.length - 1].content,
                sender_id: messages.value[messages.value.length - 1].sender_id,
                created_at: messages.value[messages.value.length - 1].created_at,
                is_read: true,
              }
            : null,
          unread_count: 0,
          can_message: canMessage.value,
        })
      }

      subscribeToPartner(partnerId)
    }
  } catch {
    listError.value = 'Não foi possível carregar esta conversa.'
    activePartnerId.value = null
    activePartner.value = null
    canMessage.value = false
    const { user: _user, ...restQuery } = route.query
    if (_user !== undefined) {
      await router.replace({ query: restQuery })
    }
  } finally {
    threadLoading.value = false
  }
}

async function selectPartner(partnerId: number) {
  if (activePartnerId.value === partnerId) return
  activePartnerId.value = partnerId
  const queryUser = parseUserQuery()
  if (queryUser !== partnerId) {
    await router.replace({ query: { ...route.query, user: String(partnerId) } })
  }
  await loadThread(partnerId)
}

async function handleSend(content: string) {
  if (!activePartnerId.value || !canMessage.value || sending.value) return
  sending.value = true
  try {
    const response = await messageService.send({
      receiver_id: activePartnerId.value,
      content,
    })
    if (response.data?.success) {
      const msg = response.data.data as ThreadMessage
      upsertMessage(msg)
      updateConversationPreview({
        id: msg.id,
        content: msg.content,
        sender_id: msg.sender_id,
        receiver_id: activePartnerId.value,
        created_at: msg.created_at,
        sender: authStore.user
          ? { id: authStore.user.id, name: authStore.user.name, avatar: authStore.user.avatar }
          : undefined,
      })
    }
  } catch {
    listError.value = `Falha ao enviar mensagem. Verifique se há uma troca ativa (pendente, aceita ou agendada) com este usuário.`
  } finally {
    sending.value = false
  }
}

async function applyRouteUser() {
  const userId = parseUserQuery()
  if (userId) {
    await selectPartner(userId)
  }
}

onMounted(async () => {
  await loadConversations()
  await applyRouteUser()
})

watch(
  () => route.query.user,
  async () => {
    const userId = parseUserQuery()
    if (userId && userId !== activePartnerId.value) {
      await selectPartner(userId)
    }
  }
)

onBeforeUnmount(() => {
  leaveEchoChannel()
})
</script>
