const { test, expect } = require('@playwright/test');
const pages = require('./pages');
const { skipIfMissing } = require('./helpers');

// Failed third-party requests (maps, fonts, analytics) are not our bugs.
const isThirdPartyNoise = text =>
  /Failed to load resource|net::ERR_|mapbox|ERR_BLOCKED/i.test(text);

for (const page of pages) {
  test.describe(page.name, () => {
    test(`${page.path} renders cleanly`, async ({ page: browser, request }) => {
      test.fail(Boolean(page.known), page.known);
      await skipIfMissing(request, page);

      const errors = [];
      browser.on('pageerror', error =>
        errors.push(`pageerror: ${error.message}`)
      );
      browser.on('console', message => {
        if (message.type() === 'error' && !isThirdPartyNoise(message.text())) {
          errors.push(`console: ${message.text()}`);
        }
      });

      const response = await browser.goto(page.path, { waitUntil: 'load' });
      expect(response.status(), 'HTTP status').toBe(page.status ?? 200);

      // The document must be complete (a missing layout/foot leaves it open).
      const html = (await request.get(page.path)).text();
      expect((await html).trimEnd(), 'document ends with </html>').toMatch(
        /<\/html>$/
      );
      await expect(browser.locator('main'), 'one <main>').toHaveCount(1);

      expect(errors, 'no JS errors').toEqual([]);
    });
  });
}

test.describe('mobile navigation', () => {
  test.use({ viewport: { width: 390, height: 800 } });

  // These pages used to miss layout/foot, which carries the toggle script.
  for (const path of ['/team', '/newsletter', '/contact', '/projects']) {
    test(`menu toggle works on ${path}`, async ({ page }) => {
      await page.goto(path);
      const toggle = page.locator('#menu-toggle');
      await expect(toggle).toBeVisible();
      await expect(toggle).not.toHaveAttribute('aria-expanded', 'true');
      await toggle.click();
      await expect(toggle).toHaveAttribute('aria-expanded', 'true');
    });
  }
});
