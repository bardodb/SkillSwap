/// <reference types="cypress" />
import loginPage from '../pages/LoginPage'

describe('Auth E2E', () => {
  const maria = () => Cypress.env('demoMaria') as { email: string; password: string }

  beforeEach(() => {
    cy.clearAllCookies()
    cy.clearAllLocalStorage()
    cy.clearAllSessionStorage()
  })

  it('AUTH-01: Login demo redireciona para dashboard', () => {
    const user = maria()
    loginPage.visit().fillLogin(user.email, user.password).submitLogin().assertOnDashboard()
  })

  it('AUTH-02: Credenciais inválidas mostram erro', () => {
    loginPage.visit().fillLogin('wrong@skillswap.com', 'wrongpassword').submitLogin().assertOnLogin()
    // API returns ValidationException on email field (422), not a general banner
    cy.get('[data-testid="login-email"]').closest('.space-y-2').find('.form-error').should('be.visible')
  })

  it('AUTH-03: Campos vazios mostram validação', () => {
    loginPage.visit()
    cy.get('form').invoke('attr', 'novalidate', 'novalidate')
    cy.get('[data-testid="login-email"] input').clear()
    cy.get('[data-testid="login-password"] input').clear()
    loginPage.submitLogin()
    cy.url().should('include', '/login')
    cy.get('[data-testid="login-email"]').closest('.space-y-2').find('.form-error').should('be.visible')
    cy.get('[data-testid="login-password"]').closest('.space-y-2').find('.form-error').should('be.visible')
  })

  it('AUTH-04: Visitante em /dashboard vai para /login', () => {
    cy.visit('/dashboard')
    cy.url().should('include', '/login')
  })

  it('AUTH-05: Autenticado em /login vai para /dashboard', () => {
    cy.loginUi(maria())
    cy.visit('/login')
    cy.url().should('include', '/dashboard')
  })

  it('AUTH-06: Register válido vai para dashboard', () => {
    const email = `e2e.reg.${Date.now()}@test.com`
    loginPage
      .visitRegister()
      .fillRegister({ name: 'E2E Register', email, password: 'password123' })
      .submitRegister()
      .assertOnDashboard()
  })

  it('AUTH-07: Register email duplicado mostra erro', () => {
    loginPage
      .visitRegister()
      .fillRegister({
        name: 'Dup User',
        email: maria().email,
        password: 'password123',
      })
      .submitRegister()
    cy.url().should('include', '/register')
    cy.get('[data-testid="register-email"]').closest('.space-y-2').find('.form-error').should('be.visible')
  })

  it('AUTH-08: Logout limpa sessão', () => {
    cy.loginUi(maria())
    cy.visit('/dashboard')
    cy.get('[data-testid="nav-logout"]').click()
    cy.url().should('eq', `${Cypress.config('baseUrl')}/`)
    cy.window().its('localStorage.token').should('not.exist')
    cy.visit('/dashboard')
    cy.url().should('include', '/login')
  })
})
