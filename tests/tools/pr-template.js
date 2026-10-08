#!/usr/bin/env node
/**
 * The "Visuelle Änderungen" checklist of the pull request template.
 *
 * tests/e2e/pages.js is the single source: every page that is compared by the
 * visual test gets a checkbox. A pull request that changes the look on purpose
 * ticks the affected pages; the CI job lets exactly those differ.
 *
 *   node tests/tools/pr-template.js --write   regenerate the block in the template
 *   node tests/tools/pr-template.js --check   fail when the template is out of date
 *   PR_BODY=... node tests/tools/pr-template.js --parse
 *                                              print the ticked pages (comma separated)
 */
const fs = require('fs');
const path = require('path');

const TEMPLATE = path.join(__dirname, '../../.github/pull_request_template.md');
const START = '<!-- visual-pages:start -->';
const END = '<!-- visual-pages:end -->';

// Same filter as tests/e2e/visual.spec.js
const visualPages = pages =>
  pages.filter(page => page.visual !== false && !page.known);

function checklist(pages) {
  const lines = ['- [ ] `all` — jede Seite darf sich ändern'];

  for (const page of visualPages(pages)) {
    const label = page.label ? ` — ${page.label}` : '';
    lines.push(`- [ ] \`${page.name}\`${label} (\`${page.path}\`)`);
  }

  return [
    START,
    '<!-- erzeugt aus tests/e2e/pages.js: npm run pr-template -->',
    ...lines,
    END,
  ].join('\n');
}

function block(text) {
  const start = text.indexOf(START);
  const end = text.indexOf(END);

  return start === -1 || end === -1 || end < start
    ? null
    : { start, end: end + END.length };
}

function render(template, pages) {
  const found = block(template);
  if (!found) {
    throw new Error(`The template has no ${START} ... ${END} block.`);
  }

  return (
    template.slice(0, found.start) +
    checklist(pages) +
    template.slice(found.end)
  );
}

/**
 * Ticked pages of a pull request description.
 * Only the generated block is read when it is there; the other checkboxes of
 * the template (`npm run format` ...) are not pages.
 */
function parse(body, pages) {
  const names = new Set(
    visualPages(pages).map(page => page.name.toLowerCase())
  );
  names.add('all');

  const found = block(body);
  const scope = found ? body.slice(found.start, found.end) : body;
  const ticked = [];
  const unknown = [];

  for (const line of scope.replace(/\r/g, '').split('\n')) {
    const match = line.match(/^\s*[-*]\s+\[[xX]\]\s+`([^`]+)`/);
    if (!match) {
      continue;
    }

    const name = match[1].trim().toLowerCase();
    if (names.has(name)) {
      ticked.push(name);
    } else if (found) {
      unknown.push(match[1]);
    }
  }

  return { ticked: [...new Set(ticked)], unknown };
}

function main(argv, env) {
  const pages = require('../e2e/pages');
  const mode = argv[0];

  if (mode === '--parse') {
    const { ticked, unknown } = parse(env.PR_BODY || '', pages);
    for (const name of unknown) {
      console.error(
        `::warning::Visual change "${name}" is not a page of tests/e2e/pages.js`
      );
    }
    console.log(ticked.join(','));
    return 0;
  }

  const current = fs.readFileSync(TEMPLATE, 'utf8');
  const next = render(current, pages);

  if (mode === '--write') {
    fs.writeFileSync(TEMPLATE, next);
    console.log('Updated .github/pull_request_template.md');
    return 0;
  }

  if (mode === '--check') {
    if (current !== next) {
      console.error(
        'The visual checklist in .github/pull_request_template.md does not match tests/e2e/pages.js.\n' +
          'Run `npm run pr-template` and commit the result.'
      );
      return 1;
    }
    console.log('PR template is up to date.');
    return 0;
  }

  console.error('Usage: pr-template.js --write | --check | --parse');
  return 2;
}

if (require.main === module) {
  process.exit(main(process.argv.slice(2), process.env));
}

module.exports = { checklist, render, parse, visualPages };
