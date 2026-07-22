class SkillsPage {
  visit() {
    cy.visit('/skills')
    return this
  }

  assertLoaded() {
    cy.get('[data-testid="skills-page"]').should('be.visible')
    return this
  }

  search(query: string) {
    cy.get('[data-testid="skills-search"] input').clear().type(query)
    return this
  }

  selectCategoryAll() {
    cy.get('[data-testid="skills-category-all"]').click()
    return this
  }

  selectCategoryById(categoryId: number | string) {
    cy.get(`[data-testid="skills-category-${categoryId}"]`).click()
    return this
  }

  getSkillCards() {
    return cy.get('[data-testid="skill-card"]')
  }

  assertEmptyState() {
    cy.get('[data-testid="skills-empty"]').should('be.visible')
    return this
  }

  assertErrorState() {
    cy.get('[data-testid="skills-error"]').should('be.visible')
    return this
  }
}

export default new SkillsPage()
