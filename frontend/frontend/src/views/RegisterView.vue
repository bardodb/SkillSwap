<template>
  <div class="min-h-screen bg-gradient-to-br from-secondary-50 to-secondary-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-lg w-full space-y-8">
      <!-- Header -->
      <div class="text-center">
        <router-link to="/" class="inline-flex items-center space-x-2 mb-8">
          <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl flex items-center justify-center">
            <span class="text-white text-lg font-bold">S</span>
          </div>
          <span class="text-2xl font-bold font-heading text-gradient">SkillSwap</span>
        </router-link>
        
        <h2 class="heading-md text-secondary-900 mb-2">Crie sua conta</h2>
        <p class="text-secondary-600">Junte-se à nossa comunidade de aprendizado</p>
      </div>

      <!-- Form Card -->
      <BaseCard class="bg-white shadow-large">
        <form @submit.prevent="handleRegister" class="space-y-6">
          <!-- Name Field -->
          <BaseInput
            v-model="form.name"
            type="text"
            label="Nome completo"
            placeholder="Seu nome"
            required
            :error="errors.name"
          >
            <template #icon-left>
              <svg class="w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </template>
          </BaseInput>

          <!-- Email Field -->
          <BaseInput
            v-model="form.email"
            type="email"
            label="E-mail"
            placeholder="seu@email.com"
            required
            :error="errors.email"
          >
            <template #icon-left>
              <svg class="w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
              </svg>
            </template>
          </BaseInput>

          <!-- Password Field -->
          <BaseInput
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            label="Senha"
            placeholder="••••••••"
            required
            :error="errors.password"
            hint="Mínimo de 8 caracteres"
          >
            <template #icon-left>
              <svg class="w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </template>
            <template #icon-right>
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="text-secondary-400 hover:text-secondary-600 transition-colors"
              >
                <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
            </template>
          </BaseInput>

          <!-- Password Confirmation Field -->
          <BaseInput
            v-model="form.password_confirmation"
            :type="showPasswordConfirmation ? 'text' : 'password'"
            label="Confirmar senha"
            placeholder="••••••••"
            required
            :error="errors.password_confirmation"
          >
            <template #icon-left>
              <svg class="w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </template>
            <template #icon-right>
              <button
                type="button"
                @click="showPasswordConfirmation = !showPasswordConfirmation"
                class="text-secondary-400 hover:text-secondary-600 transition-colors"
              >
                <svg v-if="showPasswordConfirmation" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
            </template>
          </BaseInput>

          <!-- Password Strength Indicator -->
          <div v-if="form.password" class="space-y-2">
            <div class="text-sm font-medium text-secondary-700">Força da senha:</div>
            <div class="flex space-x-1">
              <div 
                v-for="i in 4" 
                :key="i"
                class="flex-1 h-2 rounded-full"
                :class="getPasswordStrengthColor(i)"
              ></div>
            </div>
            <div class="text-sm" :class="getPasswordStrengthTextColor()">
              {{ getPasswordStrengthText() }}
            </div>
          </div>

          <!-- Terms and Conditions -->
          <div class="flex items-start">
            <input
              type="checkbox"
              v-model="form.acceptTerms"
              class="w-4 h-4 mt-1 text-primary-600 bg-secondary-100 border-secondary-300 rounded focus:ring-primary-500 focus:ring-2"
            />
            <label class="ml-3 text-sm text-secondary-600 leading-relaxed">
              Concordo com os 
              <a href="#" class="text-primary-600 hover:text-primary-700 font-medium">Termos de Uso</a> 
              e 
              <a href="#" class="text-primary-600 hover:text-primary-700 font-medium">Política de Privacidade</a>
            </label>
          </div>
          <div v-if="errors.acceptTerms" class="form-error">
            {{ errors.acceptTerms }}
          </div>

          <!-- General Error -->
          <div v-if="errors.general" class="text-center">
            <div class="bg-danger-50 border border-danger-200 rounded-xl p-4">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-danger-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm text-danger-700">{{ errors.general }}</span>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <BaseButton
            type="submit"
            variant="primary"
            size="lg"
            :loading="loading"
            full-width
            class="shadow-medium hover:shadow-large"
          >
            <template #icon-left v-if="!loading">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
            </template>
            {{ loading ? 'Criando conta...' : 'Criar conta' }}
          </BaseButton>
        </form>
      </BaseCard>

      <!-- Login Link -->
      <div class="text-center">
        <p class="text-secondary-600">
          Já tem uma conta? 
          <router-link to="/login" class="text-primary-600 hover:text-primary-700 font-medium transition-colors">
            Fazer login
          </router-link>
        </p>
      </div>

      <!-- Divider -->
      <div class="relative">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-secondary-200"></div>
        </div>
        <div class="relative flex justify-center text-sm">
          <span class="px-4 bg-secondary-50 text-secondary-500">ou cadastre-se com</span>
        </div>
      </div>

      <!-- Social Login -->
      <div class="grid grid-cols-2 gap-3">
        <BaseButton 
          variant="outline" 
          size="lg" 
          class="bg-white"
          @click="handleOAuthLogin('google')"
          :loading="loading"
        >
          <template #icon-left>
            <svg class="w-5 h-5" viewBox="0 0 24 24">
              <path fill="#4285f4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path fill="#34a853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path fill="#fbbc05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
              <path fill="#ea4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
          </template>
          Google
        </BaseButton>
        
        <BaseButton 
          variant="outline" 
          size="lg" 
          class="bg-white"
          @click="handleOAuthLogin('github')"
          :loading="loading"
        >
          <template #icon-left>
            <svg class="w-5 h-5" fill="#24292e" viewBox="0 0 24 24">
              <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
            </svg>
          </template>
          GitHub
        </BaseButton>
      </div>

      <!-- Benefits -->
      <div class="bg-white rounded-2xl shadow-soft p-6">
        <h3 class="text-lg font-semibold text-secondary-900 mb-4 text-center">Por que criar uma conta?</h3>
        <div class="space-y-3">
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
              <span class="text-primary-600 text-sm">✓</span>
            </div>
            <span class="text-secondary-600 text-sm">Acesso gratuito a milhares de habilidades</span>
          </div>
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-success-100 rounded-full flex items-center justify-center">
              <span class="text-success-600 text-sm">✓</span>
            </div>
            <span class="text-secondary-600 text-sm">Conecte-se com pessoas que compartilham seus interesses</span>
          </div>
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-warning-100 rounded-full flex items-center justify-center">
              <span class="text-warning-600 text-sm">✓</span>
            </div>
            <span class="text-secondary-600 text-sm">Sistema de mensagens para organizar trocas</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseInput from '@/components/ui/BaseInput.vue'

