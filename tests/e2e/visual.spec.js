const { test, expect } = require('@playwright/test');
const pages = require('./pages');
const { skipIfMissing } = require('./helpers');

// 1x1 grey PNG. External images (e.g. the random picsum.photos fallback in
// sections/hero.php) would make every capture different, so they are replaced.
const PLACEHOLDER = Buffer.from(
  'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
  'base64'
);

// Screenshot baselines are stored per Playwright project and per platform
// (file name suffix), because font rendering differs between macOS and Linux.
for (const page of pages.filter(p => p.visual !== false && !p.known)) {
  test(`${page.name} looks the same`, async ({ page: browser, baseURL }) => {
    await skipIfMissing(browser.request, page);
    const ownHost = new URL(baseURL).host;
    await browser.route('**/*', route => {
      const request = route.request();
      const external = new URL(request.url()).host !== ownHost;
      // Maps animate and need a token: keep the container, drop the library.
      if (/mapbox/i.test(request.url())) {
        return route.abort();
      }
      if (external && request.resourceType() === 'image') {
        return route.fulfill({ contentType: 'image/png', body: PLACEHOLDER });
      }
      return route.continue();
    });

    await browser.goto(page.path, { waitUntil: 'networkidle' });

    if (page.hide?.length) {
      await browser.addStyleTag({
        content: `${page.hide.join(', ')} { display: none !important; }`,
      });
    }

    // Scroll through the page once so lazy content and scroll-driven scripts
    // (floating team strip, avatar reveal) settle before the capture.
    await browser.evaluate(async () => {
      const step = Math.max(window.innerHeight / 2, 200);
      for (let y = 0; y < document.body.scrollHeight; y += step) {
        window.scrollTo(0, y);
        await new Promise(r => setTimeout(r, 60));
      }
      window.scrollTo(0, 0);
    });
    await browser.waitForLoadState('networkidle');

    // Lazy images must be in before the full-page capture.
    await browser.evaluate(async () => {
      for (const img of document.images) {
        img.loading = 'eager';
      }
      await Promise.all(
        [...document.images].map(img =>
          img.complete ? null : new Promise(r => (img.onload = img.onerror = r))
        )
      );
    });

    await expect(browser).toHaveScreenshot(`${page.name}.png`, {
      fullPage: true,
      mask: [
        browser.locator('iframe'),
        browser.locator('.mapboxgl-map'),
        browser.locator('.debug-warning'),
        browser.locator('[data-visual-mask]'),
      ],
    });
  });
}
