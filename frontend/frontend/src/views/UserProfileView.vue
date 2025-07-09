<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-width-custom">
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center min-h-96">
        <BaseLoading size="lg" message="Carregando perfil..." />
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="text-center py-12">
        <div class="text-6xl mb-4">😕</div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Erro ao carregar perfil</h3>
        <p class="text-gray-600 mb-6">{{ error }}</p>
        <BaseButton variant="primary" @click="$router.go(-1)">
          Voltar
        </BaseButton>
      </div>

      <!-- Profile Content -->
      <div v-else-if="userProfile" class="space-y-8">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
          <div class="flex items-start justify-between mb-6">
            <div class="flex items-center space-x-6">
              <div class="w-24 h-24 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                {{ getInitials(userProfile.name) }}
              </div>
              <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ userProfile.name }}</h1>
                <p v-if="userProfile.location" class="text-gray-600 mb-2">📍 {{ userProfile.location }}</p>
                <div class="flex items-center space-x-4">
                  <div class="flex items-center space-x-1">
                    <span class="text-yellow-500">⭐</span>
                    <span class="font-medium">{{ formatRating(userProfile.rating) }}</span>
                  </div>
                  <span class="text-gray-400">•</span>
                  <span class="text-sm text-gray-600">{{ userProfile.stats.exchanges }} trocas realizadas</span>
                  <span class="text-gray-400">•</span>
                  <span class="text-sm text-gray-600">Membro desde {{ formatDate(userProfile.member_since) }}</span>
                </div>
              </div>
            </div>
            <div class="flex space-x-3">
              <BaseButton variant="outline" @click="$router.go(-1)">
                ← Voltar
              </BaseButton>
              <BaseButton 
                variant="primary" 
                @click="showExchangeModal = true"
                :disabled="userProfile.skills.length === 0 || mySkills.length === 0"
              >
                🤝 Propor Troca
              </BaseButton>
            </div>
          </div>

          <!-- Bio -->
          <div v-if="userProfile.bio" class="mb-6">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Sobre</h3>
            <p class="text-gray-600">{{ userProfile.bio }}</p>
          </div>

          <!-- Stats -->
          <div class="grid grid-cols-3 gap-6 pt-6 border-t">
            <div class="text-center">
              <div class="text-2xl font-bold text-primary-600">{{ userProfile.stats.skills }}</div>
              <div class="text-sm text-gray-600">Habilidades</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-success-600">{{ userProfile.stats.exchanges }}</div>
              <div class="text-sm text-gray-600">Trocas</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-warning-500">{{ formatRating(userProfile.stats.rating) }}</div>
              <div class="text-sm text-gray-600">Avaliação</div>
            </div>
          </div>
        </div>

        <!-- Skills Section -->
        <div class="bg-white rounded-xl shadow-sm border">
          <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-900">Habilidades Disponíveis</h2>
          </div>
          <div class="p-6">
            <div v-if="userProfile.skills.length === 0" class="text-center py-12">
              <div class="text-4xl mb-3">📚</div>
              <p class="text-gray-600">Este usuário ainda não possui habilidades cadastradas</p>
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div 
                v-for="skill in userProfile.skills" 
                :key="skill.id"
                class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
                @click="selectSkillForExchange(skill)"
              >
                <div class="flex items-start justify-between mb-3">
                  <h3 class="font-semibold text-gray-900">{{ skill.title }}</h3>
                  <BaseBadge 
                    :variant="getLevelVariant(skill.level)"
                    size="sm"
                  >
                    {{ getLevelText(skill.level) }}
                  </BaseBadge>
                </div>
                <p class="text-sm text-gray-600 mb-3">{{ skill.description }}</p>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-primary-600 font-medium">{{ skill.category.name }}</span>
                  <div v-if="skill.tags && skill.tags.length > 0" class="flex flex-wrap gap-1">
                    <span 
                      v-for="tag in skill.tags.slice(0, 2)" 
                      :key="tag"
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                    >
                      {{ tag }}
                    </span>
                    <span v-if="skill.tags.length > 2" class="text-xs text-gray-500">+{{ skill.tags.length - 2 }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Exchange Modal -->
    <div v-if="showExchangeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
        <div class="p-6 border-b">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Propor Troca com {{ userProfile?.name }}</h3>
            <button 
              @click="closeExchangeModal"
              class="text-gray-400 hover:text-gray-600"
            >
              ✕
            </button>
          </div>
        </div>
        
        <div class="p-6 space-y-6">
          <!-- Skill Selection -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- My Skills -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-3">
                Sua habilidade para oferecer:
              </label>
              <div class="space-y-2 max-h-48 overflow-y-auto">
                <div 
                  v-for="skill in mySkills" 
                  :key="skill.id"
                  :class="{
                    'border-primary-500 bg-primary-50': exchangeForm.offered_skill_id === skill.id,
                    'border-gray-200 hover:border-gray-300': exchangeForm.offered_skill_id !== skill.id
                  }"
                  class="border rounded-lg p-3 cursor-pointer transition-colors"
                  @click="exchangeForm.offered_skill_id = skill.id"
                >
                  <div class="flex items-center space-x-3">
                    <input 
                      type="radio" 
                      :checked="exchangeForm.offered_skill_id === skill.id"
                      class="text-primary-600 focus:ring-primary-500"
                      readonly
                    >
                    <div class="flex-1">
                      <h4 class="font-medium text-gray-900">{{ skill.title }}</h4>
                      <p class="text-sm text-gray-600">{{ skill.category?.name }} • {{ getLevelText(skill.level) }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Their Skills -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-3">
                Habilidade que você quer aprender:
              </label>
              <div class="space-y-2 max-h-48 overflow-y-auto">
                <div 
                  v-for="skill in userProfile.skills" 
                  :key="skill.id"
                  :class="{
                    'border-primary-500 bg-primary-50': exchangeForm.requested_skill_id === skill.id,
                    'border-gray-200 hover:border-gray-300': exchangeForm.requested_skill_id !== skill.id
                  }"
                  class="border rounded-lg p-3 cursor-pointer transition-colors"
                  @click="exchangeForm.requested_skill_id = skill.id"
                >
                  <div class="flex items-center space-x-3">
                    <input 
                      type="radio" 
                      :checked="exchangeForm.requested_skill_id === skill.id"
                      class="text-primary-600 focus:ring-primary-500"
                      readonly
                    >
                    <div class="flex-1">
                      <h4 class="font-medium text-gray-900">{{ skill.title }}</h4>
                      <p class="text-sm text-gray-600">{{ skill.category.name }} • {{ getLevelText(skill.level) }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Message -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Mensagem (descreva sua proposta):
            </label>
            <textarea
              v-model="exchangeForm.message"
              rows="4"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              placeholder="Conte um pouco sobre sua experiência e por que gostaria de fazer essa troca..."
            ></textarea>
          </div>
        </div>

        <div class="p-6 border-t bg-gray-50 flex justify-end space-x-3">
          <BaseButton variant="outline" @click="closeExchangeModal">
            Cancelar
          </BaseButton>
          <BaseButton 
            variant="primary" 
            @click="submitExchangeRequest"
            :disabled="!canSubmitExchange || submittingExchange"
          >
            {{ submittingExchange ? 'Enviando...' : 'Enviar Proposta' }}
          </BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import BaseLoading from '@/components/ui/BaseLoading.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'
import { authService, skillService, exchangeService } from '@/services/api'

interface UserProfile {
  id: number
  name: string
  bio?: string
  location?: string
  avatar?: string
  rating: number
  total_exchanges: number
  member_since: string
  stats: {
    skills: number
    exchanges: number
    rating: number
  }
  skills: Skill[]
}

interface Skill {
  id: number
  title: string
  description: string
  level: string
  tags?: string[]
  category: {
    id: number
    name: string
  }
}

const route = useRoute()
const router = useRouter()

// Estado principal
const loading = ref(true)
const error = ref<string | null>(null)
const userProfile = ref<UserProfile | null>(null)
const mySkills = ref<Skill[]>([])

// Modal de troca
const showExchangeModal = ref(false)
const submittingExchange = ref(false)
const exchangeForm = ref({
  offered_skill_id: null as number | null,
  requested_skill_id: null as number | null,
  message: ''
})

// Computed
const canSubmitExchange = computed(() => {
  return exchangeForm.value.offered_skill_id && 
         exchangeForm.value.requested_skill_id && 
         exchangeForm.value.message.trim().length > 10
})

// Funções utilitárias
const getInitials = (name?: string): string => {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const formatRating = (rating?: number): string => {
  return (rating || 0).toFixed(1)
}

const formatDate = (dateString: string): string => {
  const date = new Date(dateString)
  return date.toLocaleDateString('pt-BR', {
    year: 'numeric',
    month: 'long'
  })
}

const getLevelText = (level: string): string => {
  const levels: { [key: string]: string } = {
    'beginner': 'Iniciante',
    'intermediate': 'Intermediário',
    'advanced': 'Avançado',
    'expert': 'Especialista'
  }
  return levels[level] || level
}

const getLevelVariant = (level: string): string => {
  const variants: { [key: string]: string } = {
    'beginner': 'secondary',
    'intermediate': 'warning', 
    'advanced': 'primary',
    'expert': 'success'
  }
  return variants[level] || 'secondary'
}

// Funções principais
const loadUserProfile = async () => {
  try {
    loading.value = true
    error.value = null

    const userId = route.params.userId as string
    
    // Carregar perfil do usuário
    const profileResponse = await authService.getUserProfile(userId)
    
    if (profileResponse.data.success) {
      userProfile.value = profileResponse.data.user
    } else {
      throw new Error(profileResponse.data.message || 'Erro ao carregar perfil')
    }

    // Carregar minhas habilidades para proposta de troca
    try {
      const mySkillsResponse = await skillService.getMySkills()
      mySkills.value = mySkillsResponse.data.skills || []
    } catch (skillsError) {
      console.warn('Erro ao carregar suas habilidades:', skillsError)
      mySkills.value = []
    }

  } catch (err: any) {
    console.error('Erro ao carregar perfil:', err)
    error.value = err.response?.data?.message || 'Erro ao carregar perfil do usuário'
  } finally {
    loading.value = false
  }
}

const selectSkillForExchange = (skill: Skill) => {
  if (mySkills.value.length === 0) {
    alert('Você precisa ter habilidades cadastradas para propor uma troca')
    return
  }
  
  exchangeForm.value.requested_skill_id = skill.id
  showExchangeModal.value = true
}

const closeExchangeModal = () => {
  showExchangeModal.value = false
  exchangeForm.value = {
    offered_skill_id: null,
    requested_skill_id: null,
    message: ''
  }
}

const submitExchangeRequest = async () => {
  if (!canSubmitExchange.value || !userProfile.value) return

  try {
    submittingExchange.value = true

    const exchangeData = {
      receiver_id: userProfile.value.id,
      offered_skill_id: exchangeForm.value.offered_skill_id,
      requested_skill_id: exchangeForm.value.requested_skill_id,
      message: exchangeForm.value.message
    }

    const response = await exchangeService.create(exchangeData)

    if (response.data.success) {
      alert('Proposta de troca enviada com sucesso!')
      closeExchangeModal()
      router.push('/dashboard')
    } else {
      throw new Error(response.data.message || 'Erro ao enviar proposta')
    }

  } catch (err: any) {
    console.error('Erro ao enviar proposta:', err)
    const errorMessage = err.response?.data?.message || 'Erro ao enviar proposta de troca'
    alert(errorMessage)
  } finally {
    submittingExchange.value = false
  }
}

onMounted(() => {
  loadUserProfile()
})
</script>

<style scoped>
.max-width-custom {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.container-custom {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

/* Scrollbar customizada */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}
</style> 