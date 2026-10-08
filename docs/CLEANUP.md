# Aufräumaktion GS_MMH_WEB

Lebendes Dokument. Nach jeder erledigten Aufgabe wird die Checkbox gesetzt und der PR/Commit verlinkt. Branch: `refractor/clean_up`. Stand der Bestandsaufnahme: 2026-10-08.

## Ziele
1. `content/` nicht mehr als Submodul deployen (git-content hält die Instanzen aktuell).
2. Eine einheitliche Struktur für Funktionen, Klassen, Routen, Hooks.
3. Doppelstrukturen (Cards, Heroes, Modals ...) abbauen.
4. Tests (PHPUnit + Playwright) als Sicherheitsnetz, **bevor** umgebaut wird.

## Leitlinien
- **Production-Code = `main`.** Räume (Rooms/Buchung) sind ein unfertiges Feature und werden nur angefasst, wenn es nicht anders geht. Aufräumen heißt: Zustand erhalten, kein Verhaltenswechsel.
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
| 2 | Test-Fundament (PHPUnit, Playwright, Lint, CI) | erledigt, bis auf den ersten GitHub-Lauf des CI-Jobs `visual` (89 Unit-Tests, 46 Smoke, 30 Visual) | |
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
- [ ] `rooms.php` / `room.php`: `cover()->toFile()` auf bereits aufgelöstem File. **Räume sind ein unfertiges Feature, bewusst unberührt**
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
- [x] Templates `calendar.php`, `machmit.php` samt Blueprint `machmit.yml` und `layout/mainLayout.php` gelöscht (kaputt: falscher Snippet-Name; in keinem Content-Branch (`production`, `staging`, `main`) verwendet)
- [ ] Templates offen: `app_performance.php` (Site und Plugin), Plugin-`templates/` und `controllers/` → mit der Plugin-Phase
- [x] Snippets gelöscht: `utilities/content-card.php`, `newsletter/blogEntries.php`, `projects/projectTimelineEntry.php`, `integrations/performace*`, `ferienpass/csv_helper.php`
- [ ] Snippets offen: `blocks/line.php` ist leer, aber **bewusst nicht löschen**: der leere Override unterdrückt das Kirby-Default-`<hr>`, Löschen würde Trennlinien sichtbar machen. Erst klären, ob das gewollt ist
- [x] `controllers/site.php` (leer) gelöscht
- [ ] Controller: `about.php` = `team.php` zusammenlegen; `?>` in `error.php`
- [x] `getArchivedProjects` gelöscht
- [ ] Funktionen offen: `getColor` (Plugin, in dessen README dokumentiert), `scheduleLabel` (Plugin-Blockmethode), `mmhApiCoverSvgUrl` (nur Definition). **`mmhOvedaEventClientPayload` ist in Gebrauch** (`events-api.php:453`), nicht löschen
- [ ] CSS: `.c-card*` in `designer.css`, `cta.css` (leer), `layout/content.css`, `newsletter-unsubscribe.css`, `.project-teaser-tags`, `.content-card--imageRight`
- [ ] Debug-Reste: `console.log` (bookingForm, newsletterTeaser, contact-map, signage screen), auskommentierte `var_dump`, auskommentiertes Mail-Handling in `hooks.php`
- [ ] Signage-Plugin: totes `'panel' => ['js','css']`, Route `signage/assets/js/`

### Fehlende Referenzen klären
- [ ] `api/rooms/availability*.json` benötigt `nextcloudCalendarIntegration.php` (fehlt). **Räume sind unfertig, bewusst unberührt**
- [ ] `dreamform/forms`-Snippet in `project.php`: existiert nicht, Kirby rendert nichts (wirkungslos). Bleibt, damit das DOM unverändert bleibt; später zusammen mit der leeren `<section>` entfernen
- [ ] 5 Signage-Snippets (`signage/player|standby|slide-*`) fehlen
- [ ] Blocks: `testimonial.yml` ↔ `testimonials.yml`; `box` nicht registriert, aber referenziert; `faq2`; `searchbar` im falschen Ordner
- [ ] `plugins/kirby3-dotenv/global.php` wird von der Prod-Config verlangt, ist aber nicht im Repo
- [x] `kirby`: verwaisten Submodul-Pointer aus dem Index entfernt (Kirby kommt per Composer)
- [ ] Event-Model fehlt (`events/(:num)` setzt `model => event`)
- [ ] Content ohne Blueprint/Template: `3_informations`, `components/page.txt`, `_drafts/...`, `timeline_entries.txt`; `content/rooms/` hat `default.txt` und `rooms.txt`

