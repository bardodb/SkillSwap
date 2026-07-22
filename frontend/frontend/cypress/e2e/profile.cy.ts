/// <reference types="cypress" />

describe('Profile E2E', () => {
  const maria = () => Cypress.env('demoMaria') as { email: string; password: string }
  const joao = () => Cypress.env('demoJoao') as { email: string; password: string }

  it('PROF-01: Maria edita bio e localização no /profile', () => {
    const uniqueBio = `Bio E2E ${Date.now()}`
    const uniqueLocation = `Cidade E2E ${Date.now()}`

    cy.loginUi(maria())
    cy.intercept('PUT', '**/api/profile').as('updateProfile')

    cy.visit('/profile')
    cy.get('[data-testid="profile-page"]').should('be.visible')
    cy.get('[data-testid="profile-edit"]').click()

    cy.get('[data-testid="profile-bio"] input').clear().type(uniqueBio)
    cy.get('[data-testid="profile-location"] input').clear().type(uniqueLocation)
    cy.get('[data-testid="profile-edit"]').click()

    cy.wait('@updateProfile').its('response.statusCode').should('be.oneOf', [200, 201])

    cy.reload()
    cy.get('[data-testid="profile-page"]').should('contain', uniqueBio)
    cy.get('[data-testid="profile-page"]').should('contain', uniqueLocation)
  })

  it('PROF-02: usuário autenticado vê perfil público de outro usuário', () => {
    cy.loginApi(joao()).then((joaoAuth) => {
      cy.loginApi(maria()).then((mariaAuth) => {
        cy.loginUi(joao())
        cy.visit(`/users/${mariaAuth.user.id}/profile`)

        cy.get('[data-testid="user-profile-page"]').should('be.visible')
        cy.get('[data-testid="user-profile-page"]').should('contain', 'Maria')
      })
    })
  })

  it('PROF-03: UUID inexistente mostra estado de erro', () => {
    cy.loginUi(maria())
    cy.visit('/users/00000000-0000-4000-8000-000000000099/profile')

    cy.get('[data-testid="profile-error"]')
      .should('be.visible')
      .and('contain', 'Erro ao carregar perfil')
    cy.get('[data-testid="user-profile-page"]').should('not.exist')
  })

  it('PROF-04: API PUT /profile com email inválido retorna 422', () => {
    cy.loginApi(maria()).then((mariaAuth) => {
      cy.apiRequest('PUT', '/profile', {
        token: mariaAuth.token,
        body: { email: 'not-an-email' },
      }).then((res) => {
        expect(res.status).to.eq(422)
        expect(res.body.errors?.email ?? res.body.message).to.exist
      })
    })
  })
})
