<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-white">
    <!-- Header Section -->
    <section class="bg-white border-b border-gray-200 sticky top-0 z-10">
      <div class="container-custom py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-secondary-900 mb-2">
              Olá, {{ user?.name || 'Usuário' }}! 👋
            </h1>
            <p class="text-secondary-600">
              Bem-vindo ao seu painel pessoal do SkillSwap
            </p>
          </div>
          <div class="flex items-center space-x-4">
            <BaseButton 
              variant="outline" 
              size="md"
              @click="toggleEditMode"
            >
              <template #icon-left>
                <span>{{ isEditMode ? '💾' : '✏️' }}</span>
              </template>
              {{ isEditMode ? 'Salvar' : 'Editar Perfil' }}
            </BaseButton>
            <BaseButton 
              variant="primary" 
              size="md"
              tag="router-link"
              to="/skills"
            >
              <template #icon-left>
                <span>➕</span>
              </template>
              Nova Habilidade
            </BaseButton>
          </div>
        </div>
      </div>
    </section>

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
    <div v-else class="container-custom py-8">
      <!-- User Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <BaseCard hover class="text-center p-6">
          <div class="w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <span class="text-white text-2xl">🎯</span>
          </div>
          <h3 class="text-2xl font-bold text-secondary-900 mb-1">{{ userStats.mySkills }}</h3>
          <p class="text-secondary-600">Minhas Habilidades</p>
        </BaseCard>

        <BaseCard hover class="text-center p-6">
          <div class="w-16 h-16 bg-gradient-to-br from-success-500 to-success-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <span class="text-white text-2xl">🤝</span>
          </div>
          <h3 class="text-2xl font-bold text-secondary-900 mb-1">{{ userStats.exchanges }}</h3>
          <p class="text-secondary-600">Trocas Realizadas</p>
        </BaseCard>

        <BaseCard hover class="text-center p-6">
          <div class="w-16 h-16 bg-gradient-to-br from-warning-500 to-warning-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <span class="text-white text-2xl">⭐</span>
          </div>
          <h3 class="text-2xl font-bold text-secondary-900 mb-1">{{ userStats.rating }}</h3>
          <p class="text-secondary-600">Avaliação Média</p>
        </BaseCard>

        <BaseCard hover class="text-center p-6">
          <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <span class="text-white text-2xl">👥</span>
          </div>
          <h3 class="text-2xl font-bold text-secondary-900 mb-1">{{ userStats.connections }}</h3>
          <p class="text-secondary-600">Conexões</p>
        </BaseCard>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column - Profile & Skills -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Profile Section -->
          <BaseCard>
            <div class="p-6">
              <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-secondary-900">Meu Perfil</h2>
                <BaseBadge 
                  :variant="(Number(user?.rating) || 0) >= 4.5 ? 'success' : (Number(user?.rating) || 0) >= 4 ? 'warning' : 'secondary'"
                >
                  ⭐ {{ formatRating(user?.rating) }}
                </BaseBadge>
              </div>
              
              <div class="flex items-start space-x-6">
                <div class="w-24 h-24 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                  {{ getInitials(user?.name) }}
                </div>
                
                <div class="flex-1">
                  <div v-if="!isEditMode" class="space-y-3">
                    <div>
                      <label class="text-sm font-medium text-secondary-700">Nome</label>
                      <p class="text-secondary-900">{{ user?.name || 'Não informado' }}</p>
                    </div>
                    <div>
                      <label class="text-sm font-medium text-secondary-700">Email</label>
                      <p class="text-secondary-900">{{ user?.email || 'Não informado' }}</p>
                    </div>
                    <div>
                      <label class="text-sm font-medium text-secondary-700">Localização</label>
                      <p class="text-secondary-900">{{ user?.location || 'Não informado' }}</p>
                    </div>
                    <div>
                      <label class="text-sm font-medium text-secondary-700">Bio</label>
                      <p class="text-secondary-900">{{ user?.bio || 'Fale um pouco sobre você...' }}</p>
                    </div>
                  </div>
                  
                  <div v-else class="space-y-4">
                    <BaseInput
                      v-model="editForm.name"
                      label="Nome"
                      placeholder="Seu nome completo"
                    />
                    <BaseInput
                      v-model="editForm.email"
                      label="Email"
                      type="email"
                      placeholder="seu@email.com"
                    />
                    <BaseInput
                      v-model="editForm.location"
                      label="Localização"
                      placeholder="Cidade, Estado"
                    />
                    <BaseInput
                      v-model="editForm.bio"
                      label="Bio"
                      type="textarea"
                      placeholder="Fale um pouco sobre você, suas experiências e interesses..."
                      rows="3"
                    />
                  </div>
                </div>
              </div>
            </div>
          </BaseCard>

          <!-- My Skills Section -->
            <BaseCard>
              <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                  <h2 class="text-xl font-bold text-secondary-900">Minhas Habilidades</h2>
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
                
                <div v-if="userSkills.length === 0" class="text-center py-12">
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
                    class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                  >
                    <div class="flex items-start justify-between mb-3">
                      <h3 class="font-semibold text-secondary-900">{{ skill.title }}</h3>
                      <div class="flex items-center space-x-2">
                        <BaseBadge 
                          :variant="getLevelVariant(skill.level)"
                          size="sm"
                        >
                          {{ getLevelText(skill.level) }}
                        </BaseBadge>
                        <button 
                          @click="deleteSkill(skill.id)"
                          class="text-danger-500 hover:text-danger-700 text-sm"
                        >
                          🗑️
                        </button>
                      </div>
                    </div>
                    <p class="text-sm text-secondary-600 mb-3">{{ skill.description }}</p>
                    <div class="flex items-center justify-between">
                      <span class="text-sm text-primary-600 font-medium">{{ skill.category?.name }}</span>
                      <div class="flex items-center text-sm text-secondary-500">
                        <span>👁️ {{ skill.views || 0 }} visualizações</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </BaseCard>
          </div>

        <!-- Right Column - Recent Activity & Matches -->
        <div class="space-y-8">
          <!-- Recent Exchanges -->
          <BaseCard>
            <div class="p-6">
              <h2 class="text-xl font-bold text-secondary-900 mb-6">Trocas Recentes</h2>
              
              <div v-if="recentExchanges.length === 0" class="text-center py-8">
                <div class="text-4xl mb-3">🤝</div>
                <p class="text-secondary-600">Nenhuma troca realizada ainda</p>
              </div>
              
              <div v-else class="space-y-4">
                <div 
                  v-for="exchange in recentExchanges" 
                  :key="exchange.id"
                  class="flex items-center space-x-4 p-3 border border-gray-200 rounded-lg"
                >
                  <div class="w-12 h-12 bg-gradient-to-br from-success-400 to-success-600 rounded-full flex items-center justify-center text-white font-bold">
                    {{ getInitials(exchange.partnerName) }}
                  </div>
                  <div class="flex-1">
                    <h4 class="font-medium text-secondary-900">{{ exchange.partnerName }}</h4>
                    <p class="text-sm text-secondary-600">{{ exchange.skillExchanged }}</p>
                    <p class="text-xs text-secondary-500">{{ formatDate(exchange.date) }}</p>
                  </div>
                  <BaseBadge 
                    :variant="getStatusVariant(exchange.status)"
                    size="sm"
                  >
                    {{ getStatusText(exchange.status) }}
                  </BaseBadge>
                </div>
              </div>
            </div>
          </BaseCard>

          <!-- Potential Matches -->
          <BaseCard>
            <div class="p-6">
              <h2 class="text-xl font-bold text-secondary-900 mb-6">Possíveis Matches</h2>
              
              <div v-if="potentialMatches.length === 0" class="text-center py-8">
                <div class="text-4xl mb-3">🔍</div>
                <p class="text-secondary-600">Nenhum match encontrado</p>
              </div>
              
              <div v-else class="space-y-4">
                <div 
                  v-for="match in potentialMatches" 
                  :key="match.id"
                  class="p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
                >
                  <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                      {{ getInitials(match.name) }}
                    </div>
                    <div>
                      <h4 class="font-medium text-secondary-900">{{ match.name }}</h4>
                      <div class="flex items-center space-x-1">
                        <span class="text-warning-500">⭐</span>
                        <span class="text-sm text-secondary-600">{{ match.rating }}</span>
                      </div>
                    </div>
                  </div>
                  <p class="text-sm text-secondary-600 mb-3">{{ match.skillTitle }}</p>
                  <div class="flex items-center justify-between">
                    <span class="text-xs text-secondary-500">{{ match.location }}</span>
                    <BaseButton 
                      variant="primary" 
                      size="sm"
                      @click="viewMatchProfile(match.id)"
                    >
                      Ver Perfil
                    </BaseButton>
                  </div>
                </div>
              </div>
            </div>
          </BaseCard>
        </div>
      </div>
    </div>

    <!-- Add Skill Modal -->
    <div v-if="showAddSkillModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-bold text-secondary-900">Nova Habilidade</h3>
          <button 
            @click="showAddSkillModal = false"
            class="text-secondary-500 hover:text-secondary-700"
          >
            ✕
          </button>
        </div>
        
        <form @submit.prevent="addSkill" class="space-y-4">
          <BaseInput
            v-model="newSkill.title"
            label="Título da Habilidade"
            placeholder="Ex: Desenvolvimento Laravel"
            required
          />
          
          <BaseInput
            v-model="newSkill.description"
            label="Descrição"
            type="textarea"
            placeholder="Descreva sua habilidade e o que você pode ensinar..."
            rows="3"
            required
          />
          
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2">Nível</label>
            <select 
              v-model="newSkill.level"
              class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              required
            >
              <option value="">Selecione o nível</option>
              <option value="beginner">Iniciante</option>
              <option value="intermediate">Intermediário</option>
              <option value="advanced">Avançado</option>
              <option value="expert">Especialista</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2">Categoria</label>
            <select 
              v-model="newSkill.category_id"
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
          </div>
          
          <div class="flex items-center justify-end space-x-3 pt-4">
            <BaseButton 
              variant="outline" 
              type="button"
              @click="showAddSkillModal = false"
            >
              Cancelar
            </BaseButton>
            <BaseButton 
              variant="primary" 
              type="submit"
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
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { authService, skillService, categoryService, exchangeService, statsService } from '@/services/api'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'
import BaseLoading from '@/components/ui/BaseLoading.vue'
import BaseInput from '@/components/ui/BaseInput.vue'

