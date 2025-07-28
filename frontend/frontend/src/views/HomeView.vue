<template>
  <div class="home">
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 text-white">
      <!-- Background Pattern -->
      <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 left-0 w-full h-full" style="background-image: radial-gradient(circle at 25% 25%, white 2px, transparent 2px); background-size: 60px 60px;"></div>
      </div>
      
      <div class="relative container-custom section-padding">
        <div class="text-center max-w-4xl mx-auto">
          <h1 class="heading-xl mb-6 animate-on-scroll">
            Troque <span class="text-primary-200">Conhecimentos</span>,<br>
            Transforme <span class="text-primary-200">Vidas</span>
          </h1>
          <p class="text-xl md:text-2xl mb-8 text-primary-100 leading-relaxed animate-on-scroll">
            Conecte-se com pessoas que compartilham seus interesses e descubra novas habilidades através da nossa plataforma de troca de conhecimentos.
          </p>
          
          <div class="flex flex-col sm:flex-row gap-4 justify-center animate-on-scroll">
            <BaseButton 
              variant="secondary" 
              size="lg"
              tag="router-link"
              to="/skills"
              class="bg-white text-primary-700 hover:bg-primary-50 shadow-large hover:shadow-xl transform hover:scale-105"
            >
              <template #icon-left>
                <span class="text-xl">🚀</span>
              </template>
              Explorar Habilidades
            </BaseButton>
            
            <BaseButton 
              variant="outline" 
              size="lg"
              tag="router-link"
              to="/register"
              class="border-white text-white hover:bg-white hover:text-primary-700"
            >
              <template #icon-left>
                <span class="text-xl">✨</span>
              </template>
              Começar Agora
            </BaseButton>
          </div>
          
          <!-- Stats -->
          <div v-if="loadingStats" class="flex justify-center items-center mt-16">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
          </div>
          
          <div v-else-if="statsError" class="text-center mt-16">
            <p class="text-primary-200 mb-4">Erro ao carregar estatísticas</p>
            <BaseButton 
              @click="loadStats" 
              variant="outline"
              size="sm"
              class="border-white text-white hover:bg-white/10"
            >
              Tentar Novamente
            </BaseButton>
          </div>
          
          <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-16 animate-on-scroll">
            <div class="text-center">
              <div class="text-3xl font-bold mb-2">{{ stats.skills }}+</div>
              <div class="text-primary-200">Habilidades Disponíveis</div>
            </div>
            <div class="text-center">
              <div class="text-3xl font-bold mb-2">{{ stats.users }}+</div>
              <div class="text-primary-200">Usuários Ativos</div>
            </div>
            <div class="text-center">
              <div class="text-3xl font-bold mb-2">{{ stats.exchanges }}+</div>
              <div class="text-primary-200">Trocas Realizadas</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Categories Section -->
    <section class="section-padding bg-white">
      <div class="container-custom">
        <div class="text-center mb-16">
          <h2 class="heading-lg mb-6 text-secondary-900">Categorias Populares</h2>
          <p class="text-xl text-secondary-600 max-w-2xl mx-auto">
            Descubra habilidades organizadas por categoria e encontre exatamente o que você procura
          </p>
        </div>
        
        <div v-if="loading" class="py-20">
          <BaseLoading size="lg" message="Carregando categorias..." />
        </div>
        
        <div v-else-if="error" class="text-center py-20">
          <div class="text-danger-500 text-lg mb-4">{{ error }}</div>
          <BaseButton variant="outline" @click="loadCategories">
            Tentar Novamente
          </BaseButton>
        </div>
        
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 lg:gap-8">
          <BaseCard 
            v-for="category in categories" 
            :key="category.id"
            hover
            class="cursor-pointer group animate-on-scroll transition-all duration-300 hover:shadow-large"
            @click="goToCategory(category.id)"
          >
            <div class="text-center p-2">
              <div 
                class="w-20 h-20 rounded-3xl mb-6 mx-auto flex items-center justify-center text-3xl transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 shadow-medium" 
                :style="{ backgroundColor: category.color + '15', color: category.color, boxShadow: `0 8px 25px ${category.color}20` }"
              >
                {{ getCategoryIcon(category.name) }}
              </div>
              <h3 class="text-xl font-bold mb-3 text-secondary-900 group-hover:text-primary-600 transition-colors">
                {{ category.name }}
              </h3>
              <p class="text-secondary-600 text-sm mb-4 leading-relaxed line-clamp-2">
                {{ category.description }}
              </p>
              <div class="flex items-center justify-center">
                <BaseBadge 
                  variant="primary" 
                  class="text-xs font-semibold px-3 py-1.5 bg-gradient-to-r from-primary-500 to-primary-600 text-white"
                >
                  {{ category.skills_count }} {{ category.skills_count === 1 ? 'habilidade' : 'habilidades' }}
                </BaseBadge>
              </div>
            </div>
          </BaseCard>
        </div>
        
        <!-- View All Categories Button -->
        <div class="text-center mt-12">
          <BaseButton 
            variant="outline" 
            size="lg"
            tag="router-link"
            to="/skills"
            class="group"
          >
            <span>Ver Todas as Categorias</span>
            <span class="ml-2 transition-transform group-hover:translate-x-1">→</span>
          </BaseButton>
        </div>
      </div>
    </section>

    <!-- How it Works Section -->
    <section class="section-padding bg-secondary-50">
      <div class="container-custom">
        <div class="text-center mb-16">
          <h2 class="heading-lg mb-6 text-secondary-900">Como Funciona</h2>
          <p class="text-xl text-secondary-600 max-w-2xl mx-auto">
            Em apenas 3 passos simples, você pode começar a trocar conhecimentos
          </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">
          <div class="text-center animate-on-scroll">
            <div class="relative mb-8">
              <div class="w-20 h-20 bg-gradient-to-br from-primary-500 to-primary-600 rounded-3xl flex items-center justify-center mx-auto shadow-large">
                <span class="text-white text-2xl font-bold">1</span>
              </div>
              <div class="hidden md:block absolute top-10 left-full w-full h-0.5 bg-gradient-to-r from-primary-300 to-transparent"></div>
            </div>
            <h3 class="heading-sm mb-4 text-secondary-900">Cadastre suas Habilidades</h3>
            <p class="text-secondary-600 leading-relaxed">
              Registre as competências que você domina e quer compartilhar com outros. Seja específico sobre seu nível de conhecimento.
            </p>
          </div>
          
          <div class="text-center animate-on-scroll">
            <div class="relative mb-8">
              <div class="w-20 h-20 bg-gradient-to-br from-success-500 to-success-600 rounded-3xl flex items-center justify-center mx-auto shadow-large">
                <span class="text-white text-2xl font-bold">2</span>
              </div>
              <div class="hidden md:block absolute top-10 left-full w-full h-0.5 bg-gradient-to-r from-success-300 to-transparent"></div>
            </div>
            <h3 class="heading-sm mb-4 text-secondary-900">Conecte-se com Pessoas</h3>
            <p class="text-secondary-600 leading-relaxed">
              Encontre pessoas que querem aprender o que você sabe ou que podem ensinar o que você deseja aprender.
            </p>
          </div>
          
          <div class="text-center animate-on-scroll">
            <div class="relative mb-8">
              <div class="w-20 h-20 bg-gradient-to-br from-warning-500 to-warning-600 rounded-3xl flex items-center justify-center mx-auto shadow-large">
                <span class="text-white text-2xl font-bold">3</span>
              </div>
            </div>
            <h3 class="heading-sm mb-4 text-secondary-900">Realize a Troca</h3>
            <p class="text-secondary-600 leading-relaxed">
              Agende sessões de aprendizado mútuo e cresça junto com a comunidade. O conhecimento se multiplica quando compartilhado.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="section-padding bg-white">
      <div class="container-custom">
        <div class="text-center mb-16">
          <h2 class="heading-lg mb-6 text-secondary-900">Por que escolher o SkillSwap?</h2>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div class="space-y-8">
            <div class="flex items-start space-x-4 animate-on-scroll">
              <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="text-primary-600 text-xl">🎯</span>
              </div>
              <div>
                <h3 class="text-xl font-semibold mb-2 text-secondary-900">Aprendizado Personalizado</h3>
                <p class="text-secondary-600">Encontre exatamente o que precisa aprender com pessoas reais que dominam o assunto.</p>
              </div>
            </div>
            
            <div class="flex items-start space-x-4 animate-on-scroll">
              <div class="w-12 h-12 bg-success-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="text-success-600 text-xl">🤝</span>
              </div>
              <div>
                <h3 class="text-xl font-semibold mb-2 text-secondary-900">Comunidade Colaborativa</h3>
                <p class="text-secondary-600">Faça parte de uma rede de pessoas dispostas a compartilhar e aprender juntas.</p>
              </div>
            </div>
            
            <div class="flex items-start space-x-4 animate-on-scroll">
              <div class="w-12 h-12 bg-warning-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="text-warning-600 text-xl">⚡</span>
              </div>
              <div>
                <h3 class="text-xl font-semibold mb-2 text-secondary-900">Flexibilidade Total</h3>
                <p class="text-secondary-600">Aprenda no seu ritmo, quando e onde for melhor para você.</p>
              </div>
            </div>
          </div>
          
          <div class="animate-on-scroll">
            <div class="bg-gradient-to-br from-primary-50 to-primary-100 rounded-3xl p-8 text-center">
              <div class="text-6xl mb-4">🚀</div>
              <h3 class="text-2xl font-bold mb-4 text-secondary-900">Pronto para começar?</h3>
              <p class="text-secondary-600 mb-6">Junte-se a milhares de pessoas que já estão trocando conhecimentos</p>
              <BaseButton 
                variant="primary" 
                size="lg"
                tag="router-link"
                to="/register"
                full-width
              >
                Criar Conta Gratuita
              </BaseButton>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="section-padding bg-gradient-to-r from-primary-600 to-primary-800 text-white">
      <div class="container-custom text-center">
        <div class="max-w-3xl mx-auto">
          <h2 class="heading-lg mb-6">Transforme seu Conhecimento em Conexões</h2>
          <p class="text-xl mb-8 text-primary-100 leading-relaxed">
            Não deixe suas habilidades paradas. Compartilhe o que você sabe e aprenda algo novo hoje mesmo.
          </p>
          
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <BaseButton 
              variant="secondary" 
              size="lg"
              tag="router-link"
              to="/register"
              class="bg-white text-primary-700 hover:bg-primary-50"
            >
              Criar Conta Gratuita
            </BaseButton>
            <BaseButton 
              variant="outline" 
              size="lg"
              tag="router-link"
              to="/login"
              class="border-white text-white hover:bg-white hover:text-primary-700"
            >
              Já tem conta? Entrar
            </BaseButton>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { categoryService, statsService } from '@/services/api'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'
