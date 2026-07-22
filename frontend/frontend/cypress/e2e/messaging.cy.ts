/// <reference types="cypress" />
import chatPage from '../pages/ChatPage'

describe('Mensagens E2E', () => {
  const maria = () => Cypress.env('demoMaria') as { email: string; password: string }
  const joao = () => Cypress.env('demoJoao') as { email: string; password: string }

  it('CHAT-01: Maria abre conversa seedada com João e vê a primeira mensagem da troca', () => {
    cy.loginUi(maria())
    cy.intercept('GET', '**/api/conversations').as('conversations')
    cy.intercept('GET', '**/api/conversations/*').as('thread')

    chatPage.visit().assertLoaded()
    cy.wait('@conversations').its('response.statusCode').should('eq', 200)

    cy.get('[data-testid="conversation-list"]').should('be.visible')
    chatPage.openConversationByName('João Silva')
    cy.wait('@thread').its('response.statusCode').should('eq', 200)

    chatPage.assertThreadPartner('João Silva')
    cy.get('[data-testid="message-thread"]').within(() => {
      cy.get('[data-testid="chat-message"]').should('have.length.at.least', 1)
      chatPage.assertMessageVisible('Claro')
    })
    cy.get('[data-testid="message-composer"]').should('be.visible')
    chatPage.assertComposerEnabled()
  })

  it('CHAT-02: Maria envia mensagem e ela aparece no thread', () => {
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

  it('CHAT-03: João abre deep link /chat?user=<uuid do parceiro> e consegue enviar mensagens para Maria', () => {
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

  it('CHAT-04: API: criar troca cria a primeira mensagem para o receptor', () => {
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
                  // These live under `exchange`, not at the top level of the
                  // response (confirmed in ExchangeController::store) — the
                  // previous top-level checks were always undefined, so this
                  // branch deterministically failed on the first attempt and
                  // only "passed" after Cypress auto-retried into the
                  // duplicate-exchange branch below (which never leaked into
                  // this DB, but the first attempt's exchange did).
                  expect(exchangeRes.body.exchange.conversation_partner_id).to.eq(mariaAuth.user.id)
                  expect(exchangeRes.body.exchange.first_message_id).to.be.a('string')

                  cy.apiRequest('GET', '/conversations', { token: mariaAuth.token }).then((conv) => {
                    expect(conv.status).to.eq(200)
                    expect(conv.body.success).to.eq(true)
                    const withJoao = (conv.body.data as Array<{ partner: { id: string }; can_message: boolean }>).find(
                      (c) => c.partner.id === joaoAuth.user.id
                    )
                    expect(withJoao, 'Maria should have conversation with João').to.exist
                    expect(withJoao!.can_message).to.eq(true)

                    // Cleanup: this exchange stays "pending" (never resolved),
                    // permanently blocking this (offered, requested) pair for
                    // future runs against the shared seed. Delete it (allowed
                    // while pending, by the initiator — both true here).
                    cy.apiRequest('DELETE', `/exchanges/${exchangeRes.body.exchange.id}`, {
                      token: joaoAuth.token,
                    }).its('status').should('eq', 200)
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

  it('CHAT-05: API: gate de envio retorna 422 sem troca ativa', () => {
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

  it('CHAT-06: usuário novo abre /chat sem conversas', () => {
    const email = `e2e.chat.empty.${Date.now()}@test.com`
    const password = 'password123'

    cy.request({
      method: 'POST',
      url: `${Cypress.env('apiUrl')}/register`,
      body: {
        name: 'E2E Empty Chat',
        email,
        password,
        password_confirmation: password,
      },
    }).then((reg) => {
      expect(reg.status).to.be.oneOf([200, 201])
    })

    cy.loginUi({ email, password })
    cy.intercept('GET', '**/api/conversations').as('conversations')

    cy.visit('/chat')
    cy.get('[data-testid="chat-page"]').should('be.visible')
    cy.wait('@conversations').its('response.statusCode').should('eq', 200)
    cy.get('[data-testid="conversation-list"]').should('be.visible')
    cy.get('[data-testid^="conversation-item-"]').should('not.exist')
    cy.get('[data-testid="composer-disabled-reason"]').should(
      'contain',
      'Selecione uma conversa para enviar mensagens.'
    )
  })

  it('CHAT-07: deep link com UUID inexistente mostra erro sem quebrar', () => {
    cy.loginUi(maria())
    cy.intercept('GET', '**/api/conversations/*').as('missingThread')

    cy.visit('/chat?user=00000000-0000-4000-8000-000000000099')
    cy.get('[data-testid="chat-page"]').should('be.visible')
    cy.wait('@missingThread').its('response.statusCode').should('eq', 404)

    cy.contains('Não foi possível carregar esta conversa.').should('be.visible')
    cy.get('[data-testid="thread-partner"]').should('not.exist')
  })
})
