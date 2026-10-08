/**
 * Pages covered by the smoke and visual tests.
 *
 * The paths refer to pages of the live content. In CI the tests render with the
 * head of web_content/production (PRs into main) or web_content/staging, and
 * pages that no longer exist are skipped (MMH_SKIP_MISSING=1), so keep the list
 * short and stable.
 *
 * detail: '/x/<y>' -> a detail page: the entry is a sample for ALL pages that match
 *                  the pattern (e.g. /team/christian stands for every team member)
 * label: '...'    -> shown in the visual checklist of the pull request template
 *                  (run `npm run pr-template` after changing the list)
 * visual: false  -> smoke only (content depends on time or external APIs)
 * hide: [css]    -> selectors hidden in screenshots (randomised content)
 * known: '...'   -> expected to fail for the given reason (test.fail); the test
 *                  turns red as soon as the page works, so the entry gets removed
 */
module.exports = [
  { name: 'home', label: 'Startseite', path: '/' },
  { name: 'projects', label: 'Projektübersicht', path: '/projects' },
  {
    name: 'project',
    label: 'Projektseiten',
    detail: '/projects/<projekt>',
    path: '/projects/01-goslar-app',
  },
  {
    name: 'project-step',
    label: 'Projektschritte',
    detail: '/projects/<projekt>/<schritt>',
    path: '/projects/01-goslar-app/version-4-3-0',
  },
  { name: 'project-archive', label: 'Projektarchiv', path: '/project-archive' },
  { name: 'notes', label: 'Tagebuch', path: '/notes' },
  {
    name: 'note',
    label: 'Tagebucheinträge',
    detail: '/notes/<eintrag>',
    path: '/notes/eine-neue-website',
    hide: ['.related-notes'], // shuffle() in templates/note.php
  },
  { name: 'events', path: '/events', visual: false },
  {
    name: 'newsletter_index',
    label: 'Newsletter-Übersicht',
    path: '/newsletter',
  },
  {
    name: 'newsletter',
    label: 'Newsletter-Ausgaben',
    detail: '/newsletter/<ausgabe>',
    path: '/newsletter/november-2025',
  },
  { name: 'team', label: 'Team', path: '/team' },
  {
    name: 'member',
    label: 'Teammitglieder',
    detail: '/team/<name>',
    path: '/team/christian',
  },
  { name: 'about', label: 'Über uns', path: '/uber-uns' },
  { name: 'contact', path: '/contact', visual: false },
  { name: 'informations', label: 'Mehr Informationen', path: '/informations' },
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
  { name: 'impressum', label: 'Impressum', path: '/impressum' },
  {
    name: 'not-allowed',
    label: 'Zugriff verweigert',
    path: '/not-allowed',
    status: 403,
  },
  {
    name: 'not-found',
    label: 'Fehlerseite (404)',
    path: '/not-found',
    status: 404,
  },
];
