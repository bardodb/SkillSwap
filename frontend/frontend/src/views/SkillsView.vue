<template>
  <div class="skills-page">
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-primary-50 via-white to-secondary-50 pt-20 pb-16">
      <div class="container-custom">
        <div class="text-center max-w-4xl mx-auto">
          <h1 class="heading-xl mb-6 text-secondary-900">
            Explore <span class="text-gradient">Habilidades</span>
          </h1>
          <p class="text-xl text-secondary-600 mb-8 leading-relaxed">
            Descubra uma vasta gama de conhecimentos compartilhados por nossa comunidade. 
            Encontre exatamente o que você precisa aprender ou ensine o que você domina.
          </p>
          
          <!-- Search Bar -->
          <div class="max-w-2xl mx-auto mb-8">
            <div class="relative">
              <BaseInput
                v-model="searchQuery"
                placeholder="Buscar habilidades... (ex: JavaScript, Design, Inglês)"
                size="lg"
                class="pr-12"
              >
                <template #icon-right>
                  <svg class="w-6 h-6 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </template>
              </BaseInput>
            </div>
          </div>
          
          <!-- Quick Stats -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 max-w-2xl mx-auto">
            <div class="text-center">
              <div class="text-3xl font-bold text-primary-600 mb-1">{{ totalSkills }}</div>
              <div class="text-secondary-600">Habilidades</div>
            </div>
            <div class="text-center">
              <div class="text-3xl font-bold text-success-600 mb-1">{{ totalCategories }}</div>
              <div class="text-secondary-600">Categorias</div>
            </div>
            <div class="text-center">
              <div class="text-3xl font-bold text-warning-600 mb-1">{{ totalInstructors }}</div>
              <div class="text-secondary-600">Instrutores</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Category Filter -->
    <section class="py-8 bg-white border-b border-secondary-100">
      <div class="container-custom">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
          <h2 class="text-2xl font-bold text-secondary-900">Filtrar por Categoria</h2>
          <div class="flex items-center gap-2">
            <span class="text-sm text-secondary-600">Visualização:</span>
            <div class="flex bg-secondary-100 rounded-lg p-1">
              <button
                @click="viewMode = 'grid'"
                :class="[
                  'px-3 py-1 rounded text-sm font-medium transition-colors',
                  viewMode === 'grid' 
                    ? 'bg-white text-primary-600 shadow-sm' 
                    : 'text-secondary-600 hover:text-secondary-900'
                ]"
              >
                Grid
              </button>
              <button
                @click="viewMode = 'list'"
                :class="[
                  'px-3 py-1 rounded text-sm font-medium transition-colors',
                  viewMode === 'list' 
                    ? 'bg-white text-primary-600 shadow-sm' 
                    : 'text-secondary-600 hover:text-secondary-900'
                ]"
              >
                Lista
              </button>
            </div>
          </div>
        </div>
        
        <!-- Category Filters -->
        <div class="flex flex-wrap gap-3">
          <button
            @click="selectedCategory = null"
            :class="[
              'px-4 py-2 rounded-full text-sm font-medium transition-all duration-200',
              selectedCategory === null
                ? 'bg-primary-600 text-white shadow-medium'
                : 'bg-secondary-100 text-secondary-700 hover:bg-secondary-200'
            ]"
          >
            Todas as Categorias
          </button>
          <button
            v-for="category in categories"
            :key="category.id"
            @click="selectedCategory = category.id"
            :class="[
              'px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 flex items-center gap-2',
              selectedCategory === category.id
                ? 'text-white shadow-medium'
                : 'bg-secondary-100 text-secondary-700 hover:bg-secondary-200'
            ]"
            :style="selectedCategory === category.id ? { backgroundColor: category.color } : {}"
          >
            <span>{{ getCategoryIcon(category.name) }}</span>
            <span>{{ category.name }}</span>
            <BaseBadge 
              variant="secondary" 
              class="ml-1 text-xs"
              :class="selectedCategory === category.id ? 'bg-white/20 text-white' : ''"
            >
              {{ category.skills_count }}
            </BaseBadge>
          </button>
        </div>
      </div>
    </section>

    <!-- Skills Grid/List -->
    <section class="section-padding bg-secondary-50">
      <div class="container-custom">
        <!-- Loading State -->
        <div v-if="loading" class="py-20">
          <BaseLoading size="lg" message="Carregando habilidades..." />
        </div>
        
        <!-- Error State -->
        <div v-else-if="error" class="text-center py-20">
          <div class="text-danger-500 text-lg mb-4">{{ error }}</div>
          <BaseButton variant="outline" @click="loadSkills">
            Tentar Novamente
          </BaseButton>
        </div>
        
        <!-- No Results -->
        <div v-else-if="filteredSkills.length === 0" class="text-center py-20">
          <div class="text-6xl mb-4">🔍</div>
          <h3 class="text-2xl font-bold text-secondary-900 mb-2">Nenhuma habilidade encontrada</h3>
          <p class="text-secondary-600 mb-6">
            Tente ajustar seus filtros ou fazer uma busca diferente.
          </p>
          <BaseButton variant="primary" @click="clearFilters">
            Limpar Filtros
          </BaseButton>
        </div>
        
        <!-- Skills Grid View -->
        <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <BaseCard
            v-for="skill in filteredSkills"
            :key="skill.id"
            hover
            class="cursor-pointer group transition-all duration-300"
            @click="goToSkill(skill.id)"
          >
            <div class="p-6">
              <!-- Skill Header -->
              <div class="flex items-start justify-between mb-4">
                <div 
                  class="w-12 h-12 rounded-xl flex items-center justify-center text-xl transition-transform group-hover:scale-110"
                  :style="{ backgroundColor: skill.category_color + '20', color: skill.category_color }"
                >
                  {{ getCategoryIcon(skill.category_name) }}
                </div>
                <BaseBadge :variant="getLevelVariant(skill.level)">
                  {{ skill.level }}
                </BaseBadge>
              </div>
              
              <!-- Skill Info -->
              <h3 class="text-lg font-bold text-secondary-900 mb-2 group-hover:text-primary-600 transition-colors">
                {{ skill.name }}
              </h3>
              <p class="text-secondary-600 text-sm mb-4 line-clamp-2">
                {{ skill.description }}
              </p>
              
              <!-- Instructor Info -->
              <div class="flex items-center justify-between pt-4 border-t border-secondary-100">
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center">
                    <span class="text-white text-sm font-semibold">
                      {{ skill.instructor_name.charAt(0).toUpperCase() }}
                    </span>
                  </div>
                  <span class="text-sm text-secondary-600">{{ skill.instructor_name }}</span>
                </div>
                <div class="flex items-center gap-1 text-sm text-secondary-500">
                  <span>⭐</span>
                  <span>{{ skill.rating }}</span>
                </div>
              </div>
            </div>
          </BaseCard>
        </div>
        
        <!-- Skills List View -->
        <div v-else class="space-y-4">
          <BaseCard
            v-for="skill in filteredSkills"
            :key="skill.id"
            hover
            class="cursor-pointer group transition-all duration-300"
            @click="goToSkill(skill.id)"
          >
            <div class="p-6">
              <div class="flex items-center gap-6">
                <!-- Icon -->
                <div 
                  class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl transition-transform group-hover:scale-110 flex-shrink-0"
                  :style="{ backgroundColor: skill.category_color + '20', color: skill.category_color }"
                >
                  {{ getCategoryIcon(skill.category_name) }}
                </div>
                
                <!-- Main Info -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-start justify-between mb-2">
                    <h3 class="text-xl font-bold text-secondary-900 group-hover:text-primary-600 transition-colors">
                      {{ skill.name }}
                    </h3>
                    <BaseBadge :variant="getLevelVariant(skill.level)">
                      {{ skill.level }}
                    </BaseBadge>
                  </div>
                  <p class="text-secondary-600 mb-3 line-clamp-1">
                    {{ skill.description }}
                  </p>
                  <div class="flex items-center gap-4 text-sm text-secondary-500">
                    <span class="flex items-center gap-1">
                      <span>👤</span>
                      {{ skill.instructor_name }}
                    </span>
                    <span class="flex items-center gap-1">
                      <span>📂</span>
                      {{ skill.category_name }}
                    </span>
                    <span class="flex items-center gap-1">
                      <span>⭐</span>
                      {{ skill.rating }}
                    </span>
                  </div>
                </div>
                
                <!-- Action -->
                <div class="flex-shrink-0">
                  <BaseButton variant="primary" size="sm">
                    Ver Detalhes
                  </BaseButton>
                </div>
              </div>
            </div>
          </BaseCard>
        </div>
        
        <!-- Load More Button -->
        <div v-if="filteredSkills.length > 0 && hasMore" class="text-center mt-12">
          <BaseButton 
            variant="outline" 
            size="lg"
            @click="loadMore"
            :loading="loadingMore"
          >
            Carregar Mais Habilidades
          </BaseButton>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'
