// @ts-check
const { defineConfig, devices } = require('@playwright/test');

/**
 * End-to-end tests against a running instance (DDEV by default).
 *
 *   npm run test:e2e            smoke tests (status, console errors, HTML, nav)
 *   npm run test:visual         screenshot comparison against stored baselines
 *   npm run test:visual:update  (re)create baselines after an intended change
 *
 * Override the target with PLAYWRIGHT_BASE_URL. The instance must not lock
 * guests out (mmh.debugLock = false, set in the DDEV config).
 */
module.exports = defineConfig({
  testDir: './tests/e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  reporter: process.env.CI ? [['github'], ['html', { open: 'never' }]] : 'list',
  outputDir: 'tests/e2e/.results',
  snapshotPathTemplate: 'tests/e2e/__screenshots__/{projectName}/{arg}{ext}',
  use: {
    baseURL: process.env.PLAYWRIGHT_BASE_URL || 'https://gs-mmh-web.ddev.site',
    ignoreHTTPSErrors: true,
    locale: 'de-DE',
    timezoneId: 'Europe/Berlin',
    trace: 'retain-on-failure',
  },
  expect: {
    toHaveScreenshot: { maxDiffPixelRatio: 0.01, animations: 'disabled' },
  },
  projects: [
    {
      name: 'desktop',
      use: {
        ...devices['Desktop Chrome'],
        viewport: { width: 1440, height: 900 },
      },
    },
    {
      name: 'mobile',
      use: { ...devices['Pixel 7'] },
    },
  ],
});
