const fs = require('fs');
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

/**
 * Looks that are changed on purpose.
 *
 * MMH_VISUAL_EXPECTED (from the `Visual-Change:` line of the pull request) is
 * `all` or a list of page names from pages.js, separated by commas or spaces.
 * Listed pages may differ from the base without failing the run; every other
 * page still has to look the same.
 */
function visualExpectation(name) {
  const listed = (process.env.MMH_VISUAL_EXPECTED || '')
    .toLowerCase()
    .split(/[,\s]+/)
    .filter(Boolean);

  return {
    all: listed.includes('all'),
    expected: listed.includes('all') || listed.includes(name.toLowerCase()),
  };
}

/** One line for the job summary of the workflow; printed to the console locally. */
function summarize(line) {
  if (process.env.GITHUB_STEP_SUMMARY) {
    fs.appendFileSync(process.env.GITHUB_STEP_SUMMARY, `${line}\n`);
  } else {
    console.log(line);
  }
}

module.exports = { skipIfMissing, visualExpectation, summarize };
