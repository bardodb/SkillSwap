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
