import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import ChatView from '../views/ChatView.vue'
import ProfileView from '../views/ProfileView.vue'
import UserProfileView from '../views/UserProfileView.vue'
import AgendaView from '../views/AgendaView.vue'
import SkillsView from '../views/SkillsView.vue'
import HelpCenterView from '../views/HelpCenterView.vue'
import FaqView from '../views/FaqView.vue'
import PrivacyPolicyView from '../views/PrivacyPolicyView.vue'
import TermsOfUseView from '../views/TermsOfUseView.vue'
import AuthCallbackView from '../views/AuthCallbackView.vue'
import ContactView from '../views/ContactView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue')
    },
    {
      path: '/auth/callback',
      name: 'auth-callback',
      component: AuthCallbackView,
    },
    {
      path: '/skills',
      name: 'skills',
      component: SkillsView,
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: DashboardView,
    },
    {
      path: '/chat',
      name: 'chat',
      component: ChatView,
    },
    {
      path: '/profile',
      name: 'profile',
      component: ProfileView,
    },
    {
      path: '/users/:userId/profile',
      name: 'user-profile',
      component: UserProfileView,
    },
    {
      path: '/agenda',
      name: 'agenda',
      component: AgendaView,
    },
    {
      path: '/about',
      name: 'about',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../views/AboutView.vue')
    },
    {
      path: '/help-center',
      name: 'help-center',
      component: HelpCenterView,
    },
    {
      path: '/faq',
      name: 'faq',
      component: FaqView,
    },
    {
      path: '/privacy-policy',
      name: 'privacy-policy',
      component: PrivacyPolicyView,
    },
    {
      path: '/terms-of-use',
      name: 'terms-of-use',
      component: TermsOfUseView,
    },
    {
      path: '/contact',
      name: 'contact',
      component: ContactView,
    },
  ],
})

// Guard para rotas que requerem autenticação
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Rotas que requerem autenticação
  const protectedRoutes = ['/dashboard', '/profile', '/chat', '/agenda']
  
  // Rotas que não devem ser acessadas quando autenticado
  const publicOnlyRoutes = ['/login', '/register']
  
  // Se a rota requer autenticação
  if (protectedRoutes.includes(to.path)) {
    if (!authStore.isAuthenticated) {
      // Redirecionar para login se não estiver autenticado
      next('/login')
      return
    }
  }
  
  // Se a rota é apenas para usuários não autenticados
  if (publicOnlyRoutes.includes(to.path)) {
    if (authStore.isAuthenticated) {
      // Redirecionar para dashboard se já estiver autenticado
      next('/dashboard')
      return
    }
  }
  
  next()
})

export default router
