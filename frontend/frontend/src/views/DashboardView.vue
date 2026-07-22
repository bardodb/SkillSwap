<template>
  <div class="min-h-screen bg-gray-50" data-testid="dashboard-page">
    <!-- Header Dashboard -->
    <div class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl flex items-center justify-center">
              <span class="text-white text-xl font-bold">📊</span>
            </div>
            <div>
              <h1 class="text-2xl font-bold text-secondary-900">Dashboard</h1>
              <p class="text-sm text-secondary-600">Visão geral das suas atividades</p>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <div class="flex items-center space-x-2 text-sm text-secondary-600">
              <span class="w-2 h-2 bg-success-400 rounded-full"></span>
              <span>Online</span>
            </div>
            <BaseButton variant="primary" size="sm" data-testid="dashboard-add-skill" @click="showAddSkillModal = true">
              + Nova Skill
            </BaseButton>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-20">
      <BaseLoading size="lg" message="Carregando dashboard..." />
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-20">
      <div class="text-danger-500 text-lg mb-4">{{ error }}</div>
      <BaseButton variant="outline" @click="loadDashboard">
        Tentar Novamente
      </BaseButton>
    </div>

    <!-- Dashboard Content -->
    <div v-else class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Quick Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" data-testid="dashboard-stats">
        <!-- Skills Card -->
        <div class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition-shadow stats-card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Habilidades</p>
              <p class="text-3xl font-bold text-gray-900">{{ userStats.mySkills }}</p>
              <p 
                :class="{
                  'text-green-600': weeklyStats.skills?.type === 'positive',
                  'text-gray-500': weeklyStats.skills?.type === 'neutral',
                  'text-red-600': weeklyStats.skills?.type === 'negative'
                }"
                class="text-xs"
              >
                {{ weeklyStats.skills?.message || 'Carregando...' }}
              </p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <span class="text-blue-600 text-xl">🎯</span>
            </div>
          </div>
        </div>

        <!-- Exchanges Card -->
        <div class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition-shadow stats-card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Trocas</p>
              <p class="text-3xl font-bold text-gray-900">{{ userStats.exchanges }}</p>
              <p 
                :class="{
                  'text-green-600': weeklyStats.exchanges?.type === 'positive',
                  'text-gray-500': weeklyStats.exchanges?.type === 'neutral',
                  'text-red-600': weeklyStats.exchanges?.type === 'negative'
                }"
                class="text-xs"
              >
                {{ weeklyStats.exchanges?.message || 'Carregando...' }}
              </p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <span class="text-green-600 text-xl">🤝</span>
            </div>
          </div>
        </div>

        <!-- Rating Card -->
        <div class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition-shadow stats-card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Avaliação</p>
              <p class="text-3xl font-bold text-gray-900">{{ userStats.rating }}</p>
              <p 
                :class="{
                  'text-green-600': weeklyStats.rating?.type === 'positive',
                  'text-gray-500': weeklyStats.rating?.type === 'neutral',
                  'text-red-600': weeklyStats.rating?.type === 'negative'
                }"
                class="text-xs"
              >
                {{ weeklyStats.rating?.message || 'Carregando...' }}
              </p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
              <span class="text-yellow-600 text-xl">⭐</span>
            </div>
          </div>
        </div>

        <!-- Connections Card -->
        <div class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition-shadow stats-card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Conexões</p>
              <p class="text-3xl font-bold text-gray-900">{{ userStats.connections }}</p>
              <p 
                :class="{
                  'text-green-600': weeklyStats.connections?.type === 'positive',
                  'text-gray-500': weeklyStats.connections?.type === 'neutral',
                  'text-red-600': weeklyStats.connections?.type === 'negative'
                }"
                class="text-xs"
              >
                {{ weeklyStats.connections?.message || 'Carregando...' }}
              </p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
              <span class="text-purple-600 text-xl">👥</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column (2/3) -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Activity Feed -->
          <div class="bg-white rounded-xl shadow-sm border">
            <div class="p-6 border-b">
              <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Atividade Recente</h2>
                <BaseButton variant="outline" size="sm">Ver Todas</BaseButton>
              </div>
            </div>
            <div class="p-6">
              <div v-if="recentExchanges.length === 0" class="text-center py-8 text-gray-500">
                <span class="text-4xl block mb-2">📈</span>
                <p>Suas atividades aparecerão aqui</p>
              </div>
              <div v-else class="space-y-4">
                <div 
                  v-for="exchange in recentExchanges" 
                  :key="exchange.id"
                  class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg"
                >
                  <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                    {{ getInitials(exchange.partnerName) }}
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">
                      Troca com {{ exchange.partnerName }}
                    </p>
                    <p class="text-sm text-gray-600">{{ exchange.skillExchanged }}</p>
                    <p class="text-xs text-gray-500">{{ formatDate(exchange.date) }}</p>
                  </div>
                  <div class="flex-shrink-0">
                    <span 
                      :class="{
                        'bg-green-100 text-green-800': exchange.status === 'completed',
                        'bg-yellow-100 text-yellow-800': exchange.status === 'pending',
                        'bg-red-100 text-red-800': exchange.status === 'cancelled'
                      }"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    >
                      {{ getStatusText(exchange.status) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- My Skills Management -->
          <BaseCard>
            <div class="p-6">
              <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-secondary-900">Gerenciar Habilidades</h2>
                <BaseButton 
                  variant="outline" 
                  size="sm"
                  @click="showAddSkillModal = true"
                >
                  <template #icon-left>
                    <span>➕</span>
                  </template>
                  Adicionar
                </BaseButton>
              </div>
              <div v-if="!hasSkills" class="text-center py-12">
                <div class="text-6xl mb-4">📚</div>
                <h3 class="text-lg font-semibold text-secondary-900 mb-2">Nenhuma habilidade cadastrada</h3>
                <p class="text-secondary-600 mb-4">Comece adicionando suas primeiras habilidades!</p>

                <BaseButton 
                  variant="primary" 
                  @click="showAddSkillModal = true"
                >
                  Adicionar Primeira Habilidade
                </BaseButton>
              </div>
              <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div 
                  v-for="skill in userSkills" 
                  :key="skill.id"
                  data-testid="dashboard-skill-item"
                  class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                >
                  <div class="flex items-start justify-between mb-3">
                    <h3 class="font-semibold text-secondary-900" data-testid="dashboard-skill-title">{{ skill.title }}</h3>
                    <div class="flex items-center space-x-2">
                      <BaseBadge 
                        :variant="getLevelVariant(skill.level)"
                        size="sm"
                      >
                        {{ getLevelText(skill.level) }}
                      </BaseBadge>
                      <button 
                        :data-testid="`dashboard-skill-delete-${skill.id}`"
                        @click="deleteSkill(skill.id)"
                        class="text-danger-500 hover:text-danger-700 text-sm"
                      >
                        🗑️
                      </button>
                    </div>
                  </div>
                  <p class="text-sm text-secondary-600 mb-3">{{ skill.description }}</p>
                  <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                      <span class="text-sm text-primary-600 font-medium">{{ skill.category?.name }}</span>
                      <span v-if="skill.is_available" class="text-xs text-success-600">• Disponível</span>
                    </div>
                    <div class="flex items-center text-sm text-secondary-500">
                      <span>👁️ {{ skill.views || 0 }} visualizações</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </BaseCard>
        </div>

        <!-- Right Column (1/3) -->
        <div class="space-y-8">
          <!-- Quick Profile Summary -->
          <div class="bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl text-white p-6">
            <div class="flex items-center space-x-4 mb-4">
              <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-2xl font-bold">
                {{ getInitials(user?.name) }}
              </div>
              <div>
                <h3 class="font-semibold text-lg">{{ user?.name || 'Usuário' }}</h3>
                <p class="text-blue-100 text-sm">{{ user?.location || 'Localização não informada' }}</p>
              </div>
            </div>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-blue-100">Avaliação:</span>
                <span class="font-semibold">{{ formatRating(user?.rating) }} ⭐</span>
              </div>
              <div class="flex justify-between">
                <span class="text-blue-100">Trocas:</span>
                <span class="font-semibold">{{ userStats.exchanges }}</span>
              </div>
            </div>
            <BaseButton 
              variant="outline" 
              size="sm" 
              class="mt-4 w-full border-white text-white hover:bg-white hover:text-blue-600"
              tag="router-link"
              to="/profile"
            >
              Editar Perfil
            </BaseButton>
          </div>

          <!-- Skill Matches -->
          <div class="bg-white rounded-xl shadow-sm border" data-testid="dashboard-matches">
            <div class="p-6 border-b">
              <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Matches Recomendados</h2>
                <BaseButton 
                  variant="outline" 
                  size="sm"
                  @click="refreshMatches"
                  :disabled="loadingMatches"
                >
                  {{ loadingMatches ? '⏳' : '🔄' }}
                </BaseButton>
              </div>
            </div>
            <div class="p-6">
              <div v-if="loadingMatches" class="text-center py-8">
                <BaseLoading size="sm" message="Buscando..." />
              </div>
              <div v-else-if="potentialMatches.length === 0" class="text-center py-8 text-gray-500">
                <span class="text-4xl block mb-2">🎯</span>
                <p class="text-sm">{{ userSkills.length === 0 ? 'Adicione habilidades para ver matches' : 'Nenhum match encontrado' }}</p>
                <BaseButton 
                  v-if="userSkills.length === 0"
                  variant="primary" 
                  size="sm"
                  class="mt-3"
                  @click="showAddSkillModal = true"
                >
                  Adicionar Skills
                </BaseButton>
              </div>
              <div v-else class="space-y-4">
                <div 
                  v-for="match in potentialMatches.slice(0, 3)" 
                  :key="match.id"
                  class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors"
                >
                  <div class="flex items-start space-x-3 mb-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                      {{ getInitials(match.user_name) }}
                    </div>
                    <div class="flex-1 min-w-0">
                      <h4 class="font-medium text-gray-900 truncate">{{ match.user_name }}</h4>
                      <div class="flex items-center space-x-1 text-xs">
                        <span class="text-yellow-500">⭐</span>
                        <span class="text-gray-600">{{ formatRating(match.user_rating) }}</span>
                      </div>
                    </div>
                  </div>
                  <div class="mb-3">
                    <h5 class="text-sm font-medium text-gray-900 mb-1">{{ match.skill_title }}</h5>
                    <p class="text-xs text-gray-600 line-clamp-2">{{ match.skill_description }}</p>
                  </div>
                  <div class="flex items-center justify-between mb-3">
                    <span 
                      :class="{
                        'bg-green-100 text-green-800': match.skill_level === 'expert',
                        'bg-blue-100 text-blue-800': match.skill_level === 'advanced',
                        'bg-yellow-100 text-yellow-800': match.skill_level === 'intermediate',
                        'bg-gray-100 text-gray-800': match.skill_level === 'beginner'
                      }"
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                    >
                      {{ getLevelText(match.skill_level) }}
                    </span>
                    <span class="text-xs text-green-600 font-medium">{{ match.match_score }}%</span>
                  </div>
                  <div class="flex space-x-2">
                    <BaseButton 
                      variant="outline" 
                      size="sm"
                      class="flex-1"
                      @click="viewUserProfile(match.user_id)"
                    >
                      Ver Perfil
                    </BaseButton>
                    <BaseButton 
                      variant="primary"
                      size="sm"
                      class="flex-1"
                      @click="initiateExchange(match)"
                    >
                      🤝 Trocar
                    </BaseButton>
                  </div>
                </div>
              </div>
              <div v-if="potentialMatches.length > 3" class="mt-4 text-center">
                <BaseButton variant="outline" size="sm">
                  Ver Todos ({{ potentialMatches.length }})
                </BaseButton>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white rounded-xl shadow-sm border">
            <div class="p-6 border-b">
              <h2 class="text-lg font-semibold text-gray-900">Ações Rápidas</h2>
            </div>
            <div class="p-6 space-y-3">
              <BaseButton 
                variant="outline" 
                size="sm" 
                class="w-full justify-start"
                tag="router-link"
                to="/skills"
              >
                <span class="mr-2">🔍</span>
                Explorar Habilidades
              </BaseButton>
              <BaseButton 
                variant="outline" 
                size="sm" 
                class="w-full justify-start"
                tag="router-link"
                to="/chat"
              >
                <span class="mr-2">💬</span>
                Minhas Conversas
              </BaseButton>
              <BaseButton 
                variant="outline" 
                size="sm" 
                class="w-full justify-start"
                @click="showAddSkillModal = true"
              >
                <span class="mr-2">➕</span>
                Nova Habilidade
              </BaseButton>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Skill Modal -->
    <div v-if="showAddSkillModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" data-testid="add-skill-modal">
      <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-bold text-secondary-900">Nova Habilidade</h3>
          <button 
            data-testid="add-skill-close"
            @click="showAddSkillModal = false"
            class="text-secondary-500 hover:text-secondary-700"
          >
            ✕
          </button>
        </div>
        
        <form @submit.prevent="addSkill" class="space-y-4" novalidate>
          <BaseInput
            v-model="newSkill.title"
            data-testid="add-skill-title"
            label="Título da Habilidade"
            placeholder="Ex: Desenvolvimento Laravel"
            :error="skillFormErrors.title"
            required
          />
          
          <BaseInput
            v-model="newSkill.description"
            data-testid="add-skill-description"
            label="Descrição"
            type="textarea"
            placeholder="Descreva sua habilidade e o que você pode ensinar..."
            rows="3"
            :error="skillFormErrors.description"
            required
          />
          
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2">Nível</label>
            <select 
              v-model="newSkill.level"
              data-testid="add-skill-level"
              class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              required
            >
              <option value="">Selecione o nível</option>
              <option value="beginner">Iniciante</option>
              <option value="intermediate">Intermediário</option>
              <option value="advanced">Avançado</option>
              <option value="expert">Especialista</option>
            </select>
            <div v-if="skillFormErrors.level" class="form-error">{{ skillFormErrors.level }}</div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2">Categoria</label>
            <select 
              v-model="newSkill.category_id"
              data-testid="add-skill-category"
              class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              required
            >
              <option value="">Selecione uma categoria</option>
              <option 
                v-for="category in categories" 
                :key="category.id" 
                :value="category.id"
              >
                {{ category.name }}
              </option>
            </select>
            <div v-if="skillFormErrors.category_id" class="form-error">{{ skillFormErrors.category_id }}</div>
          </div>
          
          <div class="flex items-center justify-end space-x-3 pt-4">
            <BaseButton 
              variant="outline" 
              type="button"
              data-testid="add-skill-cancel"
              @click="showAddSkillModal = false"
            >
              Cancelar
            </BaseButton>
            <BaseButton 
              variant="primary" 
              type="submit"
              data-testid="add-skill-submit"
              :disabled="addingSkill"
            >
              {{ addingSkill ? 'Salvando...' : 'Salvar' }}
            </BaseButton>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { authService, skillService, categoryService, exchangeService, statsService } from '@/services/api'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'
import BaseLoading from '@/components/ui/BaseLoading.vue'
import BaseInput from '@/components/ui/BaseInput.vue'

interface User {
  id: string
  name: string
  email: string
  bio?: string
  location?: string
  rating?: number | null
  total_exchanges?: number
}

interface Skill {
  id: string
  title: string
  description: string
  level: string
  category_id: string
  category?: {
    id: string
    name: string
  }
  views?: number
  is_available?: boolean
  created_at?: string
  updated_at?: string
  tags?: string[]
  image?: string | null
}

interface Category {
  id: string
  name: string
}

interface Exchange {
  id: string
  partnerName: string
  skillExchanged: string
  status: string
  date: string
}

interface Match {
  id: string
  skill_title: string
  skill_description: string
  skill_level: string
  user_id: string
  user_name: string
  user_rating: number
  user_location: string
  category_name: string
  match_score: number
}

const router = useRouter()

// Estado principal
const loading = ref(true)
const error = ref<string | null>(null)
const user = ref<User | null>(null)
const userSkills = ref<Skill[]>([])
const categories = ref<Category[]>([])
const recentExchanges = ref<Exchange[]>([])
const potentialMatches = ref<Match[]>([])
const loadingMatches = ref(false)

// Estatísticas reais do usuário
const realUserStats = ref({
  skills: 0,
  exchanges: 0,
  connections: 0,
  rating: 0
})

// Estatísticas semanais dinâmicas
const weeklyStats = ref({
  skills: { change: 0, message: 'Carregando...', type: 'neutral' },
  exchanges: { change: 0, message: 'Carregando...', type: 'neutral' },
  connections: { change: 0, message: 'Carregando...', type: 'neutral' },
  rating: { change: 0, message: 'Carregando...', type: 'neutral' }
})

// Modal de adicionar habilidade
const showAddSkillModal = ref(false)
const addingSkill = ref(false)
const newSkill = ref({
  title: '',
  description: '',
  level: '',
  category_id: null as string | null
})

const skillFormErrors = ref<Record<string, string>>({})

const validateNewSkill = (): boolean => {
  const errors: Record<string, string> = {}
  if (!newSkill.value.title.trim()) {
    errors.title = 'Título é obrigatório'
  }
  if (!newSkill.value.description.trim()) {
    errors.description = 'Descrição é obrigatória'
  }
  if (!newSkill.value.level) {
    errors.level = 'Selecione o nível'
  }
  if (!newSkill.value.category_id) {
    errors.category_id = 'Selecione uma categoria'
  }
  skillFormErrors.value = errors
  return Object.keys(errors).length === 0
}

// Estatísticas computadas
const userStats = computed(() => {
  const stats = realUserStats.value
  return {
    mySkills: Number(stats.skills) || 0,
    exchanges: Number(stats.exchanges) || 0,
    rating: Number(stats.rating || 0).toFixed(1),
    connections: Number(stats.connections) || 0
  }
})

// Computed property para verificar se tem habilidades
const hasSkills = computed(() => {
  return Array.isArray(userSkills.value) && userSkills.value.length > 0
})

// Funções utilitárias
const getInitials = (name?: string): string => {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const formatRating = (rating: any): string => {
  // Verificar se rating é null, undefined ou não é um número válido
  if (rating === null || rating === undefined || rating === '' || isNaN(Number(rating))) {
    return '0.0'
  }
  
  const numRating = Number(rating)
  return numRating.toFixed(1)
}

const getLevelVariant = (level: string): 'primary' | 'secondary' | 'danger' | 'success' | 'warning' => {
  const variants: Record<string, 'primary' | 'secondary' | 'danger' | 'success' | 'warning'> = {
    beginner: 'success',
    intermediate: 'warning', 
    advanced: 'danger',
    expert: 'secondary'
  }
  return variants[level] || 'secondary'
}

const getLevelText = (level: string) => {
  const texts: Record<string, string> = {
    beginner: 'Iniciante',
    intermediate: 'Intermediário',
    advanced: 'Avançado',
    expert: 'Especialista'
  }
  return texts[level] || level
}

const getStatusVariant = (status: string): 'primary' | 'secondary' | 'danger' | 'success' | 'warning' => {
  const variants: Record<string, 'primary' | 'secondary' | 'danger' | 'success' | 'warning'> = {
    completed: 'success',
    pending: 'warning',
    cancelled: 'danger'
  }
  return variants[status] || 'secondary'
}

const getStatusText = (status: string) => {
  const texts: Record<string, string> = {
    completed: 'Concluída',
    pending: 'Pendente',
    cancelled: 'Cancelada'
  }
  return texts[status] || status
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

// Função principal de carregamento
const loadDashboard = async () => {
  try {
    loading.value = true
    error.value = null

    // Carregar dados do usuário
    try {
      const userResponse = await authService.getUser()
      if (userResponse.data && userResponse.data.success) {
        user.value = userResponse.data.data
      } else {
        throw new Error('Usuário não encontrado')
      }
    } catch (userError) {
      console.error('Erro ao carregar dados do usuário:', userError)
      // Se não conseguir carregar o usuário, redirecionar para login
      router.push('/login')
      return
    }

    // Carregar habilidades do usuário
    await loadUserSkills()

    // Carregar estatísticas reais do usuário
    try {
      const userStatsResponse = await statsService.getUserStats()
      if (userStatsResponse.data && userStatsResponse.data.data) {
        realUserStats.value = {
          skills: Number(userStatsResponse.data.data.skills) || 0,
          exchanges: Number(userStatsResponse.data.data.exchanges) || 0,
          connections: Number(userStatsResponse.data.data.connections) || 0,
          rating: Number(userStatsResponse.data.data.rating) || 0
        }
      }
    } catch (statsError) {
      console.error('Erro ao carregar estatísticas do usuário:', statsError)
    }

    // Carregar categorias
    try {
      const categoriesResponse = await categoryService.getAll()
      if (categoriesResponse.data && categoriesResponse.data.success) {
        categories.value = categoriesResponse.data.data || []
      } else {
        console.warn('Resposta inválida ao carregar categorias')
        categories.value = []
      }
    } catch (categoriesError) {
      console.error('Erro ao carregar categorias:', categoriesError)
      categories.value = []
    }

    // Carregar trocas recentes
    try {
      const exchangesResponse = await exchangeService.getAll()
      if (exchangesResponse.data && exchangesResponse.data.success) {
        recentExchanges.value = exchangesResponse.data.data.map((exchange: any) => ({
          id: exchange.id,
          partnerName: exchange.partner.name,
          skillExchanged: `${exchange.offered_skill.title} ↔ ${exchange.requested_skill.title}`,
          status: exchange.status,
          date: exchange.created_at
        }))
      } else {
        recentExchanges.value = []
      }
    } catch (exchangesError) {
      console.error('Erro ao carregar trocas recentes:', exchangesError)
      recentExchanges.value = []
    }

    // Carregar matches potenciais
    await refreshMatches()

    // Carregar estatísticas semanais
    await loadWeeklyStats()

  } catch (err) {
    console.error('Erro ao carregar dashboard:', err)
    error.value = 'Erro ao carregar dados do dashboard'
  } finally {
    loading.value = false
  }
}

// Função para adicionar habilidade
const addSkill = async () => {
  if (!validateNewSkill()) return

  try {
    addingSkill.value = true
    skillFormErrors.value = {}
    
    const skillData = {
      ...newSkill.value,
      is_available: true
    }
    
    const response = await skillService.create(skillData)
    if (response.data && response.data.success) {
      userSkills.value.push(response.data.data)
      showAddSkillModal.value = false
      
      // Atualizar estatísticas em tempo real
      await updateUserStats()
      await loadWeeklyStats()
      
      // Reset form
      newSkill.value = {
        title: '',
        description: '',
        level: '',
        category_id: null
      }
      skillFormErrors.value = {}
    } else {
      throw new Error('Erro ao criar habilidade')
    }
  } catch (err) {
    console.error('Erro ao adicionar habilidade:', err)
    error.value = 'Erro ao adicionar habilidade'
  } finally {
    addingSkill.value = false
  }
}

// Função para deletar habilidade
const deleteSkill = async (skillId: string) => {
  if (!confirm('Tem certeza que deseja excluir esta habilidade?')) return
  
  try {
    const response = await skillService.delete(skillId)
    if (response.data && response.data.success) {
      userSkills.value = userSkills.value.filter(skill => skill.id !== skillId)
      
      // Atualizar estatísticas em tempo real
      await updateUserStats()
      await loadWeeklyStats()
    } else {
      throw new Error('Erro ao deletar habilidade')
    }
  } catch (err) {
    console.error('Erro ao deletar habilidade:', err)
    error.value = 'Erro ao deletar habilidade'
  }
}

// Função para carregar habilidades do usuário
const loadUserSkills = async () => {
  try {
    const skillsResponse = await skillService.getMySkills()
    
    if (skillsResponse?.data?.success && skillsResponse.data.data) {
      userSkills.value = skillsResponse.data.data.map((skill: Skill) => ({
        ...skill,
        views: skill.views || 0,
        is_available: skill.is_available !== false,
        category: skill.category || { id: '', name: 'Sem categoria' }
      }))
    } else {
      console.warn('Resposta inválida ao carregar skills:', skillsResponse.data)
      userSkills.value = []
    }
  } catch (skillsError) {
    console.error('Erro ao carregar skills:', skillsError)
    userSkills.value = []
  }
}

// Função para visualizar perfil do usuário
const viewUserProfile = (userId: string) => {
  router.push(`/users/${userId}/profile`)
}

// Função para atualizar matches
const refreshMatches = async () => {
  try {
    loadingMatches.value = true
    const matchesResponse = await skillService.getMatches()
    if (matchesResponse.data && matchesResponse.data.data) {
      potentialMatches.value = matchesResponse.data.data || []
    } else {
      console.warn('Resposta inválida ao carregar matches')
      potentialMatches.value = []
    }
  } catch (err) {
    console.error('Erro ao atualizar matches:', err)
    error.value = 'Erro ao carregar matches'
    potentialMatches.value = []
  } finally {
    loadingMatches.value = false
  }
}

// Função para atualizar estatísticas do usuário
const updateUserStats = async () => {
  try {
    const userStatsResponse = await statsService.getUserStats()
    if (userStatsResponse.data && userStatsResponse.data.data) {
      const newStats = {
        skills: Number(userStatsResponse.data.data.skills) || 0,
        exchanges: Number(userStatsResponse.data.data.exchanges) || 0,
        connections: Number(userStatsResponse.data.data.connections) || 0,
        rating: Number(userStatsResponse.data.data.rating) || 0
      }
      
      // Animar a mudança dos valores
      const oldStats = { ...realUserStats.value }
      realUserStats.value = newStats
      
      // Adicionar classe de animação se os valores mudaram
      if (oldStats.skills !== newStats.skills || 
          oldStats.exchanges !== newStats.exchanges || 
          oldStats.connections !== newStats.connections || 
          oldStats.rating !== newStats.rating) {
        // Trigger animation
        if (typeof document !== 'undefined') {
          document.querySelectorAll('.stats-card').forEach(card => {
            card.classList.add('stats-updated')
            setTimeout(() => card.classList.remove('stats-updated'), 1000)
          })
        }
      }
    }
  } catch (statsError) {
    console.error('Erro ao atualizar estatísticas do usuário:', statsError)
  }
}

// Função para carregar estatísticas semanais
const loadWeeklyStats = async () => {
  try {
    const weeklyStatsResponse = await statsService.getWeeklyStats()
    if (weeklyStatsResponse.data && weeklyStatsResponse.data.data) {
      weeklyStats.value = weeklyStatsResponse.data.data
    } else {
      console.warn('Resposta inválida ao carregar estatísticas semanais')
    }
  } catch (err) {
    console.error('Erro ao carregar estatísticas semanais:', err)
  }
}

// Função para favoritar um match
const favoriteMatch = (matchId: number) => {
  // Para implementação futura - pode salvar em favoritos locais ou no backend
  alert(`Match ${matchId} adicionado aos favoritos! (Funcionalidade em desenvolvimento)`)
}

// Função para iniciar uma troca
const initiateExchange = (match: Match) => {
  // Navegar para o perfil do usuário onde pode fazer a proposta de troca
  router.push(`/users/${match.user_id}/profile`)
}

// Intervalo para atualização automática das estatísticas
let statsUpdateInterval: number | null = null

onMounted(() => {
  loadDashboard()
  
  // Atualizar estatísticas a cada 30 segundos
  statsUpdateInterval = setInterval(async () => {
    await updateUserStats()
    await loadWeeklyStats()
  }, 30000)
})

onUnmounted(() => {
  if (statsUpdateInterval) {
    clearInterval(statsUpdateInterval)
  }
})
</script>

<style scoped>
/* Animações customizadas */
.fade-in {
  animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Animação para atualização de estatísticas */
.stats-updated {
  animation: statsUpdate 0.5s ease-in-out;
}

@keyframes statsUpdate {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); background-color: rgba(59, 130, 246, 0.1); }
  100% { transform: scale(1); }
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
