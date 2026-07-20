import { defineConfig } from 'cypress'

export default defineConfig({
  e2e: {
    baseUrl: 'http://localhost:8080',
    specPattern: 'cypress/e2e/**/*.cy.ts',
    supportFile: 'cypress/support/e2e.ts',
    viewportWidth: 1280,
    viewportHeight: 720,
    video: false,
    screenshotOnRunFailure: true,
    defaultCommandTimeout: 10000,
    requestTimeout: 15000,
    responseTimeout: 15000,
    retries: {
      runMode: 2,
      openMode: 0,
    },
    env: {
      apiUrl: 'http://localhost:8000/api',
      demoJoao: {
        email: 'joao@skillswap.com',
        password: 'password123',
      },
      demoMaria: {
        email: 'maria@skillswap.com',
        password: 'password123',
      },
    },
    setupNodeEvents(on, config) {
      return config
    },
  },
})