import BaseLoading from '@/components/ui/BaseLoading.vue'

interface Category {
  id: number
  name: string
  description: string
  color: string
  skills_count: number
}

interface Stats {
  users: number
  skills: number
  exchanges: number
  categories: number
}

const router = useRouter()

const categories = ref<Category[]>([])
const loading = ref(false)
const error = ref<string | null>(null)

// Estado das estatísticas
const stats = ref<Stats>({
  users: 0,
  skills: 0,
  exchanges: 0,
  categories: 0
})

const loadingStats = ref(false)
const statsError = ref(false)

// Função para carregar as estatísticas
const loadStats = async () => {
  try {
    loadingStats.value = true
    statsError.value = false
    
    const response = await statsService.getStats()
    
    if (response.data.success && response.data.data) {
      stats.value = response.data.data
    } else {
      throw new Error('Dados de estatísticas inválidos')
    }
  } catch (error) {
    console.error('Erro ao carregar estatísticas:', error)
    statsError.value = true
  } finally {
    loadingStats.value = false
  }
}

const loadCategories = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await categoryService.getAll()
    if (response.data && response.data.success) {
      categories.value = response.data.data || []
    } else {
      console.warn('Resposta inválida ao carregar categorias')
      categories.value = []
    }
  } catch (err) {
    error.value = 'Erro ao carregar categorias'
    console.error('Erro ao carregar categorias:', err)
    categories.value = []
  } finally {
    loading.value = false
  }
}