---

## Phase 2: Test-Fundament

### PHPUnit
- [x] `phpunit` 10.5 als Dev-Dependency, `tests/Unit`, `tests/bootstrap.php` (Kirby gegen Fixture-Roots), `composer test` (`.ddev/` ist nicht im Repo, daher kein DDEV-Command; Aufruf: `ddev composer test`)
- [x] Oveda: Normalisierung, Kategorien, Slug, URL, Labels, `Meta`, `Facts`, `Ics` getestet. Nicht getestet: `mmhOvedaEventDetail`/`OtherDates` (holen live von der API; erst testbar, wenn der HTTP-Zugriff injizierbar ist, Phase 4)
- [x] `mmhColorContrast`, `mmhTimestampValue`, `getProjectStatusColor`
- [x] `mmhRebaseStylesheetUrls` und `mmhInlineStylesheet` (Fixture-CSS-Baum: Hoisting, Deduplizierung, Zyklen, Reihenfolge) getestet
- [x] `mmhApiHexToRgb`, `mmhApiMixRgb`, `mmhApiRgbColor`, `mmhApiWrapSvgText`, `mmhApiXmlEscape`, `mmhApiCoverFileSlug`
- [x] Newsletter-HTML-Transforms (Icons→Emoji, Chrome entfernen, Mapbox entfernen, Timeline, Wrapper, Abmelde-Link, Inline-Styles, `mmhAbsoluteUrl`)
- [x] `ProjectPage` getestet (Farbe, Akzente, Status-Fallback, Schritt-Sortierung, `latestStepDate`, Thema/Tags). `isTimedContentVisible` (Block, Layout, Seite, Zeitzone, ungültiges Datum, Redakteure)
- [x] Horoskop: `mmhHoroscopeSortSigns` und `mmhHoroscopeAttributes` aus `list.php` nach `helpers.php` extrahiert und getestet (Seite rendert unverändert 12 Zeichen × 5 Skalen). Ein Zeichen ohne bekannte Attribut-Keys bekommt jetzt keine leere `<dl>` mehr. Zieht in Phase 4 ins App-Plugin um

