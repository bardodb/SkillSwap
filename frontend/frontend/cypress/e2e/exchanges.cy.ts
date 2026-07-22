/// <reference types="cypress" />

describe('Exchanges E2E', () => {
  const maria = () => Cypress.env('demoMaria') as { email: string; password: string }
  const joao = () => Cypress.env('demoJoao') as { email: string; password: string }
  const carlos = { email: 'carlos@skillswap.com', password: 'password123' }

  function firstSkill(res: Cypress.Response<any>): { id: string; title?: string } | undefined {
    const list = res.body?.data ?? res.body
    return Array.isArray(list) ? list[0] : undefined
  }

  function profileSkills(res: Cypress.Response<any>): Array<{ id: string; title?: string }> {
    return res.body?.data?.skills ?? res.body?.skills ?? []
  }

  it('EXCH-01: João propõe troca no perfil da Maria via UI', () => {
    const proposalMsg = `Olá Maria! Proposta E2E ${Date.now()}`

    cy.loginApi(joao()).then((joaoAuth) => {
      cy.loginApi(maria()).then((mariaAuth) => {
        cy.apiRequest('GET', '/my-skills', { token: joaoAuth.token }).then((mySkills) => {
          cy.apiRequest('GET', `/users/${mariaAuth.user.id}/profile`, { token: joaoAuth.token }).then(
            (profile) => {
              cy.apiRequest('GET', '/exchanges', { token: joaoAuth.token }).then((exList) => {
                const offeredList = (mySkills.body?.data ?? mySkills.body) as Array<{
                  id: string
                  title?: string
                }>
                const requestedList = profileSkills(profile)
                expect(offeredList.length, 'João skills').to.be.greaterThan(0)
                expect(requestedList.length, 'Maria skills').to.be.greaterThan(0)

                // Avoid pairs that already have a live exchange (pending/accepted/scheduled)
                const used = new Set(
                  (
                    (exList.body?.data ?? []) as Array<{
                      status: string
                      offered_skill: { id: string }
                      requested_skill: { id: string }
                    }>
                  )
                    .filter((e) => ['pending', 'accepted', 'scheduled'].includes(e.status))
                    .map((e) => `${e.offered_skill.id}|${e.requested_skill.id}`)
                )

                let chosen: {
                  offered: { id: string; title?: string }
                  requested: { id: string; title?: string }
                } | null = null
                for (const offered of offeredList) {
                  for (const requested of requestedList) {
                    if (!used.has(`${offered.id}|${requested.id}`)) {
                      chosen = { offered, requested }
                      break
                    }
                  }
                  if (chosen) break
                }
                expect(chosen, 'par de skills livre para proposta UI').to.exist
                expect(chosen!.offered.title, 'offered title').to.be.a('string')
                expect(chosen!.requested.title, 'requested title').to.be.a('string')

                cy.loginUi(joao())
                cy.intercept('POST', '**/api/exchanges').as('createExchange')
                cy.on('window:alert', (text) => {
                  expect(text).to.match(/sucesso|enviada/i)
                })

                cy.visit(`/users/${mariaAuth.user.id}/profile`)
                cy.get('[data-testid="user-profile-page"]').should('be.visible')
                cy.get('[data-testid="exchange-request-btn"]').click()
                cy.get('[data-testid="exchange-modal"]').should('be.visible')

                cy.get('[data-testid="exchange-modal"]').within(() => {
                  cy.contains('h4', chosen!.offered.title as string).click()
                  cy.contains('h4', chosen!.requested.title as string).click()
                  cy.get('[data-testid="exchange-message"]').type(proposalMsg)
                  cy.get('[data-testid="exchange-submit"]').should('not.be.disabled').click()
                })

                // Happy path only — must create successfully so UI shows dashboard + Maria's chat
                cy.wait('@createExchange').then((interception) => {
                  const body = interception.response?.body
                  const createdId = body?.exchange?.id ?? body?.data?.id ?? body?.id
                  expect(interception.response?.statusCode).to.be.oneOf([200, 201])
                  expect(createdId, 'created exchange id').to.be.a('string')
                  cy.wrap(createdId).as('exchangeId')
                })
                cy.url().should('include', '/dashboard')
                cy.get('[data-testid="exchange-modal"]').should('not.exist')

                cy.loginUi(maria())
                cy.intercept('GET', '**/api/conversations').as('conversations')
                cy.intercept('GET', '**/api/conversations/*').as('thread')
                cy.visit('/chat')
                cy.wait('@conversations')
                cy.contains('[data-testid^="conversation-item-"]', 'João Silva').click()
                cy.wait('@thread').its('response.statusCode').should('eq', 200)
                cy.get('[data-testid="thread-partner"]').should('contain', 'João')
                cy.contains('[data-testid="chat-message"]', proposalMsg).should('be.visible')

                // Cleanup: this exchange stays "pending" (never resolved), which
                // permanently blocks this (offered, requested) skill pair for future
                // runs against the shared seed — the 3x3=9 João↔Maria pool exhausts
                // after a handful of runs otherwise. Delete it (only allowed while
                // pending and by the initiator — both true here).
                cy.get('@exchangeId').then((exchangeId) => {
                  cy.apiRequest('DELETE', `/exchanges/${exchangeId}`, {
                    token: joaoAuth.token,
                  }).its('status').should('eq', 200)
                })
              })
            }
          )
        })
      })
    })
  })

  it('EXCH-02: API duplicate — segunda proposta com mesmas skills retorna 400/422', () => {
    cy.loginApi(joao()).then((joaoAuth) => {
      cy.loginApi(carlos).then((carlosAuth) => {
        cy.apiRequest('GET', '/my-skills', { token: joaoAuth.token }).then((mySkills) => {
          cy.apiRequest('GET', `/users/${carlosAuth.user.id}/profile`, { token: joaoAuth.token }).then(
            (profile) => {
              const offered = firstSkill(mySkills)
              const requested = profileSkills(profile)[0]
              expect(offered?.id).to.be.a('string')
              expect(requested?.id).to.be.a('string')
              expect(offered?.title, 'offered title for UI').to.be.a('string')
              expect(requested?.title, 'requested title for UI').to.be.a('string')

              const payload = {
                receiver_id: carlosAuth.user.id,
                offered_skill_id: offered!.id,
                requested_skill_id: requested!.id,
                message: `E2E duplicate exchange ${Date.now()}`,
              }

              cy.apiRequest('POST', '/exchanges', { token: joaoAuth.token, body: payload }).then((first) => {
                expect(first.status, 'first exchange').to.be.oneOf([200, 201, 400, 422])

                cy.apiRequest('POST', '/exchanges', { token: joaoAuth.token, body: payload }).then((second) => {
                  expect(second.status, 'duplicate exchange').to.be.oneOf([400, 422])

                  // UI: same pair via modal so AUT is visible and duplicate surfaces in the browser
                  cy.loginUi(joao())
                  cy.intercept('POST', '**/api/exchanges').as('dupExchangeUi')
                  cy.on('window:alert', (text) => {
                    expect(text).to.match(/já|existe|erro|não|pendente|troca/i)
                  })
                  cy.visit(`/users/${carlosAuth.user.id}/profile`)
                  cy.get('[data-testid="user-profile-page"]').should('be.visible')
                  cy.get('[data-testid="exchange-request-btn"]').click()
                  cy.get('[data-testid="exchange-modal"]').should('be.visible')
                  cy.get('[data-testid="exchange-modal"]').within(() => {
                    cy.contains('h4', offered!.title as string).click()
                    cy.contains('h4', requested!.title as string).click()
                    cy.get('[data-testid="exchange-message"]').type(`UI duplicate ${Date.now()}`)
                    cy.get('[data-testid="exchange-submit"]').should('not.be.disabled').click()
                  })
                  cy.wait('@dupExchangeUi').its('response.statusCode').should('be.oneOf', [400, 422])
                  cy.get('[data-testid="exchange-modal"]').should('be.visible')
                })
              })
            }
          )
        })
      })
    })
  })

  it('EXCH-03: POST /exchanges sem token retorna 401', () => {
    cy.visit('/login')
    cy.get('[data-testid="login-email"]').should('be.visible')
    cy.apiRequest('POST', '/exchanges', {
      body: {
        receiver_id: '00000000-0000-4000-8000-000000000001',
        offered_skill_id: '00000000-0000-4000-8000-000000000002',
        requested_skill_id: '00000000-0000-4000-8000-000000000003',
        message: 'Sem autenticação',
      },
    }).then((res) => {
      expect(res.status).to.eq(401)
      cy.get('[data-testid="login-email"]').should('be.visible')
    })
  })

  it('EXCH-04: POST /exchanges com skill ids inválidos retorna 422', () => {
    cy.loginApi(joao()).then((joaoAuth) => {
      cy.loginApi(maria()).then((mariaAuth) => {
        cy.loginUi(joao())
        cy.visit(`/users/${mariaAuth.user.id}/profile`)
        cy.get('[data-testid="user-profile-page"]').should('be.visible')
        cy.get('[data-testid="exchange-request-btn"]').should('be.visible')

        cy.apiRequest('POST', '/exchanges', {
          token: joaoAuth.token,
          body: {
            receiver_id: mariaAuth.user.id,
            offered_skill_id: '00000000-0000-4000-8000-000000000099',
            requested_skill_id: '00000000-0000-4000-8000-000000000098',
            message: 'Skills inexistentes para validação E2E',
          },
        }).then((res) => {
          expect(res.status).to.eq(422)
          cy.get('[data-testid="user-profile-page"]').should('be.visible')
        })
      })
    })
  })

  it('EXCH-05: receptor vê conversa com can_message true após troca seedada', () => {
    cy.loginApi(joao()).then((joaoAuth) => {
      cy.loginApi(maria()).then((mariaAuth) => {
        cy.apiRequest('GET', '/conversations', { token: mariaAuth.token }).then((conv) => {
          expect(conv.status).to.eq(200)
          expect(conv.body.success).to.eq(true)

          const withJoao = (
            conv.body.data as Array<{ partner: { id: string; name: string }; can_message: boolean }>
          ).find((c) => c.partner.id === joaoAuth.user.id)

          expect(withJoao, 'Maria deve ter conversa com João (seed)').to.exist
          expect(withJoao!.can_message).to.eq(true)

          cy.loginUi(maria())
          cy.intercept('GET', '**/api/conversations').as('conversations')
          cy.intercept('GET', '**/api/conversations/*').as('thread')
          cy.visit('/chat')
          cy.wait('@conversations')
          cy.get('[data-testid="chat-page"]').should('be.visible')
          cy.contains('[data-testid^="conversation-item-"]', 'João Silva').click()
          cy.wait('@thread').its('response.statusCode').should('eq', 200)
          cy.get('[data-testid="thread-partner"]').should('contain', 'João')
          cy.get('[data-testid="message-input"] input').should('be.enabled')
        })
      })
    })
  })

  it('EXCH-06: Maria responde João no chat e aceita a troca pendente', () => {
    const reply = `Aceito a troca E2E ${Date.now()}`

    cy.loginApi(joao()).then((joaoAuth) => {
      cy.loginApi(maria()).then((mariaAuth) => {
        cy.apiRequest('GET', '/my-skills', { token: joaoAuth.token }).then((joaoSkills) => {
          cy.apiRequest('GET', `/users/${mariaAuth.user.id}/profile`, { token: joaoAuth.token }).then(
            (profile) => {
              const offeredList = (joaoSkills.body?.data ?? joaoSkills.body) as Array<{ id: string }>
              const requestedList = profileSkills(profile)
              expect(offeredList.length).to.be.greaterThan(0)
              expect(requestedList.length).to.be.greaterThan(0)

              // Try skill pairs until a pending exchange is created (skip duplicates)
              const tryCreate = (oi: number, ri: number): void => {
                if (oi >= offeredList.length) {
                  throw new Error('Não foi possível criar exchange pendente João→Maria')
                }
                if (ri >= requestedList.length) {
                  tryCreate(oi + 1, 0)
                  return
                }

                cy.apiRequest('POST', '/exchanges', {
                  token: joaoAuth.token,
                  body: {
                    receiver_id: mariaAuth.user.id,
                    offered_skill_id: offeredList[oi].id,
                    requested_skill_id: requestedList[ri].id,
                    message: `Proposta E2E para Maria responder ${Date.now()}`,
                  },
                }).then((created) => {
                  if (created.status !== 200 && created.status !== 201) {
                    tryCreate(oi, ri + 1)
                    return
                  }

                  const exchangeId =
                    created.body.exchange?.id ?? created.body.data?.id ?? created.body.id
                  expect(exchangeId, 'exchange id').to.be.a('string')

                  cy.loginUi(maria())
                  cy.intercept('GET', '**/api/conversations').as('conversations')
                  cy.intercept('GET', '**/api/conversations/*').as('thread')
                  cy.intercept('POST', '**/api/messages').as('sendMessage')

                  cy.visit('/chat')
                  cy.wait('@conversations')
                  cy.contains('[data-testid^="conversation-item-"]', 'João Silva').click()
                  cy.wait('@thread').its('response.statusCode').should('eq', 200)

                  cy.get('[data-testid="thread-partner"]').should('contain', 'João')
                  cy.get('[data-testid="chat-message"]').should('have.length.at.least', 1)

                  cy.get('[data-testid="message-input"] input').clear().type(reply)
                  cy.get('[data-testid="message-send"]').click()
                  cy.wait('@sendMessage').its('response.statusCode').should('be.oneOf', [200, 201])
                  cy.get('[data-testid="chat-message"]').should('contain', reply)

                  cy.apiRequest('PUT', `/exchanges/${exchangeId}`, {
                    token: mariaAuth.token,
                    body: { status: 'accepted' },
                  }).then((accept) => {
                    expect(accept.status).to.eq(200)
                    expect(accept.body.success).to.eq(true)
                    expect(accept.body.exchange.status).to.eq('accepted')

                    // Cleanup: "accepted" is a live status and would permanently
                    // block this skill pair for future runs (destroy() only allows
                    // deleting while "pending"). Cancel it — allowed by either
                    // participant regardless of current status.
                    cy.apiRequest('PUT', `/exchanges/${exchangeId}`, {
                      token: joaoAuth.token,
                      body: { status: 'cancelled' },
                    }).its('status').should('eq', 200)
                  })
                })
              }

              tryCreate(0, 0)
            }
          )
        })
      })
    })
  })

  it('EXCH-07: initiator não pode aceitar a troca (403)', () => {
    cy.loginApi(joao()).then((joaoAuth) => {
      cy.apiRequest('GET', '/exchanges', { token: joaoAuth.token }).then((list) => {
        expect(list.status).to.eq(200)
        const asInitiator = (
          list.body.data as Array<{ id: string; status: string; is_initiator: boolean }>
        ).find((e) => e.is_initiator === true && ['pending', 'accepted'].includes(e.status))

        expect(asInitiator, 'troca em que João é initiator').to.exist

        cy.apiRequest('PUT', `/exchanges/${asInitiator!.id}`, {
          token: joaoAuth.token,
          body: { status: 'accepted' },
        }).then((res) => {
          expect(res.status).to.eq(403)
        })
      })
    })
  })

  it('EXCH-08: receptor rejeita troca pendente e status reflete no dashboard', () => {
    cy.loginApi(joao()).then((joaoAuth) => {
      cy.loginApi(maria()).then((mariaAuth) => {
        cy.createFreeExchange({
          initiatorToken: joaoAuth.token,
          receiverId: mariaAuth.user.id,
          receiverToken: mariaAuth.token,
          message: `EXCH-08 reject ${Date.now()}`,
        }).then((created) => {
          const exchangeId = created.body.exchange?.id ?? created.body.data?.id ?? created.body.id

          cy.apiRequest('PUT', `/exchanges/${exchangeId}`, {
            token: mariaAuth.token,
            body: { status: 'rejected' },
          }).then((rejected) => {
            expect(rejected.status).to.eq(200)
            expect(rejected.body.exchange.status).to.eq('rejected')

            cy.loginUi(maria())
            cy.intercept('GET', '**/api/exchanges').as('exchanges')
            cy.visit('/dashboard')
            cy.wait('@exchanges')
            cy.get('[data-testid="dashboard-exchange-item"]')
              .contains('[data-testid="dashboard-exchange-item"]', 'João')
              .should('contain', 'Rejeitada')
          })
        })
      })
    })
  })

  it('EXCH-09: troca accepted pode ser marcada completed e dashboard reflete', () => {
    cy.loginApi(joao()).then((joaoAuth) => {
      cy.loginApi(maria()).then((mariaAuth) => {
        cy.createFreeExchange({
          initiatorToken: joaoAuth.token,
          receiverId: mariaAuth.user.id,
          receiverToken: mariaAuth.token,
          message: `EXCH-09 complete ${Date.now()}`,
        }).then((created) => {
          const exchangeId = created.body.exchange?.id ?? created.body.data?.id ?? created.body.id

          cy.apiRequest('PUT', `/exchanges/${exchangeId}`, {
            token: mariaAuth.token,
            body: { status: 'accepted' },
          }).then((accepted) => {
            expect(accepted.status).to.eq(200)

            cy.apiRequest('PUT', `/exchanges/${exchangeId}`, {
              token: joaoAuth.token,
              body: { status: 'completed' },
            }).then((completed) => {
              expect(completed.status).to.eq(200)
              expect(completed.body.exchange.status).to.eq('completed')

              cy.loginUi(maria())
              cy.intercept('GET', '**/api/exchanges').as('exchanges')
              cy.visit('/dashboard')
              cy.wait('@exchanges')
              cy.contains('[data-testid="dashboard-exchange-item"]', 'João').should('contain', 'Concluída')
            })
          })
        })
      })
    })
  })

  it('EXCH-10: usuário sem participar da troca não pode alterar status (403/404)', () => {
    cy.loginApi(joao()).then((joaoAuth) => {
      cy.loginApi(maria()).then((mariaAuth) => {
        cy.createFreeExchange({
          initiatorToken: joaoAuth.token,
          receiverId: mariaAuth.user.id,
          receiverToken: mariaAuth.token,
          message: `EXCH-10 stranger ${Date.now()}`,
        }).then((created) => {
          const exchangeId = created.body.exchange?.id ?? created.body.data?.id ?? created.body.id

          cy.loginApi(carlos).then((carlosAuth) => {
            cy.apiRequest('PUT', `/exchanges/${exchangeId}`, {
              token: carlosAuth.token,
              body: { status: 'accepted' },
            }).then((res) => {
              expect(res.status).to.eq(404)

              // Cleanup: Carlos's attempt failed (404, IDOR-scoped), so the
              // exchange is still "pending" — delete it to free the pair
              // instead of leaking it permanently into the shared seed.
              cy.apiRequest('DELETE', `/exchanges/${exchangeId}`, {
                token: joaoAuth.token,
              }).its('status').should('eq', 200)
            })
          })
        })
      })
    })
  })
})