interface User {
  id: number
  name: string
  email: string
  bio?: string
  location?: string
  rating?: number | null
  total_exchanges?: number
}

interface Skill {
  id: number
  title: string
  description: string
  level: string
  category_id: number
  category?: {
    id: number
    name: string
  }
  views?: number
}

interface Category {
  id: number
  name: string
}

interface Exchange {
  id: number
  partnerName: string
  skillExchanged: string
  status: string
  date: string
}

interface Match {
  id: number
  name: string
  skillTitle: string
  rating: number
  location: string
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

// Estatísticas reais do usuário
const realUserStats = ref({
  skills: 0,
  exchanges: 0,
  connections: 0,
  rating: 0
})

// Estado de edição
const isEditMode = ref(false)
const editForm = ref({
  name: '',
  email: '',
  bio: '',
  location: ''
})

// Modal de adicionar habilidade
const showAddSkillModal = ref(false)
const addingSkill = ref(false)
const newSkill = ref({
  title: '',
  description: '',
  level: '',
  category_id: null as number | null
})

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
    const userResponse = await authService.getUser()
    user.value = userResponse.data.user

    if (!user.value) {
      throw new Error('Usuário não encontrado')
    }

    // Carregar habilidades do usuário
    try {
      const skillsResponse = await skillService.getMySkills()
      userSkills.value = skillsResponse.data.skills || []
    } catch (skillsError) {
      console.error('Erro ao carregar skills:', skillsError)
      userSkills.value = []
    }

