/// <reference types="cypress" />
import dashboardPage from '../pages/DashboardPage'

describe('Dashboard E2E', () => {
  const maria = () => Cypress.env('demoMaria') as { email: string; password: string }

  beforeEach(() => {
    cy.clearAllCookies()
    cy.clearAllLocalStorage()
    cy.clearAllSessionStorage()
  })

  it('DASH-01: login maria, visit /dashboard, assert stats visible', () => {
    cy.loginUi(maria())
    cy.intercept('GET', '**/api/my-skills').as('mySkills')
    cy.intercept('GET', '**/api/user').as('getUser')

    dashboardPage.visit().assertLoaded()
    cy.wait('@getUser').its('response.statusCode').should('eq', 200)
    cy.wait('@mySkills')

    cy.get('[data-testid="dashboard-stats"]').within(() => {
      cy.contains('Habilidades').should('be.visible')
      cy.contains('Trocas').should('be.visible')
      cy.contains('Avaliação').should('be.visible')
      cy.contains('Conexões').should('be.visible')
    })
  })

  it('DASH-02: add skill via modal and assert it appears', () => {
    const title = `E2E Skill ${Date.now()}`

    cy.loginUi(maria())
    cy.intercept('GET', '**/api/categories').as('getCategories')
    cy.intercept('POST', '**/api/skills').as('createSkill')
    cy.intercept('DELETE', '**/api/skills/*').as('deleteSkill')

    dashboardPage.visit().assertLoaded()
    cy.wait('@getCategories').then((interception) => {
      const categories = interception.response?.body?.data as Array<{ id: number; name: string }>
      const category = categories[0]
      expect(category, 'at least one category').to.exist

      dashboardPage.openAddSkillModal().fillAddSkillForm({
        title,
        description: 'Habilidade criada pelo teste E2E do dashboard.',
        level: 'beginner',
        categoryId: category.id,
      })
      dashboardPage.submitAddSkill()
      cy.wait('@createSkill').its('response.statusCode').should('be.oneOf', [200, 201])

      cy.get('[data-testid="add-skill-modal"]').should('not.exist')
      dashboardPage.assertSkillVisible(title)

      // Cleanup: avoid leaking test skills into shared/dev DB across runs —
      // an accumulating "E2E Skill ..." backlog pushes seeded skills off
      // page 1 of the (client-side-filtered) /skills listing over time.
      cy.on('window:confirm', () => true)
      dashboardPage.deleteSkillByTitle(title)
      cy.wait('@deleteSkill').its('response.statusCode').should('be.oneOf', [200, 204])
    })
  })

  it('DASH-03: delete a skill created in test', () => {
    const title = `E2E Delete ${Date.now()}`

    cy.loginUi(maria())
    cy.intercept('GET', '**/api/categories').as('getCategories')
    cy.intercept('POST', '**/api/skills').as('createSkill')
    cy.intercept('DELETE', '**/api/skills/*').as('deleteSkill')

    dashboardPage.visit().assertLoaded()
    cy.wait('@getCategories').then((interception) => {
      const categories = interception.response?.body?.data as Array<{ id: number; name: string }>
      const category = categories[0]

      dashboardPage.openAddSkillModal().fillAddSkillForm({
        title,
        description: 'Skill temporária para exclusão no E2E.',
        level: 'intermediate',
        categoryId: category.id,
      })
      dashboardPage.submitAddSkill()
      cy.wait('@createSkill')
      dashboardPage.assertSkillVisible(title)

      cy.on('window:confirm', () => true)
      dashboardPage.deleteSkillByTitle(title)
      cy.wait('@deleteSkill').its('response.statusCode').should('be.oneOf', [200, 204])

      cy.contains('[data-testid="dashboard-skill-item"]', title).should('not.exist')
    })
  })

  it('DASH-04: empty add-skill form shows validation errors', () => {
    cy.loginUi(maria())

    dashboardPage.visit().assertLoaded()
    dashboardPage.openAddSkillModal()
    cy.intercept('POST', '**/api/skills').as('createSkill')

    dashboardPage.submitAddSkill()
    dashboardPage.assertValidationErrors()
    cy.get('@createSkill.all').should('have.length', 0)
    cy.get('[data-testid="add-skill-modal"]').should('be.visible')
  })

  it('DASH-05: invalid token redirects to login or blocks dashboard', () => {
    cy.window().then((win) => {
      win.localStorage.setItem('token', 'invalid')
      win.localStorage.setItem(
        'user',
        JSON.stringify({
          id: '00000000-0000-0000-0000-000000000001',
          name: 'Invalid User',
          email: 'invalid@skillswap.com',
          rating: 0,
          total_exchanges: 0,
        })
      )
    })

    cy.intercept('GET', '**/api/user', { statusCode: 401, body: { message: 'Unauthenticated' } }).as(
      'getUserUnauthorized'
    )

    dashboardPage.visit()

    cy.url({ timeout: 10000 }).should('satisfy', (url: string) => {
      return url.includes('/login') || url.includes('/dashboard')
    })

    cy.url().then((url) => {
      if (url.includes('/login')) {
        cy.get('[data-testid="login-email"]').should('be.visible')
      } else {
        cy.get('body').then(($body) => {
          const hasError =
            $body.find('[data-testid="dashboard-page"] .text-danger-500').length > 0 ||
            $body.text().includes('Erro')
          expect(hasError, 'dashboard error handling').to.eq(true)
        })
      }
    })
  })

  it('DASH-06: matches section is visible', () => {
    cy.loginUi(maria())
    cy.intercept('GET', '**/api/skill-matches').as('getMatches')

    dashboardPage.visit().assertLoaded()
    dashboardPage.assertMatchesVisible()
    cy.wait('@getMatches').its('response.statusCode').should('eq', 200)
    cy.get('[data-testid="dashboard-matches"]').should('contain', 'Matches Recomendados')
  })

  it('EXCH-11: dashboard lista trocas recentes após login', () => {
    cy.loginUi(maria())
    cy.intercept('GET', '**/api/exchanges').as('exchanges')

    dashboardPage.visit().assertLoaded()
    cy.wait('@exchanges')
    cy.get('[data-testid="dashboard-recent-exchanges"]').should('be.visible')
    cy.get('[data-testid="dashboard-exchange-item"]').should('have.length.greaterThan', 0)
  })

  it('DASH-08: click Ver Perfil em um match abre o perfil do usuário', () => {
    cy.loginUi(maria())
    cy.intercept('GET', '**/api/skill-matches').as('getMatches')

    dashboardPage.visit().assertLoaded()
    cy.wait('@getMatches')
    cy.get('[data-testid="dashboard-match-view-profile"]').first().click()
    cy.url().should('match', /\/users\/.+\/profile/)
    cy.get('[data-testid="user-profile-page"]').should('be.visible')
  })
})
