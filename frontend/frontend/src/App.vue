<script setup lang="ts">
import { RouterLink, RouterView } from 'vue-router'
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BaseButton from '@/components/ui/BaseButton.vue'

const router = useRouter()
const authStore = useAuthStore()
const mobileMenuOpen = ref(false)

const handleLogout = async () => {
  await authStore.logout()
  router.push('/')
}

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

onMounted(async () => {
  // Inicializar autenticação se houver token
  await authStore.initializeAuth()
})
</script>

<template>
  <div id="app" class="min-h-screen bg-secondary-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-soft border-b border-secondary-100 sticky top-0 z-50 backdrop-blur-sm bg-white/95">
      <div class="container-custom">
        <div class="flex justify-between items-center h-16">
          <!-- Logo -->
          <router-link 
            to="/" 
            class="flex items-center space-x-2 text-2xl font-bold font-heading text-gradient transition-smooth hover:scale-105"
          >
            <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center">
              <span class="text-white text-sm font-bold">S</span>
            </div>
            <span>SkillSwap</span>
          </router-link>
          
          <!-- Desktop Navigation -->
          <div class="hidden md:flex items-center space-x-8">
            <router-link 
              to="/" 
              class="text-secondary-600 hover:text-primary-600 font-medium transition-smooth relative group"
            >
              Home
              <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-primary-600 transition-all duration-300 group-hover:w-full"></span>
            </router-link>
            <router-link 
              to="/skills" 
              class="text-secondary-600 hover:text-primary-600 font-medium transition-smooth relative group"
            >
              Habilidades
              <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-primary-600 transition-all duration-300 group-hover:w-full"></span>
            </router-link>
            <router-link 
              to="/about" 
              class="text-secondary-600 hover:text-primary-600 font-medium transition-smooth relative group"
            >
              Sobre
              <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-primary-600 transition-all duration-300 group-hover:w-full"></span>
            </router-link>
          </div>
          
          <!-- User Menu -->
          <div class="flex items-center space-x-4">
            <template v-if="authStore.isAuthenticated">
              <router-link 
                to="/dashboard" 
                class="hidden md:block text-secondary-600 hover:text-primary-600 font-medium transition-smooth"
              >
                Dashboard
              </router-link>
              
              <!-- User Avatar -->
              <div class="flex items-center space-x-3">
                <router-link 
                  to="/profile" 
                  class="flex items-center space-x-2 text-secondary-700 hover:text-primary-600 transition-smooth"
                >
                  <div class="w-8 h-8 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center">
                    <span class="text-white text-sm font-semibold">
                      {{ authStore.user?.name?.charAt(0).toUpperCase() }}
                    </span>
                  </div>
                  <span class="hidden md:block font-medium">{{ authStore.user?.name }}</span>
                </router-link>
                
                <BaseButton 
                  variant="danger" 
                  size="sm"
                  @click="handleLogout"
                >
                  Sair
                </BaseButton>
              </div>
            </template>
            
            <template v-else>
              <BaseButton 
                variant="ghost" 
                size="sm"
                tag="router-link"
                to="/login"
              >
                Entrar
              </BaseButton>
              <BaseButton 
                variant="primary" 
                size="sm"
                tag="router-link"
                to="/register"
              >
                Cadastrar
              </BaseButton>
            </template>
            
            <!-- Mobile menu button -->
            <button
              @click="toggleMobileMenu"
              class="md:hidden p-2 rounded-lg text-secondary-600 hover:bg-secondary-100 transition-smooth"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path 
                  v-if="!mobileMenuOpen"
                  stroke-linecap="round" 
                  stroke-linejoin="round" 
                  stroke-width="2" 
                  d="M4 6h16M4 12h16M4 18h16"
                />
                <path 
                  v-else
                  stroke-linecap="round" 
                  stroke-linejoin="round" 
                  stroke-width="2" 
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
        </div>
        
        <!-- Mobile Navigation -->
        <div 
          v-if="mobileMenuOpen" 
          class="md:hidden py-4 border-t border-secondary-100 animate-slide-down"
        >
          <div class="flex flex-col space-y-4">
            <router-link 
              to="/" 
              class="text-secondary-600 hover:text-primary-600 font-medium transition-smooth"
              @click="mobileMenuOpen = false"
            >
              Home
            </router-link>
            <router-link 
              to="/skills" 
              class="text-secondary-600 hover:text-primary-600 font-medium transition-smooth"
              @click="mobileMenuOpen = false"
            >
              Habilidades
            </router-link>
            <router-link 
              to="/about" 
              class="text-secondary-600 hover:text-primary-600 font-medium transition-smooth"
              @click="mobileMenuOpen = false"
            >
              Sobre
            </router-link>
            
            <template v-if="authStore.isAuthenticated">
              <router-link 
                to="/dashboard" 
                class="text-secondary-600 hover:text-primary-600 font-medium transition-smooth"
                @click="mobileMenuOpen = false"
              >
                Dashboard
              </router-link>
              <router-link 
                to="/profile" 
                class="text-secondary-600 hover:text-primary-600 font-medium transition-smooth"
                @click="mobileMenuOpen = false"
              >
                Perfil
              </router-link>
            </template>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1">
      <RouterView v-slot="{ Component }">
        <transition
          name="page"
          enter-active-class="animate-fade-in"
          leave-active-class="animate-fade-in"
          mode="out-in"
        >
          <component :is="Component" />
        </transition>
      </RouterView>
    </main>

    <!-- Footer -->
    <footer class="bg-secondary-900 text-white">
      <div class="container-custom">
        <div class="py-12">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Brand -->
            <div class="space-y-4">
              <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center">
                  <span class="text-white text-sm font-bold">S</span>
                </div>
                <span class="text-xl font-bold font-heading">SkillSwap</span>
              </div>
              <p class="text-secondary-300 text-sm leading-relaxed">
                Conectando pessoas através do conhecimento. Aprenda, ensine e cresça junto com nossa comunidade.
              </p>
            </div>
            
            <!-- Links -->
            <div>
              <h3 class="font-semibold mb-4">Plataforma</h3>
              <ul class="space-y-2 text-sm">
                <li><router-link to="/skills" class="text-secondary-300 hover:text-white transition-smooth">Habilidades</router-link></li>
                <li><router-link to="/about" class="text-secondary-300 hover:text-white transition-smooth">Sobre Nós</router-link></li>
                <li><router-link to="/contact" class="text-secondary-300 hover:text-white transition-smooth">Contato</router-link></li>
              </ul>
            </div>
            
            <!-- Support -->
            <div>
              <h3 class="font-semibold mb-4">Suporte</h3>
              <ul class="space-y-2 text-sm">
                <li><router-link to="/help-center" class="text-secondary-300 hover:text-white transition-smooth">Central de Ajuda</router-link></li>
                <li><router-link to="/faq" class="text-secondary-300 hover:text-white transition-smooth">FAQ</router-link></li>
                <li><router-link to="/privacy-policy" class="text-secondary-300 hover:text-white transition-smooth">Política de Privacidade</router-link></li>
                <li><router-link to="/terms-of-use" class="text-secondary-300 hover:text-white transition-smooth">Termos de Uso</router-link></li>
              </ul>
            </div>
            
            <!-- Social -->
            <div>
              <h3 class="font-semibold mb-4">Redes Sociais</h3>
              <div class="flex space-x-4">
                <a href="#" class="w-10 h-10 bg-secondary-800 rounded-lg flex items-center justify-center hover:bg-primary-600 transition-smooth">
                  <span class="text-sm">📧</span>
                </a>
                <a href="#" class="w-10 h-10 bg-secondary-800 rounded-lg flex items-center justify-center hover:bg-primary-600 transition-smooth">
                  <span class="text-sm">🐦</span>
                </a>
                <a href="#" class="w-10 h-10 bg-secondary-800 rounded-lg flex items-center justify-center hover:bg-primary-600 transition-smooth">
                  <span class="text-sm">💼</span>
                </a>
              </div>
            </div>
          </div>
        </div>
        
        <div class="py-6 border-t border-secondary-800">
          <div class="flex flex-col md:flex-row justify-between items-center text-sm text-secondary-400">
            <p>&copy; 2024 SkillSwap. Todos os direitos reservados.</p>
            <p class="mt-2 md:mt-0">Feito com ❤️ para a comunidade</p>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* Router link active states */
.router-link-active {
  @apply text-primary-600;
}

.router-link-active span {
  @apply w-full;
}

/* Page transitions */
.page-enter-active,
.page-leave-active {
  transition: all 0.3s ease-in-out;
}

.page-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.page-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
