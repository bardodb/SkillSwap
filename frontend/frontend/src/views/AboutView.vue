<template>
  <div class="about-page">
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-primary-50 via-white to-secondary-50 pt-20 pb-16">
      <div class="container-custom">
        <div class="text-center max-w-4xl mx-auto">
          <h1 class="heading-xl mb-6 text-secondary-900">
            Sobre o <span class="text-gradient">SkillSwap</span>
          </h1>
          <p class="text-xl text-secondary-600 mb-8 leading-relaxed">
            Uma plataforma que conecta pessoas através do conhecimento, 
            promovendo a troca de habilidades e o crescimento mútuo da comunidade.
          </p>
        </div>
      </div>
    </section>

    <!-- Mission Section -->
    <section class="section-padding bg-white">
      <div class="container-custom">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div class="space-y-6">
            <h2 class="heading-lg text-secondary-900">Nossa Missão</h2>
            <p class="text-lg text-secondary-600 leading-relaxed">
              Acreditamos que todos têm algo valioso para ensinar e sempre há algo novo para aprender. 
              O SkillSwap foi criado para quebrar as barreiras do aprendizado tradicional e democratizar 
              o acesso ao conhecimento.
            </p>
            <p class="text-lg text-secondary-600 leading-relaxed">
              Nossa missão é conectar pessoas com diferentes habilidades, criando uma comunidade 
              colaborativa onde o conhecimento flui livremente e todos podem crescer juntos.
            </p>
          </div>
          <div class="bg-gradient-to-br from-primary-50 to-primary-100 rounded-3xl p-8 text-center">
            <div class="text-6xl mb-4">🎯</div>
            <h3 class="text-2xl font-bold mb-4 text-secondary-900">Democratizar o Conhecimento</h3>
            <p class="text-secondary-600">
              Tornar o aprendizado acessível, colaborativo e enriquecedor para todos.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Values Section -->
    <section class="section-padding bg-secondary-50">
      <div class="container-custom">
        <div class="text-center mb-16">
          <h2 class="heading-lg mb-6 text-secondary-900">Nossos Valores</h2>
          <p class="text-xl text-secondary-600 max-w-2xl mx-auto">
            Os princípios que guiam nossa comunidade e moldam nossa plataforma.
          </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <BaseCard hover class="text-center">
            <div class="p-8">
              <div class="w-16 h-16 bg-primary-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <span class="text-primary-600 text-2xl">🤝</span>
              </div>
              <h3 class="text-xl font-bold text-secondary-900 mb-3">Colaboração</h3>
              <p class="text-secondary-600">
                Acreditamos que juntos chegamos mais longe. O conhecimento cresce quando é compartilhado.
              </p>
            </div>
          </BaseCard>
          
          <BaseCard hover class="text-center">
            <div class="p-8">
              <div class="w-16 h-16 bg-success-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <span class="text-success-600 text-2xl">💡</span>
              </div>
              <h3 class="text-xl font-bold text-secondary-900 mb-3">Inovação</h3>
              <p class="text-secondary-600">
                Buscamos constantemente novas formas de facilitar o aprendizado e a conexão entre pessoas.
              </p>
            </div>
          </BaseCard>
          
          <BaseCard hover class="text-center">
            <div class="p-8">
              <div class="w-16 h-16 bg-warning-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <span class="text-warning-600 text-2xl">🌟</span>
              </div>
              <h3 class="text-xl font-bold text-secondary-900 mb-3">Excelência</h3>
              <p class="text-secondary-600">
                Comprometemo-nos com a qualidade em cada interação e experiência na plataforma.
              </p>
            </div>
          </BaseCard>
          
          <BaseCard hover class="text-center">
            <div class="p-8">
              <div class="w-16 h-16 bg-danger-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <span class="text-danger-600 text-2xl">❤️</span>
              </div>
              <h3 class="text-xl font-bold text-secondary-900 mb-3">Inclusão</h3>
              <p class="text-secondary-600">
                Todos são bem-vindos, independentemente do nível de conhecimento ou experiência.
              </p>
            </div>
          </BaseCard>
          
          <BaseCard hover class="text-center">
            <div class="p-8">
              <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <span class="text-purple-600 text-2xl">🔒</span>
              </div>
              <h3 class="text-xl font-bold text-secondary-900 mb-3">Confiança</h3>
              <p class="text-secondary-600">
                Criamos um ambiente seguro onde todos podem aprender e ensinar com confiança.
              </p>
            </div>
          </BaseCard>
          
          <BaseCard hover class="text-center">
            <div class="p-8">
              <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <span class="text-indigo-600 text-2xl">🚀</span>
              </div>
              <h3 class="text-xl font-bold text-secondary-900 mb-3">Crescimento</h3>
              <p class="text-secondary-600">
                Valorizamos o desenvolvimento pessoal e profissional contínuo de nossa comunidade.
              </p>
            </div>
          </BaseCard>
        </div>
      </div>
    </section>

    <!-- Creator Section -->
    <section class="section-padding bg-white">
      <div class="container-custom">
        <div class="text-center mb-16">
          <h2 class="heading-lg mb-6 text-secondary-900">Criador do Projeto</h2>
          <p class="text-xl text-secondary-600 max-w-2xl mx-auto">
            Conheça a pessoa por trás da visão do SkillSwap.
          </p>
        </div>
        
        <div class="max-w-md mx-auto">
          <div class="text-center group">
            <div class="w-40 h-40 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:scale-105 transition-transform shadow-large">
              <span class="text-white text-4xl font-bold">BS</span>
            </div>
            <h3 class="text-2xl font-bold text-secondary-900 mb-2">Braian Sins</h3>
            <p class="text-primary-600 font-semibold text-lg mb-4">Criador & Desenvolvedor</p>
            <p class="text-secondary-600 leading-relaxed max-w-sm mx-auto">
              Desenvolvedor apaixonado por tecnologia e educação, criou o SkillSwap com a visão de 
              democratizar o acesso ao conhecimento através da troca de habilidades entre pessoas.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="section-padding bg-gradient-to-r from-primary-600 to-primary-800 text-white">
      <div class="container-custom">
        <div class="text-center mb-12">
          <h2 class="heading-lg mb-6">SkillSwap em Números</h2>
          <p class="text-xl text-primary-100 max-w-2xl mx-auto">
            O impacto da nossa comunidade em dados que nos orgulham.
          </p>
        </div>
        
        <div v-if="loadingStats" class="flex justify-center items-center py-16">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white"></div>
        </div>
        
        <div v-else-if="statsError" class="text-center py-16">
          <p class="text-lg text-primary-200 mb-4">Erro ao carregar estatísticas</p>
          <BaseButton 
            @click="loadStats" 
            variant="outline"
            size="md"
            class="border-white text-white hover:bg-white/10"
          >
            Tentar Novamente
          </BaseButton>
        </div>
        
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
          <div class="text-center">
            <div class="text-4xl font-bold mb-2">{{ stats.users }}+</div>
            <div class="text-primary-200">Usuários Ativos</div>
          </div>
          <div class="text-center">
            <div class="text-4xl font-bold mb-2">{{ stats.skills }}+</div>
            <div class="text-primary-200">Habilidades</div>
          </div>
          <div class="text-center">
            <div class="text-4xl font-bold mb-2">{{ stats.exchanges }}+</div>
            <div class="text-primary-200">Trocas Realizadas</div>
          </div>
          <div class="text-center">
            <div class="text-4xl font-bold mb-2">{{ stats.categories }}+</div>
            <div class="text-primary-200">Categorias</div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="section-padding bg-secondary-50">
      <div class="container-custom text-center">
        <div class="max-w-3xl mx-auto">
          <h2 class="heading-lg mb-6 text-secondary-900">Faça Parte da Nossa História</h2>
          <p class="text-xl text-secondary-600 mb-8 leading-relaxed">
            Junte-se a milhares de pessoas que já descobriram o poder da troca de conhecimentos. 
            Seu próximo aprendizado ou sua próxima oportunidade de ensinar está a um clique de distância.
          </p>
          
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <BaseButton 
              variant="primary" 
              size="lg"
              tag="router-link"
              to="/register"
            >
              Começar Agora
            </BaseButton>
            <BaseButton 
              variant="outline" 
              size="lg"
              tag="router-link"
              to="/skills"
            >
              Explorar Habilidades
            </BaseButton>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import { statsService } from '@/services/api'

// Interface para tipagem das estatísticas
interface Stats {
  users: number
  skills: number
  exchanges: number
  categories: number
}

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
    
    if (response.data.success) {
      stats.value = response.data.data
    } else {
      throw new Error('Erro ao carregar estatísticas')
    }
  } catch (error) {
    console.error('Erro ao carregar estatísticas:', error)
    statsError.value = true
  } finally {
    loadingStats.value = false
  }
}

// Carregar estatísticas ao montar o componente
onMounted(() => {
  loadStats()
})
</script>

<style>
@media (min-width: 1024px) {
  .about {
    min-height: 100vh;
    display: flex;
    align-items: center;
  }
}
</style>