    // Carregar estatísticas reais do usuário
    try {
      const userStatsResponse = await statsService.getUserStats()
      if (userStatsResponse.data.success) {
        const data = userStatsResponse.data.data
        realUserStats.value = {
          skills: Number(data.skills) || 0,
          exchanges: Number(data.exchanges) || 0,
          connections: Number(data.connections) || 0,
          rating: Number(data.rating) || 0
        }
      }
    } catch (statsError) {
      console.warn('Erro ao carregar estatísticas do usuário:', statsError)
      // Manter valores padrão se der erro
    }

    // Carregar categorias
    const categoriesResponse = await categoryService.getAll()
    categories.value = categoriesResponse.data.categories || []

    // Carregar trocas recentes (mock data)
    recentExchanges.value = [
      {
        id: 1,
        partnerName: 'Maria Silva',
        skillExchanged: 'Vue.js ↔ Design UX/UI',
        status: 'completed',
        date: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString()
      },
      {
        id: 2,
        partnerName: 'João Santos',
        skillExchanged: 'Laravel ↔ Marketing Digital',
        status: 'pending',
        date: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000).toISOString()
      }
    ]

    // Carregar matches potenciais (mock data)
    potentialMatches.value = [
      {
        id: 1,
        name: 'Ana Costa',
        skillTitle: 'React Advanced',
        rating: 4.8,
        location: 'São Paulo, SP'
      },
      {
        id: 2,
        name: 'Carlos Lima',
        skillTitle: 'Photoshop',
        rating: 4.5,
        location: 'Rio de Janeiro, RJ'
      }
    ]

    // Preencher formulário de edição
    if (user.value) {
      editForm.value = {
        name: user.value.name || '',
        email: user.value.email || '',
        bio: user.value.bio || '',
        location: user.value.location || ''
      }
    }

  } catch (err) {
    console.error('Erro ao carregar dashboard:', err)
    error.value = 'Erro ao carregar dados do dashboard'
  } finally {
    loading.value = false
  }
}

