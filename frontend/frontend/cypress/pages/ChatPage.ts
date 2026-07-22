class ChatPage {
  visit() {
    cy.visit('/chat')
    return this
  }

  visitWithPartner(userId: string) {
    cy.visit(`/chat?user=${userId}`)
    return this
  }

  assertLoaded() {
    cy.get('[data-testid="chat-page"]').should('be.visible')
    return this
  }

  openConversationByName(name: string) {
    cy.contains('[data-testid^="conversation-item-"]', name).click()
    return this
  }

  assertThreadPartner(name: string) {
    cy.get('[data-testid="thread-partner"]').should('contain', name)
    return this
  }

  typeMessage(text: string) {
    cy.get('[data-testid="message-input"] input').clear().type(text)
    return this
  }

  sendMessage() {
    cy.get('[data-testid="message-send"]').click()
    return this
  }

  assertMessageVisible(text: string) {
    cy.contains('[data-testid="chat-message"]', text).should('be.visible')
    return this
  }

  assertComposerDisabled(reason?: string) {
    if (reason) {
      cy.get('[data-testid="composer-disabled-reason"]').should('contain', reason)
    } else {
      cy.get('[data-testid="composer-disabled-reason"]').should('be.visible')
    }
    return this
  }

  assertComposerEnabled() {
    cy.get('[data-testid="message-input"] input').should('be.enabled')
    cy.get('[data-testid="composer-disabled-reason"]').should('not.exist')
    return this
  }
}

export default new ChatPage()
