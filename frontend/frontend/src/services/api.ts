import axios from 'axios'

// Types
interface LoginCredentials {
  email: string
  password: string
}

interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
  bio?: string
  location?: string
}

interface UpdateProfileData {
  name?: string
  email?: string
  bio?: string
  location?: string
  phone?: string
}

// Configuração base do Axios
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Controller usado para cancelar requisições pendentes (ex: ao fazer logout),
// evitando que respostas 401 de chamadas "em voo" disparem um redirect indevido.
let abortController = new AbortController()
let loggingOut = false

export const setLoggingOut = (value: boolean) => {
  loggingOut = value
}

export const cancelPendingRequests = () => {
  abortController.abort()
  abortController = new AbortController()
}

// Interceptor para adicionar token de autenticação
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    if (!config.signal) {
      config.signal = abortController.signal
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Interceptor para lidar com respostas de erro
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (axios.isCancel(error)) {
      return Promise.reject(error)
    }
    if (error.response?.status === 401) {
      const requestUrl = String(error.config?.url || '')
      const isLogoutRequest = requestUrl.includes('/logout')
      // Token expirado ou inválido — durante logout o App navega para `/`;
      // não hijackar com hard redirect para `/login` (nem no próprio /logout).
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      if (!loggingOut && !isLogoutRequest) {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

// Serviços de autenticação
export const authService = {
  register: (userData: RegisterData) => api.post('/register', userData),
  login: (credentials: LoginCredentials) => api.post('/login', credentials),
  logout: () => api.post('/logout'),
  getUser: () => api.get('/user'),
  updateProfile: (userData: UpdateProfileData) => api.put('/profile', userData),
  getUserProfile: (userId: string) => api.get(`/users/${userId}/profile`)
}

// Serviços OAuth
export const oauthService = {
  getRedirectUrl: (provider: string) => api.get(`/auth/${provider}/redirect`),
  handleCallback: (provider: string) => api.get(`/auth/${provider}/callback`),
  handleToken: (provider: string, code: string) => api.post(`/auth/${provider}/token`, { code })
}

// Serviços de categorias
export const categoryService = {
  getAll: () => api.get('/categories'),
  getById: (id: string) => api.get(`/categories/${id}`),
  create: (categoryData: any) => api.post('/categories', categoryData),
  update: (id: string, categoryData: any) => api.put(`/categories/${id}`, categoryData),
  delete: (id: string) => api.delete(`/categories/${id}`)
}

// Serviços de habilidades
export const skillService = {
  getAll: (params: Record<string, any> = {}) => api.get('/skills', { params }),
  getMySkills: () => api.get('/my-skills'),
  getMatches: () => api.get('/skill-matches'),
  getById: (id: string) => api.get(`/skills/${id}`),
  create: (skillData: any) => api.post('/skills', skillData),
  update: (id: string, skillData: any) => api.put(`/skills/${id}`, skillData),
  delete: (id: string) => api.delete(`/skills/${id}`)
}

// Serviços de trocas
export const exchangeService = {
  getAll: () => api.get('/exchanges'),
  getById: (id: string) => api.get(`/exchanges/${id}`),
  create: (exchangeData: any) => api.post('/exchanges', exchangeData),
  update: (id: string, exchangeData: any) => api.put(`/exchanges/${id}`, exchangeData),
  delete: (id: string) => api.delete(`/exchanges/${id}`)
}

// Serviços de mensagens
export const messageService = {
  getConversations: () => api.get('/conversations'),
  getConversation: (userId: string) => api.get(`/conversations/${userId}`),
  send: (messageData: any) => api.post('/messages', messageData),
  getAll: () => api.get('/messages')
}

// Serviços de estatísticas
export const statsService = {
  getStats: () => api.get('/stats'),
  getUserStats: () => api.get('/user-stats'),
  getWeeklyStats: () => api.get('/weekly-stats')
}

export default api 