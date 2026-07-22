class DashboardPage {
  visit() {
    cy.visit('/dashboard')
    return this
  }

  assertLoaded() {
    cy.get('[data-testid="dashboard-page"]').should('be.visible')
    cy.get('[data-testid="dashboard-stats"]').should('be.visible')
    return this
  }

  openAddSkillModal() {
    cy.get('[data-testid="dashboard-add-skill"]').click()
    cy.get('[data-testid="add-skill-modal"]').should('be.visible')
    return this
  }

  fillAddSkillForm(data: {
    title: string
    description: string
    level: string
    categoryLabel?: string
    categoryId?: string | number
  }) {
    cy.get('[data-testid="add-skill-title"] input').clear().type(data.title)
    cy.get('[data-testid="add-skill-description"] input').clear().type(data.description)
    cy.get('[data-testid="add-skill-level"]').select(data.level)
    if (data.categoryId !== undefined) {
      cy.get('[data-testid="add-skill-category"]').select(String(data.categoryId))
    } else if (data.categoryLabel) {
      cy.get('[data-testid="add-skill-category"]').select(data.categoryLabel)
    }
    return this
  }

  submitAddSkill() {
    cy.get('[data-testid="add-skill-submit"]').click()
    return this
  }

  assertSkillVisible(title: string) {
    cy.contains('[data-testid="dashboard-skill-item"]', title).should('be.visible')
    return this
  }

  deleteSkillByTitle(title: string) {
    cy.contains('[data-testid="dashboard-skill-item"]', title)
      .find('[data-testid^="dashboard-skill-delete-"]')
      .click()
    return this
  }

  assertValidationErrors() {
    cy.get('[data-testid="add-skill-title"]').find('.form-error').should('be.visible')
    cy.get('[data-testid="add-skill-description"]').find('.form-error').should('be.visible')
    cy.get('[data-testid="add-skill-level"]').parent().find('.form-error').should('be.visible')
    cy.get('[data-testid="add-skill-category"]').parent().find('.form-error').should('be.visible')
    return this
  }

  assertMatchesVisible() {
    cy.get('[data-testid="dashboard-matches"]').should('be.visible')
    return this
  }
}

export default new DashboardPage()
