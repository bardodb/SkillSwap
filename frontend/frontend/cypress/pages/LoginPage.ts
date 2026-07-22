class LoginPage {
  visit() {
    cy.visit('/login')
    return this
  }

  visitRegister() {
    cy.visit('/register')
    return this
  }

  fillLogin(email: string, password: string) {
    cy.get('[data-testid="login-email"] input').clear().type(email)
    cy.get('[data-testid="login-password"] input').clear().type(password, { log: false })
    return this
  }

  submitLogin() {
    cy.get('[data-testid="login-submit"]').click()
    return this
  }

  fillRegister(data: { name: string; email: string; password: string }) {
    // Testid may be on the BaseInput wrapper or the <input> (Vue attrs fallthrough).
    const inputOf = (testId: string) =>
      cy.get(`input[data-testid="${testId}"], [data-testid="${testId}"] input`).first()

    inputOf('register-name').clear().type(data.name)
    inputOf('register-email').clear().type(data.email)
    // Re-query between clear/type: password-strength UI can remount nearby fields
    // and leave a stale subject that Cypress reports as disabled.
    inputOf('register-password').clear()
    inputOf('register-password').should('be.enabled').type(data.password, { log: false })
    inputOf('register-password-confirmation').clear()
    inputOf('register-password-confirmation').should('be.enabled').type(data.password, { log: false })
    cy.get('[data-testid="register-accept-terms"]').check({ force: true })
    return this
  }

  submitRegister() {
    cy.get('[data-testid="register-submit"]').click()
    return this
  }

  assertOnDashboard() {
    cy.url().should('include', '/dashboard')
    cy.window().its('localStorage.token').should('be.a', 'string')
    return this
  }

  assertOnLogin() {
    cy.url().should('include', '/login')
    return this
  }

  assertGeneralError() {
    cy.get('[data-testid="login-error"]').should('be.visible')
    return this
  }

  assertFieldError(testid: string) {
    cy.get(`[data-testid="${testid}"]`).find('.form-error').should('be.visible')
    return this
  }
}

export default new LoginPage()
