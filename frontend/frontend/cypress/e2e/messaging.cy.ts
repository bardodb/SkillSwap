/// <reference types="cypress" />

describe('Messaging E2E', () => {
  const maria = () => Cypress.env('demoMaria') as { email: string; password: string }
  const joao = () => Cypress.env('demoJoao') as { email: string; password: string }

  it('Maria opens seeded conversation with João and sees the first exchange message', () => {
    cy.loginUi(maria())
    cy.intercept('GET', '**/api/conversations').as('conversations')
    cy.intercept('GET', '**/api/conversations/*').as('thread')

    cy.visit('/chat')
    cy.get('[data-testid="chat-page"]').should('be.visible')
    cy.wait('@conversations').its('response.statusCode').should('eq', 200)

    cy.get('[data-testid="conversation-list"]').should('be.visible')
    cy.contains('[data-testid^="conversation-item-"]', 'João Silva').click()
    cy.wait('@thread').its('response.statusCode').should('eq', 200)

    cy.get('[data-testid="thread-partner"]').should('contain', 'João Silva')
    cy.get('[data-testid="message-thread"]').within(() => {
      cy.get('[data-testid="chat-message"]').should('have.length.at.least', 1)
      cy.contains('[data-testid="chat-message"]', 'Claro').should('be.visible')
    })
    cy.get('[data-testid="message-composer"]').should('be.visible')
    cy.get('[data-testid="message-input"] input').should('not.be.disabled')
    cy.get('[data-testid="composer-disabled-reason"]').should('not.exist')
  })

  it('Maria sends a message and it appears in the thread', () => {
    const unique = `E2E Maria ${Date.now()}`

    cy.loginUi(maria())
    cy.intercept('POST', '**/api/messages').as('sendMessage')
    cy.intercept('GET', '**/api/conversations/*').as('thread')

    cy.visit('/chat')
    cy.contains('[data-testid^="conversation-item-"]', 'João Silva').click()
    cy.wait('@thread')

    cy.get('[data-testid="message-input"] input').clear().type(unique)
    cy.get('[data-testid="message-send"]').click()
    cy.wait('@sendMessage').its('response.statusCode').should('be.oneOf', [200, 201])

    cy.get('[data-testid="chat-message"]').should('contain', unique)
  })

  it('João opens deep link /chat?user=<partner uuid> and can message Maria', () => {
    cy.loginUi(joao())
    cy.intercept('GET', '**/api/conversations').as('conversations')

    cy.visit('/chat')
    cy.wait('@conversations').then((interception) => {
      const list = interception.response?.body?.data as Array<{ partner: { id: string; name: string } }>
      const maria = list.find((c) => c.partner.name.includes('Maria'))
      expect(maria, 'seeded Maria conversation').to.exist
      const mariaUuid = maria!.partner.id

      cy.intercept('GET', `**/api/conversations/${mariaUuid}`).as('mariaThread')
      cy.visit(`/chat?user=${mariaUuid}`)
      cy.get('[data-testid="chat-page"]').should('be.visible')
      cy.wait('@mariaThread').its('response.statusCode').should('eq', 200)

      cy.get('[data-testid="thread-partner"]').should('contain', 'Maria')
      cy.get('[data-testid="message-composer"]').should('be.visible')
    })
  })

  it('API: creating an exchange creates the first message for the receiver', () => {
    cy.loginApi(joao()).then((joaoAuth) => {
      cy.loginApi(maria()).then((mariaAuth) => {
        // Use demo skill ids from API responses (UUID strings)
        cy.apiRequest('GET', '/my-skills', { token: joaoAuth.token }).then((mySkills) => {
          cy.apiRequest('GET', `/users/${mariaAuth.user.id}/profile`, { token: joaoAuth.token }).then(
            (profile) => {
              const offered = mySkills.body?.data?.[0] ?? mySkills.body?.[0]
              const requested =
                profile.body?.data?.skills?.[0] ??
                profile.body?.skills?.[0] ??
                profile.body?.data?.user?.skills?.[0]

              const offeredId = offered?.id
              const requestedId = requested?.id
              expect(offeredId, 'offered skill id').to.be.a('string')
              expect(requestedId, 'requested skill id').to.be.a('string')

              cy.apiRequest('POST', '/exchanges', {
                token: joaoAuth.token,
                body: {
                  receiver_id: mariaAuth.user.id,
                  offered_skill_id: offeredId,
                  requested_skill_id: requestedId,
                  message: `E2E exchange first message ${Date.now()}`,
                },
              }).then((exchangeRes) => {
                // 200/201 success OR 400 if duplicate live exchange for same skills
                if (exchangeRes.status === 200 || exchangeRes.status === 201) {
                  expect(exchangeRes.body.success).to.eq(true)
                  expect(exchangeRes.body.conversation_partner_id).to.eq(mariaAuth.user.id)
                  expect(exchangeRes.body.first_message_id).to.be.a('string')

                  cy.apiRequest('GET', '/conversations', { token: mariaAuth.token }).then((conv) => {
                    expect(conv.status).to.eq(200)
                    expect(conv.body.success).to.eq(true)
                    const withJoao = (conv.body.data as Array<{ partner: { id: string }; can_message: boolean }>).find(
                      (c) => c.partner.id === joaoAuth.user.id
                    )
                    expect(withJoao, 'Maria should have conversation with João').to.exist
                    expect(withJoao!.can_message).to.eq(true)
                  })
                } else {
                  // Duplicate live exchange is acceptable for seeded demo — assert conversations still work
                  expect(exchangeRes.status).to.be.oneOf([400, 422])
                  cy.apiRequest('GET', '/conversations', { token: mariaAuth.token }).then((conv) => {
                    expect(conv.status).to.eq(200)
                    expect(conv.body.data.length).to.be.greaterThan(0)
                  })
                }
              })
            }
          )
        })
      })
    })
  })

  it('API: send gate returns 422 without a live exchange', () => {
    // Register a fresh user with no exchanges, try messaging João
    const email = `e2e.gate.${Date.now()}@test.com`
    cy.request({
      method: 'POST',
      url: `${Cypress.env('apiUrl')}/register`,
      body: {
        name: 'E2E Gate User',
        email,
        password: 'password123',
        password_confirmation: 'password123',
      },
    }).then((reg) => {
      expect(reg.status).to.be.oneOf([200, 201])
      const token = reg.body.token as string

      cy.loginApi(joao()).then((joaoAuth) => {
        cy.apiRequest('POST', '/messages', {
          token,
          body: {
            receiver_id: joaoAuth.user.id,
            content: 'Should be blocked',
          },
        }).then((res) => {
          expect(res.status).to.eq(422)
        })
      })
    })
  })
})
