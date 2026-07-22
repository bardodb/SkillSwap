/// <reference types="cypress" />

describe('Páginas estáticas E2E', () => {
  const staticPages = [
    { path: '/faq', testId: 'faq-page', heading: 'FAQ' },
    { path: '/help-center', testId: 'help-center-page', heading: 'Central de Ajuda' },
    { path: '/contact', testId: 'contact-page', heading: 'Contato' },
    { path: '/privacy-policy', testId: 'privacy-policy-page', heading: 'Política de Privacidade' },
    { path: '/terms-of-use', testId: 'terms-of-use-page', heading: 'Termos de Uso' },
    { path: '/about', testId: 'about-page', heading: 'Sobre o' },
  ] as const

  it('STAT-01: home exibe marca SkillSwap e links de navegação', () => {
    cy.visit('/')
    cy.get('[data-testid="home-page"]').should('be.visible')
    cy.contains('SkillSwap').should('be.visible')
    cy.get('nav').within(() => {
      cy.contains('a', 'Home').should('be.visible')
      cy.contains('a', 'Habilidades').should('be.visible')
      cy.contains('a', 'Sobre').should('be.visible')
    })
  })

  it('STAT-02: páginas estáticas exibem título principal', () => {
    staticPages.forEach(({ path, testId, heading }) => {
      cy.visit(path)
      cy.get(`[data-testid="${testId}"]`).should('be.visible')
      cy.get('h1').should('contain', heading)
    })
  })

  it('STAT-03: ContactView exige campos obrigatórios no envio vazio', () => {
    cy.visit('/contact')
    cy.get('[data-testid="contact-page"]').should('be.visible')

    cy.get('[data-testid="contact-name"] input').should('have.attr', 'required')
    cy.get('[data-testid="contact-email"] input').should('have.attr', 'required')
    cy.get('[data-testid="contact-message"]').should('have.attr', 'required')

    // HTML5 validation blocks empty submit (form has no novalidate)
    cy.get('[data-testid="contact-submit"]').click()
    cy.url().should('include', '/contact')
    cy.get('[data-testid="contact-name"] input').should('have.value', '')
  })

  it('STAT-04: links do footer navegam para as páginas correspondentes', () => {
    const footerLinks: Array<{ text: string; path: string }> = [
      { text: 'Habilidades', path: '/skills' },
      { text: 'Sobre Nós', path: '/about' },
      { text: 'Contato', path: '/contact' },
      { text: 'Central de Ajuda', path: '/help-center' },
      { text: 'FAQ', path: '/faq' },
      { text: 'Política de Privacidade', path: '/privacy-policy' },
      { text: 'Termos de Uso', path: '/terms-of-use' },
    ]

    footerLinks.forEach(({ text, path }) => {
      cy.visit('/')
      cy.get('[data-testid="footer"]').should('be.visible').within(() => {
        cy.contains('a', text).click()
      })
      cy.url().should('include', path)
    })
  })
})
