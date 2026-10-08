/**
 * Pages covered by the smoke and visual tests.
 *
 * The paths refer to pages of the live content. In CI the tests render with the
 * head of web_content/production (PRs into main) or web_content/staging, and
 * pages that no longer exist are skipped (MMH_SKIP_MISSING=1), so keep the list
 * short and stable.
 *
 * visual: false  -> smoke only (content depends on time or external APIs)
 * hide: [css]    -> selectors hidden in screenshots (randomised content)
 * known: '...'   -> expected to fail for the given reason (test.fail); the test
 *                  turns red as soon as the page works, so the entry gets removed
 */
module.exports = [
  { name: 'home', path: '/' },
  { name: 'projects', path: '/projects' },
  { name: 'project', path: '/projects/01-goslar-app' },
  { name: 'project-step', path: '/projects/01-goslar-app/version-4-3-0' },
  { name: 'project-archive', path: '/project-archive' },
  { name: 'notes', path: '/notes' },
  {
    name: 'note',
    path: '/notes/eine-neue-website',
    hide: ['.related-notes'], // shuffle() in templates/note.php
  },
  { name: 'events', path: '/events', visual: false },
  { name: 'newsletter', path: '/newsletter' },
  { name: 'newsletter', path: '/newsletter/november-2025' },
  { name: 'team', path: '/team' },
  { name: 'member', path: '/team/christian' },
  { name: 'about', path: '/uber-uns' },
  { name: 'contact', path: '/contact', visual: false },
  { name: 'informations', path: '/informations' },
  {
    name: 'rooms',
    path: '/rooms',
    known: 'Rooms feature unfinished: HTTP 500 in bookingForm.php:80',
  },
  {
    name: 'room',
    path: '/rooms/empfangsraum',
    known: 'Rooms feature unfinished: HTTP 500',
  },
  { name: 'impressum', path: '/impressum' },
  { name: 'not-allowed', path: '/not-allowed', status: 403 },
  { name: 'not-found', path: '/not-found', status: 404 },
];
