class ProfilePage {
  visit() {
    cy.visit('/profile')
    return this
  }

  visitPublic(userId: string) {
    cy.visit(`/users/${userId}/profile`)
    return this
  }

  assertLoaded() {
    cy.get('[data-testid="profile-page"]').should('be.visible')
    return this
  }

  assertPublicLoaded() {
    cy.get('[data-testid="user-profile-page"]').should('be.visible')
    return this
  }

  assertErrorState() {
    cy.get('[data-testid="profile-error"]').should('be.visible').and('contain', 'Erro ao carregar perfil')
    return this
  }

  edit() {
    cy.get('[data-testid="profile-edit"]').click()
    return this
  }

  fillBio(bio: string) {
    cy.get('[data-testid="profile-bio"] input').clear().type(bio)
    return this
  }

  fillLocation(location: string) {
    cy.get('[data-testid="profile-location"] input').clear().type(location)
    return this
  }

  save() {
    cy.get('[data-testid="profile-edit"]').click()
    return this
  }
}

export default new ProfilePage()
