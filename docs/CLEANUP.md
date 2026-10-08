# Aufräumaktion GS_MMH_WEB

Lebendes Dokument. Nach jeder erledigten Aufgabe wird die Checkbox gesetzt und der PR/Commit verlinkt. Branch: `refractor/clean_up`. Stand der Bestandsaufnahme: 2026-10-08.

## Ziele
1. `content/` nicht mehr als Submodul deployen (git-content hält die Instanzen aktuell).
2. Eine einheitliche Struktur für Funktionen, Klassen, Routen, Hooks.
3. Doppelstrukturen (Cards, Heroes, Modals ...) abbauen.
4. Tests (PHPUnit + Playwright) als Sicherheitsnetz, **bevor** umgebaut wird.

## Leitlinien
- Betriebsnotwendige Logik gehört ins Hauptrepo, als lokales Plugin `site/plugins/mmh-site/` (`src/` mit Namespace `Mmh\Site\`, PSR-4).
- Eigenständige Module bleiben separate Plugins: Raumbuchung, App-Auslieferung (`/app/*`, `api/*-cover`, `latest-update`, `highlights`, Horoskope, Ferienpass), Signage, Newsletter-Versand. Sie hängen nicht von Site-Funktionen ab.
- Jede Route, jeder Hook, jede Funktion hat genau eine Quelle.
- `site/config/` enthält nur Optionen, `controllers/` nur echte Controller, `snippets/` nur Markup.
- Plugin-Routen werden **vor** `site/config/routes.php` gematcht.

## Status

| Phase | Inhalt | Status | PR |
|---|---|---|---|
| 0 | Dieses Dokument | erledigt | |
| 1 | Sofort-Fixes & Hygiene | in Arbeit (Routen, Hooks, Kleinbugs erledigt) | |
| 2 | Test-Fundament (PHPUnit, Playwright, Lint, CI) | offen | |
| 3 | Content-Submodul entfernen | offen | |
| 4 | Logik-Struktur (`mmh-site` + Module) | offen | |
| 5 | Komponenten konsolidieren | offen | |
| 6 | Konventionen festschreiben | offen | |

---

## Phase 1: Sofort-Fixes & Hygiene

### Routen und Hooks
- [x] Plugin-Kopien von `newsletter.xml`, `/app/(:any)`, `/app/ferienpass*` aus `gs-mmh-web-plugin/index.php` entfernen (sie liefen zuerst und waren defekt: Counter zählte nicht, `rss_feed`-Snippet fehlte). Erledigt; Tracker in `routes.php` legt die Tabelle jetzt selbst an. **Plugin-Änderung braucht eigenen Commit im Submodul.**
- [x] `page.changeStatus:after` nur noch einmal (`config/hooks.php` + Plugin); Template-Check `'notes'` → `'note'`
- [ ] `DreamForm::register` nur einmal aufrufen (Plugin: Top-Level und `system.loadPlugins:after`)
- [ ] `booking-request.json` (E) entfernen, das Formular nutzt `api/booking/submit`
- [ ] Zwei Newsletter-HTML-Routen (`api/newsletter-html/(:all).html`, `api/newsletter/(:any).html`) zusammenlegen
- [x] Kommentar „before /app tracker so it wins“ in `routes.php` korrigieren oder obsolet machen

### Bugs
- [x] 7 Templates ohne `layout/foot`: contact, newsletter, newsletters, newsletter-unsubscribe, team, theme, themes
- [x] `horoskope/card.php`: `'rammelsberg'` vs Datei `hk_rammelberg.png`
- [x] `statusheader.php`: Literal `'test'`
- [x] `api.php`: Status `44` statt `404`
- [x] `public/index.php`: `'./assets'` (fehlender Slash)
- [x] `home.php`: `block"`-Typo
- [ ] `sections/hero.php`: picsum-Fallback in Production
- [ ] `rooms.php` / `room.php`: `cover()->toFile()` auf bereits aufgelöstem File (Hero-Bild rendert vermutlich nie)
- [ ] `ferienpass/events.php` / `event_random.php`: `sort_by_start` ohne Guard doppelt deklariert
- [ ] `utilities/content-card.php`: `isset($item)` nach Zugriff (wird mit dem Löschen erledigt)

### Sicherheit
- [x] `config.localhost.php`: Remote-DB-Passwort entfernt (Env-Variablen `MMH_DB_*`). **Offen für dich: Passwort rotieren, es steht in der Git-Historie**
- [x] Committete `site/sessions/*.sess` aus dem Repo, `/site/sessions/*` in `.gitignore` (`site/cache` war schon ignoriert)
- [~] git-content `cronHooksSecret`: Option liest `MMH_GIT_CONTENT_SECRET`. **Offen: Variable auf den Instanzen setzen und Cron-Aufrufe um `?secret=` ergänzen**
- [x] `member.php`: `addslashes()`/`setHTML` durch `json_encode` + `setDOMContent` ersetzt (XSS)
- [x] `MemberPage::description()`: loripsum-Abruf entfernt
- [ ] ~~`config.php`: Default `debug => true` ändern~~ **Nicht ändern:** `layout/head.php` sperrt Instanzen mit `debug => true` für Gäste (so ist Staging geschützt). Stattdessen in Phase 3 eine explizite Staging-Config anlegen und die Sperre an eine eigene Option koppeln
- [ ] Staging: eigene Config anlegen (siehe Phase 3)

### Toter Code
- [x] Dateien: `debug_test.php` gelöscht; `data_format.txt` → `docs/app-card-format.txt` (App-Card-Format, ist Doku). `DEBUG_SETUP.md` bleibt (README verlinkt sie)
- [ ] Templates: `calendar.php`, `machmit.php`, `app_performance.php` (Site und Plugin), Plugin-`templates/` und `controllers/`
- [x] Snippets gelöscht: `utilities/content-card.php`, `newsletter/blogEntries.php`, `projects/projectTimelineEntry.php`, `integrations/performace*`, `ferienpass/csv_helper.php`
- [ ] Snippets offen: `layout/mainLayout.php` (hängt an calendar/machmit); `blocks/line.php` ist leer, aber **bewusst nicht löschen**: der leere Override unterdrückt das Kirby-Default-`<hr>`, Löschen würde Trennlinien sichtbar machen. Erst klären, ob das gewollt ist
- [x] `controllers/site.php` (leer) gelöscht
- [ ] Controller: `about.php` = `team.php` zusammenlegen; `?>` in `error.php`
- [x] `getArchivedProjects` gelöscht
- [ ] Funktionen offen: `getColor` (Plugin, in dessen README dokumentiert), `scheduleLabel` (Plugin-Blockmethode), `mmhApiCoverSvgUrl` (nur Definition). **`mmhOvedaEventClientPayload` ist in Gebrauch** (`events-api.php:453`), nicht löschen
- [ ] CSS: `.c-card*` in `designer.css`, `cta.css` (leer), `layout/content.css`, `newsletter-unsubscribe.css`, `.project-teaser-tags`, `.content-card--imageRight`
- [ ] Debug-Reste: `console.log` (bookingForm, newsletterTeaser, contact-map, signage screen), auskommentierte `var_dump`, auskommentiertes Mail-Handling in `hooks.php`
- [ ] Signage-Plugin: totes `'panel' => ['js','css']`, Route `signage/assets/js/`

### Fehlende Referenzen klären
- [ ] `api/rooms/availability*.json` benötigt `nextcloudCalendarIntegration.php` (fehlt)
- [ ] `dreamform/forms`-Snippet in `project.php`
- [ ] 5 Signage-Snippets (`signage/player|standby|slide-*`) fehlen
- [ ] Blocks: `testimonial.yml` ↔ `testimonials.yml`; `box` nicht registriert, aber referenziert; `faq2`; `searchbar` im falschen Ordner
- [ ] `plugins/kirby3-dotenv/global.php` wird von der Prod-Config verlangt, ist aber nicht im Repo
- [ ] `kirby`: verwaister Submodul-Pointer → `git rm --cached kirby`
- [ ] Event-Model fehlt (`events/(:num)` setzt `model => event`)
- [ ] Content ohne Blueprint/Template: `3_informations`, `components/page.txt`, `_drafts/...`, `timeline_entries.txt`; `content/rooms/` hat `default.txt` und `rooms.txt`

---

## Phase 2: Test-Fundament

### PHPUnit
- [ ] `phpunit` als Dev-Dependency, `tests/Unit`, `tests/bootstrap.php`, `.ddev/commands/web/test`
- [ ] Oveda-Parsing (`events-api.php`, `oveda-event.php`), mit JSON-Fixtures
- [ ] `mmhColorContrast`, `mmhTimestampValue`
- [ ] `mmhStylesheetBundle` / `mmhRebaseStylesheetUrls` (Fixture-CSS-Baum)
- [ ] `mmhApiHexToRgb`, `mmhApiWrapSvgText`, `mmhApiXmlEscape`
- [ ] Newsletter-HTML-Transforms (Snapshot)
- [ ] `ProjectPage::effectiveProjectStatus` / `latestStepDate`, `isTimedContentVisible`
- [ ] Horoskop: Parsing in pure Funktion extrahieren und testen (Sortierung, Attribute 0–8)

### Playwright
- [ ] `tests/visual/`, `playwright.config.ts`, `baseURL` per Umgebungsvariable
- [ ] Fester Test-Content (Tag `test-fixture` in `web_content`); Oveda, n8n, Mapbox mocken/maskieren
- [ ] Seiten: home, projects, project (mit/ohne Projektfarbe, Highlights), theme(s), notes, note, rooms, room, events, event, newsletters, newsletter, über-uns, member, contact, error, sitemap, `/app/horoskope`
- [ ] Breakpoints 390 / 768 / 1040 / 1440; Interaktionen: Mobile-Nav, Modals, Kalender-Overlay
- [ ] Smoke-Checks: Status, Konsolenfehler, geschlossenes HTML
- [ ] Baseline-Screenshots erzeugen (vor Phase 4/5)

### Lint & CI
- [ ] Stylelint: 14 Fehler (`grid.css`, `eventsList.css`, Leerzeilen)
- [ ] ESLint: Browser-Globals, `lightbox/` ignorieren, `lint:js` ohne `--fix` aufs ganze Repo
- [ ] lint-staged-Globs (JS in `public/assets/js` wird nie geprüft; PHP fehlt)
- [ ] PHP-CS-Fixer / PHPCS-Excludes vereinheitlichen (Signage- und Web-Plugin inkonsistent)
- [ ] `.editorconfig`; toten Prettier-PHP-Override entfernen
- [ ] Optional: PHPStan Level 1–3
- [ ] `.github/workflows/ci.yml`: lint, phpunit (PHP 8.4), visual

---

## Phase 3: Content-Submodul entfernen

Deployment läuft über eine **externe Pipeline** (nicht im Repo). Zu klären: checkt sie Submodule rekursiv aus, synchronisiert sie `content/`, schließt sie `content/` aus?

- [ ] Pipeline prüfen und anpassen (`content/` nie überschreiben/löschen)
- [ ] Pro Instanz (erst Staging, dann Prod): Backup `content/`, `git -C content status` sauber, `content/` in eigenständigen Clone überführen (`git submodule absorbgitdirs`/Re-Clone), Submodul-Eintrag aus `.git/config`
- [ ] Hauptrepo: `git rm --cached content`, `.gitmodules` bereinigen, tote DreamForm-`!`-Regeln in `.gitignore` löschen
- [ ] git-content konfigurieren (`pull`/`push`, `cronHooksSecret`), Cron dokumentieren
- [ ] README (Zeile 22 und 85) und `DEVELOPMENT_SETUP.md`: `content/` als eigener Clone; optional `.ddev/commands/host/content-sync`
- [ ] Staging-Config anlegen
- [ ] Verifikation auf Staging: Panel-Speichern → Commit auf richtigem Branch; erneuter Deploy lässt `content/` unverändert

Hinweis: Der Pointer ist veraltet (pinnt `1e85a2a`, lokal `staging@955ef3c`). Ein `submodule update` auf einem Server würde den Content zurücksetzen.

---

## Phase 4: Logik-Struktur

### Modul-Zuordnung (Vorschlag, vor Umsetzung zu bestätigen)

| Modul | Inhalt |
|---|---|
| `mmh-site` (neu, Hauptrepo) | Projekt-Routen/Redirects (`MMH_MERGED_PROJECTS`), `events/(:num)`, `events.json`, `newsletter.xml`, sitemap/robots, SEO, Status-/Archiv-Hooks, `isTimedContentVisible`, Stylesheet-Bundle, `mmhBackLink`, `mmhAvatarImage`, `mmhColorContrast`, Oveda-Bibliothek, Blocks |
| App-Plugin (eigenständig) | `/app/*` inkl. Counter, Horoskope, Ferienpass, `api/latest-update`, `api/highlights`, `api/*-cover` |
| Raumbuchung (eigenständig) | `bookingRequestHandler`, `googleCalendarIntegration`, Booking-Routen und -Hooks, Mail-Templates, Availability-API |
| Newsletter (eigenständig) | `newsletter-email.php`, `NewsletterRecipients`, Panel-Areas, `api/newsletter-html` |
| `gs-mmh-signage-plugin` | bleibt separat; Namespace/Autoload korrigieren |
| `gs-mmh-web-plugin` | schrumpfen (Forms/Submissions) oder auflösen, Entscheidung nach den Umzügen |

### Aufgaben
- [ ] `mmh-site` anlegen (`index.php`, `src/`, PSR-4, `.gitignore`-Ausnahme)
- [ ] Bibliotheken aus `controllers/` verschieben: `api-images`, `events-api`, `latest-update`, `newsletter-email`, `oveda-event`
- [ ] Funktionen aus `snippets/` verschieben: `bookingRequestHandler`, `googleCalendarIntegration`
- [ ] `controllers/blocks.php` ersetzen (Closure-`require` in 9 Templates, Variable heißt `$blockIsVisible` bzw. `$contentIsVisible`)
- [ ] Duplikate zusammenführen: `mmhTimestampValue` = `latestUpdateTimestampValue`; CSS-Inlining (helpers vs newsletter-email); Hex-Parsing (`mmhColorContrast` vs `mmhApiHexToRgb`); `mmhAbsoluteUrl` vs `mmhOvedaAbsoluteUrl`
- [ ] `cover()` aus 6 Models in einen Trait mit einheitlich `?File`, Aufrufer anpassen
- [ ] Autoload: PSR-4 für `mmh-site`; kopierte Blöcke in beiden Plugin-`composer.json` korrigieren; `@include_once` entfernen; Plugin-IDs vereinheitlichen
- [ ] Web-Plugin entkoppeln (ruft `mmhAbsoluteUrl`, `mmhNewsletterMobileHtml` der Site; Site-Template lädt Plugin-Klasse per Pfad)
- [ ] `site/config/routes.php` und `hooks.php` leeren, wenn alles umgezogen ist
- [ ] Jeder Umzug ein eigener PR; PHPUnit + Playwright-Smoke grün

---

## Phase 5: Komponenten konsolidieren

Reihenfolge nach Nutzen; jeder Schritt gegen die Playwright-Baseline.

- [ ] **Block-/Layout-Loop** (10 Kopien in 11 Templates) → `utilities/blocks` und `utilities/layouts` (`highlight`, `wrapperClass`); Sichtbarkeitsprüfung auch in `home`/`machmit`
- [ ] **Teaser-Karten**: project, theme, Archiv-Inline in `projects.php`, project-update, newsletter → ein `utilities/teaser-card`; eine CSS-Datei statt zwei
- [ ] **Heroes**: `c-hero`, rooms-hero, notes-hero (CSS identisch), note-hero, member/event/newsletter-Header → `sections/hero` mit Varianten
- [ ] **Horizontale Media-Card**: `c-blog-card` + `content-card` (page-card) zusammenführen
- [ ] **Avatar** (6 Kopien) → `utilities/avatar`
- [ ] **Modal-JS** (4 Varianten + Inline-onclick) → ein `modal.js`; `events-calendar-modal` auf `gs-c-modal`
- [ ] **Mapbox** (4 Init-Kopien) → `event-map.js` über Data-Attribute
- [ ] **Badges** (5 Vokabulare) → `badge.css`
- [ ] `eventsListItem` nutzt nur normalisierte Oveda-Events
- [ ] Inline-SVG (teamMemberCard, stepStatusBadge, member) → `utilities/icon`
- [ ] Team-Strip-CSS/JS aus `templates/project.php` auslagern
- [ ] Horoskop-Seite: Inline-CSS in Datei, Daten mit Timeout und Cache
- [ ] Nav-Toggle-Script nur einmal
- [ ] Tokens: `--spacing-*` (151×) und weitere undefinierte Variablen (`--color-bg-secondary`, `--color-fg-light`, `--radius-sm` ...) durch echte Tokens ersetzen (`rooms.css`, `notes.css`, `teamMemberCard.css`, `projectsListing.css`)
- [ ] Notizkarte: featured/regular-Zweig (~95 % identisch) zusammenführen
- [ ] Roomcard auf `utilities/imagePlaceholder`

---

## Phase 6: Konventionen
- [ ] Snippets kebab-case, Ordner englisch (`horoskope`, `ferienpass` prüfen)
- [ ] CSS-Klassen BEM mit `c-`-Präfix (heute 6 Schemata nebeneinander, u. a. `gs-c-*`, `c-camelCase`, ungeprefixte Ketten)
- [ ] `performace` → `performance`
- [ ] `CONTRIBUTING.md`: „Wo gehört was hin?“
- [ ] Doppelte Basisregeln für Raster-Imports (`layout.css` importiert `grid`/`spacing` erneut) bereinigen

---

## Offene Fragen
- Wie sieht die externe Deploy-Pipeline genau aus (Submodule, rsync, Ausschlüsse)?
- Staging-Host und dessen Config?
- Newsletter: eigenes Plugin oder im verbleibenden Web-Plugin?
- Soll `gs-mmh-web-plugin` am Ende aufgelöst oder auf Forms/Submissions reduziert werden?
- Wo liegt die produktive `kirby3-dotenv`-Abhängigkeit?
