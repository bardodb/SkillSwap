<template>
  <div class="min-h-screen bg-gradient-to-br from-secondary-50 to-secondary-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- Loading State -->
      <div v-if="loading" class="text-center">
        <BaseCard class="bg-white shadow-large p-8">
          <div class="flex flex-col items-center space-y-4">
            <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl flex items-center justify-center">
              <BaseLoading class="w-6 h-6 text-white" />
            </div>
            <h2 class="text-xl font-semibold text-secondary-900">Processando autenticação...</h2>
            <p class="text-secondary-600">Aguarde enquanto finalizamos seu login.</p>
          </div>
        </BaseCard>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="text-center">
        <BaseCard class="bg-white shadow-large p-8">
          <div class="flex flex-col items-center space-y-4">
            <div class="w-12 h-12 bg-danger-100 rounded-2xl flex items-center justify-center">
              <svg class="w-6 h-6 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h2 class="text-xl font-semibold text-danger-900">Erro na autenticação</h2>
            <p class="text-secondary-600">{{ error }}</p>
            <BaseButton
              variant="primary"
              @click="goToLogin"
            >
              Voltar ao login
            </BaseButton>
          </div>
        </BaseCard>
      </div>

      <!-- Success State -->
      <div v-else class="text-center">
        <BaseCard class="bg-white shadow-large p-8">
          <div class="flex flex-col items-center space-y-4">
            <div class="w-12 h-12 bg-success-100 rounded-2xl flex items-center justify-center">
              <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <h2 class="text-xl font-semibold text-success-900">Login realizado com sucesso!</h2>
            <p class="text-secondary-600">Redirecionando para o dashboard...</p>
          </div>
        </BaseCard>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseLoading from '@/components/ui/BaseLoading.vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const loading = ref(true)
const error = ref<string | null>(null)

const goToLogin = () => {
  router.push('/login')
}

const processCallback = async () => {
  try {
    const queryParams = route.query
    
    // Check for error in URL
    if (queryParams.error) {
      error.value = queryParams.error as string
      loading.value = false
      return
    }

    // Check for success data
    if (queryParams.success && queryParams.data) {
      try {
        const userData = JSON.parse(atob(queryParams.data as string))
        const { user, token } = userData

        // Set user data in store
        authStore.user = user
        authStore.token = token

        // Save to localStorage
        localStorage.setItem('token', token)
        localStorage.setItem('user', JSON.stringify(user))

        // Clear OAuth session data
        sessionStorage.removeItem('oauth_provider')
        sessionStorage.removeItem('oauth_in_progress')

        loading.value = false

        // Redirect to dashboard after short delay
        setTimeout(() => {
          router.push('/dashboard')
        }, 1500)

      } catch (decodeError) {
        console.error('Error decoding OAuth data:', decodeError)
        error.value = 'Erro ao processar dados de autenticação'
        loading.value = false
      }
    } else {
      error.value = 'Dados de autenticação inválidos'
      loading.value = false
    }

  } catch (err: any) {
    console.error('OAuth callback error:', err)
    error.value = 'Erro inesperado durante a autenticação'
    loading.value = false
  }
}

onMounted(() => {
  processCallback()
})
</script> 