import BaseLoading from '@/components/ui/BaseLoading.vue'
import BaseInput from '@/components/ui/BaseInput.vue'

interface Category {
  id: number
  name: string
  description: string
  color: string
  skills_count: number
}

interface Skill {
  id: number
  name: string
  description: string
  level: 'Iniciante' | 'Intermediário' | 'Avançado'
  category_name: string
  category_color: string
  instructor_name: string
  rating: number
  category_id: number
}

const router = useRouter()

// State
const loading = ref(false)
const loadingMore = ref(false)
const error = ref<string | null>(null)
const categories = ref<Category[]>([])
const skills = ref<Skill[]>([])
const searchQuery = ref('')
const selectedCategory = ref<number | null>(null)
const viewMode = ref<'grid' | 'list'>('grid')
const hasMore = ref(true)

// Computed
const totalSkills = computed(() => skills.value.length)
const totalCategories = computed(() => categories.value.length)
const totalInstructors = computed(() => {
  const instructors = new Set(skills.value.map(skill => skill.instructor_name))
  return instructors.size
})

const filteredSkills = computed(() => {
  let filtered = skills.value
  
  // Filter by category
  if (selectedCategory.value) {
    filtered = filtered.filter(skill => skill.category_id === selectedCategory.value)
  }
  
  // Filter by search query
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase().trim()
    filtered = filtered.filter(skill => 
      skill.name.toLowerCase().includes(query) ||
      skill.description.toLowerCase().includes(query) ||
      skill.category_name.toLowerCase().includes(query) ||
      skill.instructor_name.toLowerCase().includes(query)
    )
  }
  
  return filtered
})