const goToCategory = (categoryId: number) => {
  router.push(`/skills?category=${categoryId}`)
}

const getCategoryIcon = (categoryName: string): string => {
  const iconMap: Record<string, string> = {
    'Programação': '💻',
    'Design': '🎨',
    'Idiomas': '🌍',
    'Música': '🎵',
    'Culinária': '👨‍🍳',
    'Esportes': '⚽',
    'Artesanato': '🛠️',
    'Negócios': '💼',
    'Fotografia': '📸',
    'Educação': '📚'
  }
  return iconMap[categoryName] || '📚'
}

// Intersection Observer for scroll animations
const observeElements = () => {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('in-view')
      }
    })
  }, {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
  })

  document.querySelectorAll('.animate-on-scroll').forEach((el) => {
    observer.observe(el)
  })
}

onMounted(() => {
  loadCategories()
  loadStats()
  setTimeout(observeElements, 100) // Small delay to ensure DOM is ready
})
</script>

<style scoped>
/* Custom animations for this component */
.animate-on-scroll {
  transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.animate-on-scroll:nth-child(1) { transition-delay: 0.1s; }
.animate-on-scroll:nth-child(2) { transition-delay: 0.2s; }
.animate-on-scroll:nth-child(3) { transition-delay: 0.3s; }
.animate-on-scroll:nth-child(4) { transition-delay: 0.4s; }
.animate-on-scroll:nth-child(5) { transition-delay: 0.5s; }
</style>

