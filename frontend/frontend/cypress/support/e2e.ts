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

  // Cypress command-log snapshot highlighter (_showSnapshotVue → highlightEl)
  // can throw when measuring padding/margin; not an app failure.
  if (err.message.includes('Element attr did not return a valid number')) {
    return false
  }

  return true
})

// Mild slowdown for primary button clicks in cy:open only (not input focus from .type()).
if (Cypress.config('isInteractive')) {
  const CLICK_DELAY_MS = 400

  Cypress.Commands.overwrite('click', (originalFn, subject, options) => {
    const el = subject?.[0] as HTMLElement | undefined
    const tag = el?.tagName?.toLowerCase()
    const isButtonLike =
      tag === 'button' || el?.getAttribute('role') === 'button' || tag === 'a'

    if (!isButtonLike) {
      return originalFn(subject, options)
    }

    return originalFn(subject, options).then(($el) => {
      return new Cypress.Promise((resolve) => {
        setTimeout(() => resolve($el), CLICK_DELAY_MS)
      })
    })
  })
}