// Methods
const loadCategories = async () => {
  try {
    // Mock data
    categories.value = [
      { id: 1, name: 'Programação', description: 'Desenvolvimento web, mobile e desktop', color: '#3b82f6', skills_count: 25 },
      { id: 2, name: 'Design', description: 'UI/UX, Design Gráfico e Ilustração', color: '#f59e0b', skills_count: 18 },
      { id: 3, name: 'Marketing', description: 'Marketing Digital, SEO e Redes Sociais', color: '#ef4444', skills_count: 15 },
      { id: 4, name: 'Idiomas', description: 'Inglês, Espanhol, Francês e outros', color: '#22c55e', skills_count: 12 },
      { id: 5, name: 'Música', description: 'Instrumentos, Teoria Musical e Produção', color: '#8b5cf6', skills_count: 10 },
      { id: 6, name: 'Culinária', description: 'Receitas, Técnicas e Gastronomia', color: '#f97316', skills_count: 8 },
      { id: 7, name: 'Negócios', description: 'Empreendedorismo, Finanças e Gestão', color: '#06b6d4', skills_count: 14 },
      { id: 8, name: 'Esportes', description: 'Fitness, Yoga, Artes Marciais', color: '#84cc16', skills_count: 9 }
    ]
  } catch (err) {
    console.error('Erro ao carregar categorias:', err)
  }
}

