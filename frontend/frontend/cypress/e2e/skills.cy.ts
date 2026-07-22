/// <reference types="cypress" />
import skillsPage from '../pages/SkillsPage'

describe('Skills E2E', () => {
  beforeEach(() => {
    cy.clearAllCookies()
    cy.clearAllLocalStorage()
    cy.clearAllSessionStorage()
  })

  it('SKILL-01: visit /skills, assert skill cards visible', () => {
    cy.intercept('GET', '**/api/skills').as('getSkills')
    cy.intercept('GET', '**/api/categories').as('getCategories')

    skillsPage.visit().assertLoaded()
    cy.wait('@getSkills').its('response.statusCode').should('eq', 200)
    cy.wait('@getCategories').its('response.statusCode').should('eq', 200)

    skillsPage.getSkillCards().should('have.length.greaterThan', 0)
  })

  it('SKILL-02: search matches seeded Laravel skill', () => {
    cy.intercept('GET', '**/api/skills').as('getSkills')

    skillsPage.visit().assertLoaded()
    cy.wait('@getSkills')

    skillsPage.search('Laravel')
    skillsPage.getSkillCards().should('have.length.at.least', 1)
    skillsPage.getSkillCards().first().should('contain', 'Laravel')
    cy.get('[data-testid="skills-empty"]').should('not.exist')
  })

  it('SKILL-03: category filter changes results', () => {
    cy.intercept('GET', '**/api/skills').as('getSkills')
    cy.intercept('GET', '**/api/categories').as('getCategories')

    skillsPage.visit().assertLoaded()
    cy.wait('@getSkills')
    cy.wait('@getCategories').then((interception) => {
      const categories = interception.response?.body?.data as Array<{ id: string | number; name: string }>
      const programming = categories.find((c) => c.name === 'Programação')
      expect(programming, 'seeded Programação category').to.exist

      skillsPage.getSkillCards().its('length').then((initialCount) => {
        expect(initialCount).to.be.greaterThan(1)
        skillsPage.selectCategoryById(programming!.id)
        skillsPage.getSkillCards().should('have.length.at.least', 1)
        skillsPage.getSkillCards().its('length').should('be.lessThan', initialCount)
        cy.contains('[data-testid="skill-card"]', 'Laravel').should('be.visible')

        skillsPage.selectCategoryAll()
        skillsPage.getSkillCards().should('have.length', initialCount)
      })
    })
  })

  it('SKILL-04: nonsense search shows empty state', () => {
    cy.intercept('GET', '**/api/skills').as('getSkills')

    skillsPage.visit().assertLoaded()
    cy.wait('@getSkills')

    skillsPage.search('xyznonexistentterm12345')
    skillsPage.assertEmptyState()
    skillsPage.getSkillCards().should('not.exist')
  })

  it('SKILL-05: API 500 shows error state', () => {
    cy.intercept('GET', '**/api/skills', {
      statusCode: 500,
      body: { success: false, message: 'Server error' },
    }).as('skillsFail')

    skillsPage.visit().assertLoaded()
    cy.wait('@skillsFail')
    skillsPage.assertErrorState()
    cy.get('[data-testid="skill-card"]').should('not.exist')
  })

  it('SKILL-06: home category or Ver Todas navigates to /skills', () => {
    cy.intercept('GET', '**/api/categories').as('getCategories')

    cy.visit('/')
    cy.wait('@getCategories')
    cy.get('[data-testid="home-categories"]').should('be.visible')

    cy.get('[data-testid^="home-category-"]').first().click()
    cy.url().should('include', '/skills')
    cy.get('[data-testid="skills-page"]').should('be.visible')

    cy.visit('/')
    cy.wait('@getCategories')
    cy.get('[data-testid="home-view-all-categories"]').click()
    cy.url().should('include', '/skills')
    cy.get('[data-testid="skills-page"]').should('be.visible')
  })
})