// Função para alternar modo de edição
const toggleEditMode = async () => {
  if (isEditMode.value) {
    // Salvar alterações
    try {
      await authService.updateProfile(editForm.value)
      if (user.value) {
        user.value = { ...user.value, ...editForm.value } as User
      }
      isEditMode.value = false
    } catch (err) {
      console.error('Erro ao salvar perfil:', err)
      error.value = 'Erro ao salvar alterações do perfil'
    }
  } else {
    isEditMode.value = true
  }
}

// Função para adicionar habilidade
const addSkill = async () => {
  try {
    addingSkill.value = true
    
    const skillData = {
      ...newSkill.value,
      is_available: true
    }
    
    const response = await skillService.create(skillData)
    userSkills.value.push(response.data.skill)
    
    // Reset form
    newSkill.value = {
      title: '',
      description: '',
      level: '',
      category_id: null
    }
    
    showAddSkillModal.value = false
  } catch (err) {
    console.error('Erro ao adicionar habilidade:', err)
    error.value = 'Erro ao adicionar habilidade'
  } finally {
    addingSkill.value = false
  }
}

// Função para deletar habilidade
const deleteSkill = async (skillId: number) => {
  if (!confirm('Tem certeza que deseja excluir esta habilidade?')) return
  
  try {
    await skillService.delete(skillId)
    userSkills.value = userSkills.value.filter(skill => skill.id !== skillId)
  } catch (err) {
    console.error('Erro ao deletar habilidade:', err)
    error.value = 'Erro ao deletar habilidade'
  }
}

// Função para visualizar perfil de um match
const viewMatchProfile = (matchId: number) => {
  router.push(`/users/${matchId}/profile`)
}

onMounted(() => {
  loadDashboard()
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