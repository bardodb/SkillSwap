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
  baseURL: 'http://localhost:8000/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Interceptor para adicionar token de autenticação
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
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
    if (error.response?.status === 401) {
      // Token expirado ou inválido
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      window.location.href = '/login'
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
  getById: (id: number) => api.get(`/categories/${id}`),
  create: (categoryData: any) => api.post('/categories', categoryData),
  update: (id: number, categoryData: any) => api.put(`/categories/${id}`, categoryData),
  delete: (id: number) => api.delete(`/categories/${id}`)
}

// Serviços de habilidades
export const skillService = {
  getAll: (params: Record<string, any> = {}) => api.get('/skills', { params }),
  getMySkills: () => api.get('/my-skills'),
  getMatches: () => api.get('/skill-matches'),
  getById: (id: number) => api.get(`/skills/${id}`),
  create: (skillData: any) => api.post('/skills', skillData),
  update: (id: number, skillData: any) => api.put(`/skills/${id}`, skillData),
  delete: (id: number) => api.delete(`/skills/${id}`)
}

// Serviços de trocas
export const exchangeService = {
  getAll: () => api.get('/exchanges'),
  getById: (id: number) => api.get(`/exchanges/${id}`),
  create: (exchangeData: any) => api.post('/exchanges', exchangeData),
  update: (id: number, exchangeData: any) => api.put(`/exchanges/${id}`, exchangeData),
  delete: (id: number) => api.delete(`/exchanges/${id}`)
}

// Serviços de mensagens
export const messageService = {
  getConversations: () => api.get('/conversations'),
  getConversation: (userId: number) => api.get(`/conversations/${userId}`),
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