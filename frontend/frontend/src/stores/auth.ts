import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService, oauthService } from '@/services/api'

interface User {
  id: number
  name: string
  email: string
  bio?: string
  location?: string
  avatar?: string
  phone?: string
  rating: number
  total_exchanges: number
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('token'))
  const loading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!token.value && !!user.value)

  // Inicializar usuário se tiver token
  const initializeAuth = async () => {
    if (token.value) {
      try {
        // Tentar carregar usuário do localStorage primeiro
        const savedUser = localStorage.getItem('user')
        if (savedUser) {
          user.value = JSON.parse(savedUser)
        }
        
        // Verificar se o token ainda é válido
        const response = await authService.getUser()
        if (response.data && response.data.success) {
          user.value = response.data.data
          localStorage.setItem('user', JSON.stringify(response.data.data))
        } else {
          logout()
        }
      } catch (error) {
        console.error('Erro ao inicializar autenticação:', error)
        logout()
      }
    }
  }

  const login = async (credentials: { email: string; password: string }) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await authService.login(credentials)
      if (response.data && response.data.success) {
        const { data: userData, token: authToken } = response.data
        
        user.value = userData
        token.value = authToken
        
        localStorage.setItem('token', authToken)
        localStorage.setItem('user', JSON.stringify(userData))
        
        return response.data
      } else {
        throw new Error('Resposta inválida do servidor')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao fazer login'
      throw err
    } finally {
      loading.value = false
    }
  }

  const register = async (userData: any) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await authService.register(userData)
      if (response.data && response.data.success) {
        const { data: newUser, token: authToken } = response.data
        
        user.value = newUser
        token.value = authToken
        
        localStorage.setItem('token', authToken)
        localStorage.setItem('user', JSON.stringify(newUser))
        
        return response.data
      } else {
        throw new Error('Resposta inválida do servidor')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao criar conta'
      throw err
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    try {
      if (token.value) {
        await authService.logout()
      }
    } catch (error) {
      console.error('Erro ao fazer logout:', error)
    } finally {
      user.value = null
      token.value = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    }
  }

  const updateProfile = async (profileData: any) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await authService.updateProfile(profileData)
      if (response.data && response.data.success) {
        user.value = response.data.data
        localStorage.setItem('user', JSON.stringify(response.data.data))
        return response.data
      } else {
        throw new Error('Resposta inválida do servidor')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao atualizar perfil'
      throw err
    } finally {
      loading.value = false
    }
  }

  const loginWithOAuth = async (provider: string) => {
    loading.value = true
    error.value = null
    
    try {
      // Get OAuth redirect URL
      const response = await oauthService.getRedirectUrl(provider)
      const { redirect_url } = response.data
      
      // Store the provider and redirect URL in sessionStorage for later use
      sessionStorage.setItem('oauth_provider', provider)
      sessionStorage.setItem('oauth_in_progress', 'true')
      
      // Redirect to OAuth provider
      window.location.href = redirect_url
      
    } catch (err: any) {
      error.value = err.response?.data?.message || `Erro ao fazer login com ${provider}`
      loading.value = false
      throw err
    }
  }

  const completeOAuthLogin = async (code: string, provider: string) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await oauthService.handleToken(provider, code)
      const { user: userData, token: authToken } = response.data
      
      user.value = userData
      token.value = authToken
      
      localStorage.setItem('token', authToken)
      localStorage.setItem('user', JSON.stringify(userData))
      
      // Clear OAuth session data
      sessionStorage.removeItem('oauth_provider')
      sessionStorage.removeItem('oauth_in_progress')
      
      return response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || `Erro ao completar login com ${provider}`
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    initializeAuth,
    login,
    register,
    logout,
    updateProfile,
    loginWithOAuth,
    completeOAuthLogin
  }
}) 