/// <reference types="cypress" />

type DemoUser = { email: string; password: string }

type CreateExchangeOpts = {
  token: string
  receiverId: string
  offeredSkillId: string
  requestedSkillId: string
  message: string
}

type CreateFreeExchangeOpts = {
  initiatorToken: string
  receiverId: string
  receiverToken: string
  message: string
}

declare global {
  namespace Cypress {
    interface Chainable {
      loginUi(user: DemoUser): Chainable<void>
      loginApi(user: DemoUser): Chainable<{ token: string; user: { id: string; name: string; email: string } }>
      apiRequest(
        method: string,
        path: string,
        options?: { body?: Record<string, unknown>; token?: string }
      ): Chainable<Cypress.Response<any>>
      createExchangeApi(opts: CreateExchangeOpts): Chainable<Cypress.Response<any>>
      createFreeExchange(opts: CreateFreeExchangeOpts): Chainable<Cypress.Response<any>>
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
        user: res.body.data as { id: string; name: string; email: string },
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

Cypress.Commands.add('createExchangeApi', (opts: CreateExchangeOpts) => {
  return cy.apiRequest('POST', '/exchanges', {
    token: opts.token,
    body: {
      receiver_id: opts.receiverId,
      offered_skill_id: opts.offeredSkillId,
      requested_skill_id: opts.requestedSkillId,
      message: opts.message,
    },
  })
})

Cypress.Commands.add('createFreeExchange', (opts: CreateFreeExchangeOpts) => {
  return cy.apiRequest('GET', '/my-skills', { token: opts.initiatorToken }).then((mySkills) => {
    return cy
      .apiRequest('GET', `/users/${opts.receiverId}/profile`, { token: opts.initiatorToken })
      .then((profile) => {
        const offeredList = (mySkills.body?.data ?? mySkills.body) as Array<{ id: string }>
        const requestedList = (profile.body?.data?.skills ?? profile.body?.skills ?? []) as Array<{
          id: string
        }>
        expect(offeredList.length, 'offered skills').to.be.greaterThan(0)
        expect(requestedList.length, 'requested skills').to.be.greaterThan(0)

        const tryCreate = (oi: number, ri: number): Cypress.Chainable<Cypress.Response<any>> => {
          if (oi >= offeredList.length) {
            throw new Error('Não foi possível criar exchange livre para o par informado')
          }
          if (ri >= requestedList.length) return tryCreate(oi + 1, 0)

          return cy
            .apiRequest('POST', '/exchanges', {
              token: opts.initiatorToken,
              body: {
                receiver_id: opts.receiverId,
                offered_skill_id: offeredList[oi].id,
                requested_skill_id: requestedList[ri].id,
                message: opts.message,
              },
            })
            .then((res) => {
              if (res.status !== 200 && res.status !== 201) return tryCreate(oi, ri + 1)
              return cy.wrap(res)
            })
        }

        return tryCreate(0, 0)
      })
  })
})

export {}