const router = useRouter()
const authStore = useAuthStore()

const loading = ref(false)
const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  acceptTerms: false
})

const errors = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  acceptTerms: '',
  general: ''
})

const clearErrors = () => {
  errors.name = ''
  errors.email = ''
  errors.password = ''
  errors.password_confirmation = ''
  errors.acceptTerms = ''
  errors.general = ''
}

const validateForm = (): boolean => {
  clearErrors()
  let isValid = true

  if (!form.name.trim()) {
    errors.name = 'Nome é obrigatório'
    isValid = false
  } else if (form.name.trim().length < 2) {
    errors.name = 'Nome deve ter pelo menos 2 caracteres'
    isValid = false
  }

  if (!form.email) {
    errors.email = 'E-mail é obrigatório'
    isValid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'E-mail inválido'
    isValid = false
  }

  if (!form.password) {
    errors.password = 'Senha é obrigatória'
    isValid = false
  } else if (form.password.length < 8) {
    errors.password = 'Senha deve ter pelo menos 8 caracteres'
    isValid = false
  }

  if (!form.password_confirmation) {
    errors.password_confirmation = 'Confirmação de senha é obrigatória'
    isValid = false
  } else if (form.password !== form.password_confirmation) {
    errors.password_confirmation = 'As senhas não coincidem'
    isValid = false
  }

  if (!form.acceptTerms) {
    errors.acceptTerms = 'Você deve aceitar os termos de uso'
    isValid = false
  }

  return isValid
}

// Password strength calculation
const passwordStrength = computed(() => {
  if (!form.password) return 0

  let strength = 0
  const password = form.password

  // Length check
  if (password.length >= 8) strength++
  if (password.length >= 12) strength++

  // Character variety checks
  if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++
  if (/\d/.test(password)) strength++
  if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++

  return Math.min(strength, 4)
})

const getPasswordStrengthColor = (index: number) => {
  if (index <= passwordStrength.value) {
    if (passwordStrength.value <= 1) return 'bg-danger-500'
    if (passwordStrength.value <= 2) return 'bg-warning-500'
    if (passwordStrength.value <= 3) return 'bg-success-400'
    return 'bg-success-600'
  }
  return 'bg-secondary-200'
}

const getPasswordStrengthTextColor = () => {
  if (passwordStrength.value <= 1) return 'text-danger-600'
  if (passwordStrength.value <= 2) return 'text-warning-600'
  if (passwordStrength.value <= 3) return 'text-success-500'
  return 'text-success-600'
}

const getPasswordStrengthText = () => {
  if (passwordStrength.value <= 1) return 'Fraca'
  if (passwordStrength.value <= 2) return 'Regular'
  if (passwordStrength.value <= 3) return 'Boa'
  return 'Excelente'
}

const handleOAuthLogin = async (provider: string) => {
  try {
    await authStore.loginWithOAuth(provider)
    router.push('/dashboard')
  } catch (error: any) {
    errors.general = error.message || `Erro ao fazer login com ${provider}`
  }
}

const handleRegister = async () => {
  if (!validateForm()) return

  loading.value = true
  clearErrors()

  try {
    await authStore.register({
      name: form.name.trim(),
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation
    })
    
    router.push('/dashboard')
  } catch (error: any) {
    if (error.response?.data?.errors) {
      const serverErrors = error.response.data.errors
      if (serverErrors.name) errors.name = serverErrors.name[0]
      if (serverErrors.email) errors.email = serverErrors.email[0]
      if (serverErrors.password) errors.password = serverErrors.password[0]
    } else {
      errors.general = error.response?.data?.message || 'Erro ao criar conta. Tente novamente.'
    }
  } finally {
    loading.value = false
  }
}
</script> 