const test = require('node:test');
const assert = require('node:assert');
const fs = require('fs');
const os = require('os');
const path = require('path');
const { parseArgs, changedPages } = require('./visual');

const pages = [
  { name: 'home', path: '/' },
  { name: 'newsletter_index', path: '/newsletter' },
  { name: 'member', path: '/team/christian' },
  { name: 'events', path: '/events', visual: false },
  { name: 'rooms', path: '/rooms', known: 'unfinished' },
];

test('no arguments: compare everything, nothing expected', () => {
  assert.deepStrictEqual(parseArgs([], pages), {
    update: false,
    expect: [],
    pages: [],
    rest: [],
  });
});

test('--expect takes names separated by commas or spaces', () => {
  assert.deepStrictEqual(parseArgs(['--expect', 'home,Member'], pages).expect, [
    'home',
    'member',
  ]);
  assert.deepStrictEqual(parseArgs(['--expect=home member'], pages).expect, [
    'home',
    'member',
  ]);
});

test('--update without names refreshes everything, with names only these', () => {
  assert.deepStrictEqual(parseArgs(['--update'], pages).pages, []);
  assert.strictEqual(parseArgs(['--update'], pages).update, true);
  assert.deepStrictEqual(
    parseArgs(['--update', 'home', 'member'], pages).pages,
    ['home', 'member']
  );
  assert.deepStrictEqual(parseArgs(['--update', 'all'], pages).pages, []);
});

test('other options are passed on to Playwright', () => {
  const options = parseArgs(
    ['--project=mobile', '--expect', 'home', '--headed'],
    pages
  );

  assert.deepStrictEqual(options.rest, ['--project=mobile', '--headed']);
  assert.deepStrictEqual(options.expect, ['home']);
});

test('a typo is an error that lists the valid names', () => {
  assert.throws(
    () => parseArgs(['--expect', 'membre'], pages),
    /Unknown page: membre[\s\S]*Valid names: all, home, newsletter_index, member/
  );
});

test('pages the visual test does not compare are not valid names', () => {
  assert.throws(
    () => parseArgs(['--expect', 'events'], pages),
    /Unknown page: events/
  );
  assert.throws(
    () => parseArgs(['--update', 'rooms'], pages),
    /Unknown page: rooms/
  );
});

test('changedPages maps result folders back to page names', () => {
  const dir = fs.mkdtempSync(path.join(os.tmpdir(), 'visual-'));
  for (const folder of [
    'visual-newsletter-index-looks-the-same-desktop',
    'visual-newsletter-index-looks-the-same-mobile',
    'visual-member-looks-the-same-mobile',
    'smoke-home-renders-cleanly-desktop',
    'visual-gone-looks-the-same-desktop',
  ]) {
    fs.mkdirSync(path.join(dir, folder));
  }

  assert.deepStrictEqual(changedPages(pages, dir), [
    { name: 'newsletter_index', viewports: ['desktop', 'mobile'] },
    { name: 'member', viewports: ['mobile'] },
  ]);
  assert.deepStrictEqual(changedPages(pages, path.join(dir, 'missing')), []);
});
