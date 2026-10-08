#!/usr/bin/env node
/**
 * Local entry point of the visual tests.
 *
 *   npm run test:visual                          compare with the local baselines
 *   npm run test:visual -- --expect member,team  these pages may differ (announced change)
 *   npm run test:visual -- --expect all          every page may differ
 *   npm run test:visual:update                   refresh ALL baselines
 *   npm run test:visual:update -- member team    refresh only these baselines
 *
 * `--expect` is the local twin of the ticked boxes in the pull request
 * description: the listed pages are allowed to differ (their diffs are still
 * written to tests/e2e/.results), every other page must stay the same.
 */
const fs = require('fs');
const path = require('path');
const { spawnSync } = require('child_process');
const { visualPages } = require('./pr-template');

const RESULTS = path.join(__dirname, '../e2e/.results');

// Playwright names result folders after the test title with every character
// that is not a letter or digit replaced by a dash.
const slug = value => value.toLowerCase().replace(/[^a-z0-9]+/g, '-');

function parseArgs(argv, pages) {
  const names = visualPages(pages).map(page => page.name.toLowerCase());
  const known = new Set([...names, 'all']);
  const options = { update: false, expect: [], pages: [], rest: [] };
  let target = null;

  for (const arg of argv) {
    if (arg === '--update') {
      options.update = true;
      target = 'pages';
    } else if (arg === '--expect') {
      target = 'expect';
    } else if (arg.startsWith('--expect=')) {
      options.expect.push(
        ...arg
          .slice(9)
          .split(/[,\s]+/)
          .filter(Boolean)
      );
      target = null;
    } else if (arg.startsWith('-')) {
      options.rest.push(arg);
      target = null;
    } else if (target) {
      options[target].push(...arg.split(/[,\s]+/).filter(Boolean));
    } else {
      options.rest.push(arg);
    }
  }

  options.expect = options.expect.map(name => name.toLowerCase());
  options.pages = options.pages.map(name => name.toLowerCase());

  const unknown = [...options.expect, ...options.pages].filter(
    name => !known.has(name)
  );
  if (unknown.length) {
    throw new Error(
      `Unknown page${unknown.length > 1 ? 's' : ''}: ${unknown.join(', ')}\n` +
        `Valid names: all, ${names.join(', ')}`
    );
  }
  if (options.pages.includes('all')) {
    options.pages = [];
  }

  return options;
}

/** Pages with differences, from the result folders of the last run. */
function changedPages(pages, resultsDir = RESULTS) {
  if (!fs.existsSync(resultsDir)) {
    return [];
  }

  const bySlug = new Map(
    visualPages(pages).map(page => [slug(page.name), page.name])
  );
  const changed = new Map();

  for (const folder of fs.readdirSync(resultsDir)) {
    const match = folder.match(/^visual-(.+)-looks-the-same-(desktop|mobile)$/);
    const name = match && bySlug.get(match[1]);
    if (name) {
      changed.set(name, [...(changed.get(name) || []), match[2]]);
    }
  }

  // in the order of pages.js, not of the file system
  return visualPages(pages)
    .filter(page => changed.has(page.name))
    .map(page => ({ name: page.name, viewports: changed.get(page.name) }));
}

function main(argv, env) {
  const pages = require('../e2e/pages');
  let options;

  try {
    options = parseArgs(argv, pages);
  } catch (error) {
    console.error(error.message);
    return 2;
  }

  const args = ['playwright', 'test', 'visual', ...options.rest];
  if (options.update) {
    args.push('--update-snapshots');
  }
  if (options.pages.length) {
    // `\b` keeps `project` apart from `project-step` and `note` from `notes`
    args.push('-g', `\\b(${options.pages.join('|')}) looks the same`);
  }

  const expected = [
    ...new Set([
      ...options.expect,
      ...(env.MMH_VISUAL_EXPECTED || '')
        .toLowerCase()
        .split(/[,\s]+/)
        .filter(Boolean),
    ]),
  ];
  const result = spawnSync('npx', args, {
    stdio: 'inherit',
    env: { ...env, MMH_VISUAL_EXPECTED: expected.join(',') },
  });

  if (result.status !== 0 && !options.update) {
    const changed = changedPages(pages);
    if (changed.length) {
      const list = changed
        .map(c => `${c.name} (${c.viewports.join(', ')})`)
        .join(', ');
      console.error(`\nDifferences in: ${list}`);
      console.error(
        '\nIf that is intended:\n' +
          `  npm run test:visual -- --expect ${changed.map(c => c.name).join(',')}   (allow them for this run)\n` +
          `  npm run test:visual:update -- ${changed.map(c => c.name).join(' ')}   (accept them as the new baseline)\n` +
          'and tick the pages in the pull request description.\n' +
          'Diffs: tests/e2e/.results/'
      );
    }
  }

  return result.status ?? 1;
}

if (require.main === module) {
  process.exit(main(process.argv.slice(2), process.env));
}

module.exports = { parseArgs, changedPages, slug };
