/// <reference types="cypress" />

type DemoUser = { email: string; password: string }

declare global {
  namespace Cypress {
    interface Chainable {
      loginUi(user: DemoUser): Chainable<void>
      loginApi(user: DemoUser): Chainable<{ token: string; user: { id: number; name: string; email: string } }>
      apiRequest(
        method: string,
        path: string,
        options?: { body?: Record<string, unknown>; token?: string }
      ): Chainable<Cypress.Response<any>>
    }
  }
}

Cypress.Commands.add('loginUi', (user: DemoUser) => {
  cy.session(
    [`ui-${user.email}`],
    () => {
      cy.visit('/login')
      cy.get('[data-testid="login-email"] input').clear().type(user.email)
      cy.get('[data-testid="login-password"] input').clear().type(user.password, { log: false })
      cy.get('[data-testid="login-submit"]').click()
      cy.url().should('include', '/dashboard')
      cy.window().its('localStorage.token').should('be.a', 'string')
    },
    {
      validate() {
        cy.window().then((win) => {
          expect(win.localStorage.getItem('token'), 'token').to.be.a('string')
        })
      },
    }
  )
})

Cypress.Commands.add('loginApi', (user: DemoUser) => {
  const apiUrl = Cypress.env('apiUrl') as string
  return cy
    .request({
      method: 'POST',
      url: `${apiUrl}/login`,
      body: { email: user.email, password: user.password },
    })
    .then((res) => {
      expect(res.status).to.eq(200)
      expect(res.body.success).to.eq(true)
      expect(res.body.token).to.be.a('string')
      return {
        token: res.body.token as string,
        user: res.body.data as { id: number; name: string; email: string },
      }
    })
})

Cypress.Commands.add(
  'apiRequest',
  (method: string, path: string, options: { body?: Record<string, unknown>; token?: string } = {}) => {
    const apiUrl = Cypress.env('apiUrl') as string
    return cy.request({
      method,
      url: `${apiUrl}${path}`,
      body: options.body,
      failOnStatusCode: false,
      headers: options.token
        ? {
            Authorization: `Bearer ${options.token}`,
            Accept: 'application/json',
          }
        : { Accept: 'application/json' },
    })
  }
)

export {}