### Playwright
- [x] `tests/e2e/`, `playwright.config.js`, `baseURL` per `PLAYWRIGHT_BASE_URL` (Default DDEV). Projekte `desktop` (1440) und `mobile` (Pixel 7)
- [ ] Seiten: home, projects, project (mit/ohne Projektfarbe, Highlights), theme(s), notes, note, rooms, room, events, event, newsletters, newsletter, über-uns, member, contact, error, sitemap, `/app/horoskope`
- [ ] Breakpoints 390 / 768 / 1040 / 1440; Interaktionen: Mobile-Nav, Modals, Kalender-Overlay
- [x] Smoke-Checks (`npm run test:e2e`): Status, JS-Fehler, `</html>` vorhanden, genau ein `<main>`, Mobile-Menü-Toggle auf den früher defekten Seiten. 46 Tests grün, Räume als bekannt fehlerhaft markiert (HTTP 500 in `bookingForm.php:80`, unfertiges Feature)
- [x] Visual-Tests (`npm run test:visual`, `test:visual:update`): 15 Seiten × 2 Viewports, 3× hintereinander stabil. **Baselines werden nicht committet** (29 MB, plattformabhängig, Pfad in `.gitignore`): lokal vor einem Refactoring auf dem unveränderten Stand mit `test:visual:update` erzeugen, danach vergleichen
- [x] Content **und Plugins** in CI: Es zählt der **aktuelle Stand der Umgebung**, nicht der im Hauptrepo gepinnte Commit. PRs nach `main` rendern mit `production`, PRs nach `staging` (und Pushes) mit `staging`, sowohl für `web_content` als auch für `gs-mmh-web-plugin` und `gs-mmh-signage-plugin`. Gibt es im Plugin den Branch nicht (heute: Web-Plugin hat `main` und `staging`, Signage nur `main`), gilt `main`. Alle Commits werden **einmal pro Lauf aufgelöst** (`git ls-remote`, mit Wiederholung), alle Renderings eines Laufs sehen denselben Stand, ein Vergleich Basis/PR wird nie durch einen fremden Push verfälscht. Das umgeht auch den Fehler „not our ref“ bei ungepushten Plugin-Pointern. Das Ergebnis steht in der Job-Zusammenfassung. Override: `workflow_dispatch` (`content_ref`) bzw. die Umgebungsvariablen `WEB_PLUGIN_REF`, `SIGNAGE_PLUGIN_REF`. Seiten, die Redakteure entfernt haben, werden in CI übersprungen (`MMH_SKIP_MISSING=1`; Startseite und erwartete 404 nie). **Grenzen:** (1) `web_content/production` hinkt dem Live-System hinterher, solange dort ungepushte Commits liegen (Phase 3). (2) Die Pipeline deployt die **gepinnten** Plugin-Commits, CI testet die Branch-Köpfe: Wer den Pointer nicht nachzieht, testet etwas anderes als er ausliefert. Externe Bilder (picsum) und Mapbox werden im Visual-Test ersetzt/blockiert; Oveda/n8n werden nicht gemockt
- [x] **Gewollte Design-Änderungen:** Ein PR kündigt sie im PR-Text an: `Visual-Change: project, notes` (Seitennamen aus `tests/e2e/pages.js`) oder `Visual-Change: all`. Nur die genannten Seiten dürfen abweichen, **jede andere Abweichung lässt den Job weiter scheitern**, ein Nebeneffekt rutscht also nicht durch. Die Bilder (vorher/nachher/Diff) hängen als Artefakt `visual-diffs` am Lauf, die Job-Übersicht listet jede angekündigte Änderung und weist auf Einträge hin, die sich gar nicht geändert haben. Das Bearbeiten des PR-Textes startet nur den Visual-Check neu (Event `edited`, eigene Concurrency-Gruppe, die Code-Jobs werden dabei übersprungen und nicht abgebrochen). Das PR-Template hat dafür einen Abschnitt. Lokal geprüft mit einer absichtlichen CSS-Änderung: ohne Ankündigung 5 Seiten rot, mit `home` 4 rot, mit `all` grün. Entscheidung gegen ein Label: ein Label wäre alles oder nichts
- [~] CI-Jobs `smoke` (Code + Live-Content, `ci-smoke.sh`) und `visual` (Pull Requests und manuell, `ci-visual.sh`): rendert **Base-Commit und PR mit demselben Content** und vergleicht sie. Es werden keine Baselines gespeichert, die Basis *ist* die Baseline (unabhängig vom Betriebssystem, genau die Frage eines Refactorings). Das Skript läuft nur mit `CI=true` oder `CI_VISUAL_FORCE=1`, weil es mit `git checkout --force` den Arbeitsbaum wechselt. **Noch nicht auf GitHub gelaufen**; lokal geprüft wurden Syntax, Schutz und der PHP-Server-Teil (PHP-Built-in-Server mit `kirby/router.php`, CI-Config ohne Debug-Sperre: alle Seiten 200, CSS ausgeliefert). **Voraussetzung:** die vom Hauptrepo gepinnten Plugin-Commits müssen auf GitHub liegen (siehe unten)

### Lint & CI
- [x] Stylelint: 0 Fehler (Autofix: Range-Notation, Leerzeilen, `overflow`-Shorthand; das generierte Bundle `public/media/**` wird ignoriert)
- [x] ESLint: Browser-Globals, `lightbox/` ignoriert, `lint:js` ohne `--fix` nur auf `public/assets/js` (`lint:js:fix` für Autofix)
- [~] lint-staged-Glob für `public/assets/js/*.js` korrigiert; PHP im Hook fehlt weiterhin (läuft nur in DDEV)
- [x] PHP-CS-Fixer-Exclude auf `gs-mmh-signage-plugin` korrigiert (Pfad stimmte nicht, das Plugin wurde mitformatiert); PHPCS prüft zusätzlich `tests/`
- [x] `.editorconfig` angelegt; toter Prettier-PHP-Override entfernt
- [ ] Optional: PHPStan Level 1–3
- [x] `.github/workflows/ci.yml`: phpunit, ESLint/Stylelint, PHPCS (alle blockierend), `smoke`, `visual`. PHPCS beendete sich bei bloßen **Warnungen** (64, fast nur Zeilenlänge) mit exit 1; `phpcs.xml` setzt jetzt `ignore_warnings_on_exit`, Fehler lassen den Job weiterhin scheitern (geprüft). `continue-on-error` ist entfernt

