/// <reference types="cypress" />

describe('Security Smoke E2E', () => {
  const maria = () => Cypress.env('demoMaria') as { email: string; password: string }
  const joao = () => Cypress.env('demoJoao') as { email: string; password: string }
  const carlos = { email: 'carlos@skillswap.com', password: 'password123' }

  it('SEC-01: usuário não relacionado não lê exchange de outro par (404)', () => {
    cy.loginApi(carlos).then((carlosAuth) => {
      cy.loginApi(joao()).then((joaoAuth) => {
        cy.apiRequest('GET', '/exchanges', { token: joaoAuth.token }).then((list) => {
          // Some seeded exchanges are between João and Carlos, so we cannot
          // just take the first entry — pick one where Carlos is NOT the
          // partner to guarantee this is a genuinely unrelated pair.
          const unrelatedExchange = (
            list.body.data as Array<{ id: string; partner: { id: string } }>
          ).find((e) => e.partner.id !== carlosAuth.user.id)
          expect(unrelatedExchange, 'exchange de João não relacionada a Carlos').to.exist

          cy.apiRequest('GET', `/exchanges/${unrelatedExchange!.id}`, { token: carlosAuth.token }).then(
            (res) => {
              expect(res.status).to.eq(404)
              expect(res.body.success).to.eq(false)
              expect(res.body.message).to.eq('Not found')
            }
          )
        })
      })
    })
  })

  it('SEC-02: usuário não pode atualizar skill de outro dono (404)', () => {
    cy.loginApi(maria()).then((mariaAuth) => {
      cy.apiRequest('GET', '/my-skills', { token: mariaAuth.token }).then((mySkills) => {
        const mariaSkill = (mySkills.body?.data ?? mySkills.body)[0] as { id: string }
        expect(mariaSkill, 'skill da Maria').to.exist

        cy.loginApi(joao()).then((joaoAuth) => {
          cy.apiRequest('PUT', `/skills/${mariaSkill.id}`, {
            token: joaoAuth.token,
            body: { title: 'Hijacked by João' },
          }).then((res) => {
            expect(res.status).to.eq(404)
            expect(res.body.success).to.eq(false)
            expect(res.body.message).to.eq('Not found')
          })
        })
      })
    })
  })

  it('SEC-03: usuário sem relação não lê conversa entre outro par (404)', () => {
    cy.loginApi(maria()).then((mariaAuth) => {
      cy.loginApi(carlos).then((carlosAuth) => {
        cy.apiRequest('GET', `/conversations/${carlosAuth.user.id}`, { token: mariaAuth.token }).then(
          (res) => {
            expect(res.status).to.eq(404)
            expect(res.body.message).to.eq('Not found')
          }
        )
      })
    })
  })

  it('SEC-04: não-admin não pode criar categoria (403)', () => {
    cy.loginApi(maria()).then((mariaAuth) => {
      cy.apiRequest('POST', '/categories', {
        token: mariaAuth.token,
        body: { name: 'Categoria Hijack', description: 'não deveria existir' },
      }).then((res) => {
        expect(res.status).to.eq(403)
      })
    })
  })
})
