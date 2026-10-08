const { test } = require('@playwright/test');

/**
 * In CI the pages are rendered with the live content of web_content, where
 * editors rename or remove pages. With MMH_SKIP_MISSING=1 a page that answers
 * 404 is skipped instead of failing the run. The home page and pages that are
 * expected to be 404 are never skipped, so a broken router still fails.
 */
async function skipIfMissing(request, page) {
  if (process.env.MMH_SKIP_MISSING !== '1') {
    return;
  }
  if (page.path === '/' || page.status === 404) {
    return;
  }

  const response = await request.get(page.path);
  test.skip(
    response.status() === 404,
    `${page.path} does not exist in this content`
  );
}

module.exports = { skipIfMissing };
