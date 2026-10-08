const test = require('node:test');
const assert = require('node:assert');
const { checklist, render, parse } = require('./pr-template');

const pages = [
  { name: 'home', path: '/', label: 'Startseite' },
  { name: 'newsletter_index', path: '/newsletter' },
  { name: 'events', path: '/events', visual: false },
  { name: 'rooms', path: '/rooms', known: 'unfinished' },
  { name: 'project-step', path: '/p/s' },
];

test('lists only pages that the visual test compares', () => {
  const text = checklist(pages);

  assert.match(text, /`all`/);
  assert.match(text, /- \[ \] `home` — Startseite \(`\/`\)/);
  assert.match(text, /`newsletter_index`/);
  assert.match(text, /`project-step`/);
  assert.doesNotMatch(text, /`events`/);
  assert.doesNotMatch(text, /`rooms`/);
});

test('render replaces the block and keeps the rest', () => {
  const template =
    'before\n<!-- visual-pages:start -->\nold\n<!-- visual-pages:end -->\nafter\n';
  const result = render(template, pages);

  assert.ok(result.startsWith('before\n'));
  assert.ok(result.endsWith('\nafter\n'));
  assert.doesNotMatch(result, /\nold\n/);
  assert.match(result, /`home`/);
  assert.strictEqual(render(result, pages), result, 'rendering is idempotent');
});

test('render fails without the block', () => {
  assert.throws(
    () => render('no markers', pages),
    /no <!-- visual-pages:start -->/
  );
});

test('parse returns the ticked pages of the generated block', () => {
  const body = `## Art\n- [x] Bug Fix\n- [x] Code ist formatiert (\`npm run format\`)\n\n${checklist(
    pages
  )
    .replace('- [ ] `home`', '- [x] `home`')
    .replace('- [ ] `project-step`', '- [X] `project-step`')}\n`;

  assert.deepStrictEqual(parse(body, pages), {
    ticked: ['home', 'project-step'],
    unknown: [],
  });
});

test('parse knows the all switch and underscores in names', () => {
  const body = checklist(pages)
    .replace('- [ ] `all`', '- [x] `all`')
    .replace('- [ ] `newsletter_index`', '- [x] `newsletter_index`');

  assert.deepStrictEqual(parse(body, pages).ticked, [
    'all',
    'newsletter_index',
  ]);
});

test('parse reports pages that no longer exist', () => {
  const body = `<!-- visual-pages:start -->\n- [x] \`gone-page\`\n- [x] \`home\`\n<!-- visual-pages:end -->`;

  assert.deepStrictEqual(parse(body, pages), {
    ticked: ['home'],
    unknown: ['gone-page'],
  });
});

test('parse ignores unticked boxes, CRLF and empty bodies', () => {
  assert.deepStrictEqual(parse(checklist(pages), pages).ticked, []);
  assert.deepStrictEqual(parse('', pages).ticked, []);
  assert.deepStrictEqual(
    parse(
      '<!-- visual-pages:start -->\r\n- [x] `home`\r\n<!-- visual-pages:end -->',
      pages
    ).ticked,
    ['home']
  );
});

test('parse reads a body without the block, but stays quiet about other boxes', () => {
  const result = parse('- [x] `home`\n- [x] `npm run format`', pages);

  assert.deepStrictEqual(result, { ticked: ['home'], unknown: [] });
});
