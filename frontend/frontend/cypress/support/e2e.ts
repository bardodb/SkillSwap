import './commands'

Cypress.on('uncaught:exception', (err) => {
  // Bitwarden / extension noise and Echo reconnect races should not fail E2E
  if (
    err.message.includes('Receiving end does not exist') ||
    err.message.includes('ResizeObserver') ||
    err.message.includes('chrome-extension')
  ) {
    return false
  }
  return true
})

// Slow clicks in interactive mode (cy:open) so the UI is easier to follow.
// Headless `cypress run` stays at normal speed.
if (Cypress.config('isInteractive')) {
  const CLICK_DELAY_MS = 3000

  Cypress.Commands.overwrite('click', (originalFn, subject, options) => {
    return originalFn(subject, options).then(($el) => {
      // Use a plain Promise — do not call cy.* inside this .then()
      return new Cypress.Promise((resolve) => {
        setTimeout(() => resolve($el), CLICK_DELAY_MS)
      })
    })
  })
}