**Befunde aus den Tests (nicht verändert, nur dokumentiert):**
- **Die 404-Seite antwortete mit 500, wenn der Content keine Seite `sitemap` hat (behoben).** `controllers/error.php` rief `$site->find('sitemap')->pages()` auf `null` auf. Aufgefallen ist es in CI: `web_content/production` und `/staging` enthalten **keine** `sitemap`-Seite, sie existiert nur lokal (`4_sitemap`, angelegt durch lokale, ungepushte git-content-Commits „update(page): sitemap“). Fix: ohne `sitemap` bleibt die Navigation leer, die 404 bleibt eine 404 (Test `ErrorControllerTest`). Folge für Phase 3: die ungepushten Commits auf den Instanzen enthalten Inhalte, die CI nicht sieht
- **Zeitgesteuerte Veröffentlichung wirkte nicht (behoben).** `isTimedContentVisible()` (`helpers.php`) fragte `method_exists($content, 'publish_date')` ab. Kirby-Blöcke, -Layouts und -Seiten liefern Felder aber über `__call()`, also war das immer `false`, und Inhalte mit `publish_date` in der Zukunft oder abgelaufenem `end_date` wurden **trotzdem angezeigt**. Fix: neuer Helfer `mmhTimedContentFields()` liest Layouts aus `attrs()`, Blöcke und Seiten aus `content()`; Redakteure/Admins und `?preview` sehen weiterhin alles. Im Production- und im lokalen Content haben nur `contact` (publish 2026-03-26) und `wie-funktioniert-machmit` (publish 2026-04-15) ein Datum, beide in der Vergangenheit: heute ändert sich nichts sichtbar. Ab jetzt wirken neu gesetzte Zeiten tatsächlich. Tests für Block, Layout, Seite, Zeitzone, ungültige Daten und Redakteure
- `sections/hero.php` lädt ohne Cover ein **zufälliges Foto von picsum.photos** (404-Seite, Impressum, Über-uns u. a.): jede Seite sieht bei jedem Aufruf anders aus. Siehe Phase 1 „picsum-Fallback“.
- `templates/note.php:149`: `shuffle()` bei „Weitere Einträge“ erzeugt wechselnde Seitenhöhe (im Visual-Test per `hide` ausgeblendet).
- `mmh.debugLock` (neu, Default `true`, in `layout/head.php`): die Debug-Sperre für Gäste lässt sich abschalten. Nur die DDEV-Config setzt `false`, damit Playwright als Gast browsen kann. Staging/Production unverändert.

---

## Phase 3: Content-Submodul entfernen

**Detaillierte Checkliste pro Instanz: [CONTENT-SUBMODULE-MIGRATION.md](CONTENT-SUBMODULE-MIGRATION.md).**

Stand der Pipeline (von dir bestätigt): Sie checkt `content` rekursiv als Submodul aus, **kopiert den Ordner aber nicht**, weil er auf den Servern schon liegt. Auf Production gibt es redaktionelle Änderungen, die entweder lokal committet und nicht gepusht sind (git-content pusht nicht) oder außerhalb der Struktur liegen und von Hand committet werden müssen. Als Submodul ergibt `content/` damit keinen Sinn: der Pointer ist veraltet, ein `submodule update` würde den Content zurücksetzen, und der Checkout kostet jedes Mal ~586 MB.

Reihenfolge (nicht ändern): **Pipeline → Instanz umwandeln → Hauptrepo.**

- [ ] 1 Pipeline: nur noch die Plugin-Submodule holen, `content/` nie anfassen
- [ ] 2 Pro Instanz Backup und Bestandsaufnahme (Staging, dann Production)
- [ ] 3 Unpushed Commits (A) sichern, manuelle Änderungen (B) von Hand committen, alles zuerst auf einen `backup/*`-Branch pushen
- [ ] 4 Losgelösten HEAD in einen Branch (`production` / `staging`) überführen
- [ ] 5 `content/` zum eigenständigen Repo machen (`.git` aus `.git/modules/content` nach `content/.git`), **ohne** `submodule deinit`
- [ ] 6 Hauptrepo: `git rm --cached content`, `.gitmodules` und tote `.gitignore`-Regeln bereinigen, README/DEVELOPMENT_SETUP anpassen
- [ ] 7 Danach: git-content-Secret, Staging-Config, Backups nach einer Woche löschen
- [ ] Fehlende Staging-Config (`config.<staging-host>.php`) anlegen. Sonst läuft Staging mit `debug => true` und `panel.install => true`; die Debug-Sperre in `layout/head.php` hängt an `debug`, siehe Befunde
- [ ] Verifikation auf Staging: Panel-Speichern → Commit auf richtigem Branch; erneuter Deploy lässt `content/` unverändert; frischer Dev-Clone funktioniert mit der neuen README-Anleitung

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