const loadSkills = async () => {
  loading.value = true
  error.value = null
  
  try {
    // Mock data
    skills.value = [
      // Programação
      { id: 1, name: 'JavaScript Básico', description: 'Aprenda os fundamentos do JavaScript moderno', level: 'Iniciante', category_name: 'Programação', category_color: '#3b82f6', instructor_name: 'Ana Silva', rating: 4.8, category_id: 1 },
      { id: 2, name: 'React Avançado', description: 'Desenvolva aplicações complexas com React', level: 'Avançado', category_name: 'Programação', category_color: '#3b82f6', instructor_name: 'Carlos Santos', rating: 4.9, category_id: 1 },
      { id: 3, name: 'Python para Dados', description: 'Análise de dados com Python e Pandas', level: 'Intermediário', category_name: 'Programação', category_color: '#3b82f6', instructor_name: 'Maria Oliveira', rating: 4.7, category_id: 1 },
      
      // Design
      { id: 4, name: 'UI/UX Design', description: 'Princípios de design de interface e experiência', level: 'Intermediário', category_name: 'Design', category_color: '#f59e0b', instructor_name: 'João Costa', rating: 4.6, category_id: 2 },
      { id: 5, name: 'Figma Completo', description: 'Domine a ferramenta Figma do básico ao avançado', level: 'Iniciante', category_name: 'Design', category_color: '#f59e0b', instructor_name: 'Laura Pereira', rating: 4.8, category_id: 2 },
      
      // Marketing
      { id: 6, name: 'Marketing Digital', description: 'Estratégias de marketing para o mundo digital', level: 'Iniciante', category_name: 'Marketing', category_color: '#ef4444', instructor_name: 'Pedro Lima', rating: 4.5, category_id: 3 },
      { id: 7, name: 'SEO Avançado', description: 'Otimização para mecanismos de busca', level: 'Avançado', category_name: 'Marketing', category_color: '#ef4444', instructor_name: 'Sofia Rodrigues', rating: 4.9, category_id: 3 },
      
      // Idiomas
      { id: 8, name: 'Inglês Conversação', description: 'Pratique conversação em inglês do dia a dia', level: 'Intermediário', category_name: 'Idiomas', category_color: '#22c55e', instructor_name: 'Robert Johnson', rating: 4.7, category_id: 4 },
      { id: 9, name: 'Espanhol Básico', description: 'Aprenda espanhol desde o zero', level: 'Iniciante', category_name: 'Idiomas', category_color: '#22c55e', instructor_name: 'Carmen García', rating: 4.6, category_id: 4 },
      
      // Música
      { id: 10, name: 'Violão Fingerstyle', description: 'Técnicas avançadas de violão fingerstyle', level: 'Avançado', category_name: 'Música', category_color: '#8b5cf6', instructor_name: 'Lucas Mendes', rating: 4.8, category_id: 5 },
      { id: 11, name: 'Piano Clássico', description: 'Fundamentos do piano clássico', level: 'Iniciante', category_name: 'Música', category_color: '#8b5cf6', instructor_name: 'Helena Bach', rating: 4.9, category_id: 5 },
      
      // Culinária
      { id: 12, name: 'Culinária Italiana', description: 'Pratos tradicionais da culinária italiana', level: 'Intermediário', category_name: 'Culinária', category_color: '#f97316', instructor_name: 'Giuseppe Cadura', rating: 4.7, category_id: 6 },
      
      // Negócios
      { id: 13, name: 'Gestão de Projetos', description: 'Metodologias ágeis e gestão eficiente', level: 'Intermediário', category_name: 'Negócios', category_color: '#06b6d4', instructor_name: 'Rafael Ferreira', rating: 4.6, category_id: 7 },
      
      // Esportes
      { id: 14, name: 'Yoga para Iniciantes', description: 'Posturas básicas e respiração no yoga', level: 'Iniciante', category_name: 'Esportes', category_color: '#84cc16', instructor_name: 'Namastê Zen', rating: 4.8, category_id: 8 }
    ]
  } catch (err) {
    error.value = 'Erro ao carregar habilidades'
    console.error('Erro ao carregar habilidades:', err)
  } finally {
    loading.value = false
  }
}

const getCategoryIcon = (categoryName: string): string => {
  const icons: Record<string, string> = {
    'Programação': '💻',
    'Design': '🎨',
    'Marketing': '📈',
    'Idiomas': '🌍',
    'Música': '🎵',
    'Culinária': '🍳',
    'Negócios': '💼',
    'Esportes': '⚽'
  }
  return icons[categoryName] || '📚'
}

const getLevelVariant = (level: string) => {
  switch (level) {
    case 'Iniciante': return 'success'
    case 'Intermediário': return 'warning'
    case 'Avançado': return 'danger'
    default: return 'primary'
  }
}

const goToSkill = (skillId: number) => {
  // router.push(`/skills/${skillId}`)
  console.log('Ir para skill:', skillId)
}

const clearFilters = () => {
  searchQuery.value = ''
  selectedCategory.value = null
}

const loadMore = () => {
  loadingMore.value = true
  // Simulate loading more
  setTimeout(() => {
    loadingMore.value = false
    hasMore.value = false
  }, 1000)
}

// Lifecycle
onMounted(() => {
  loadCategories()
  loadSkills()
})

// Watch for changes in filters
watch([searchQuery, selectedCategory], () => {
  // Reset pagination when filters change
  hasMore.value = true
})
</script>

<style scoped>
.line-clamp-1 {
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
}

.line-clamp-2 {
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}
</style> 