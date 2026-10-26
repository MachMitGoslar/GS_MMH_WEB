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
- **Regel (2026-10-16): Ein eigenes Repo gibt es nur für Dinge, die unabhängig deploybar oder wiederverwendbar sind. Alles, was zur Website gehört (Seitenelemente, Rendering, Panel-Vorschau, Routen, Hooks, Newsletter-Darstellung, SEO), liegt im Hauptrepo (`site/` und `site/plugins/mmh-site/`).** Der Ordner `mmh-site` ist nur der Weg, den Kirby für Panel-JS, Block-Methoden und Übersetzungen verlangt, kein weiteres Repo und kein Pointer.
- Im Plugin `gs-mmh-web-plugin` bleiben (tendenziell, Entscheidung 2026-10-16): **angepasste Formularverarbeitung** (Dreamform-Datenbank, Formulare, Einreichungen) und **App-Integration** (`/app/*`, `api/*-cover`, `latest-update`, `highlights`, Horoskope, Ferienpass). Das Signage-Plugin bleibt eigenständig. Sie hängen nicht von Site-Funktionen ab.
- Jede Route, jeder Hook, jede Funktion hat genau eine Quelle.
- `site/config/` enthält nur Optionen, `controllers/` nur echte Controller, `snippets/` nur Markup.
- Plugin-Routen werden **vor** `site/config/routes.php` gematcht.

## Status

| Phase | Inhalt | Status | PR |
|---|---|---|---|
| 0 | Dieses Dokument | erledigt | |
| 1 | Sofort-Fixes & Hygiene | in Arbeit (Routen, Hooks, Kleinbugs, toter Code erledigt; offen: Secret/Staging-Config, Raum-Referenzen, Content ohne Template) | |
| 2 | Test-Fundament (PHPUnit, Playwright, Lint, CI) | erledigt, bis auf den ersten GitHub-Lauf des CI-Jobs `visual` (120 Unit-Tests, 46 Smoke, 30 Visual); beide Plugin-Repos haben jetzt dieselbe Strategie | |
| 3 | Content-Submodul entfernen | Hauptrepo-Seite erledigt; offen sind die Schritte auf Pipeline und Instanzen (1–5, 7) und die Verifikation, alle bei dir | |
| 4 | Logik-Struktur (`mmh-site` + Module) | offen | |
| 5 | Komponenten konsolidieren | offen | |
| 6 | Konventionen festschreiben | offen |
| 7 | Atomares Komponenten-System entlang der Kirby-Struktur (Feld → Block → Layout → Seite) | Recherche und Plan angelegt | |

---

## Phase 1: Sofort-Fixes & Hygiene

### Routen und Hooks
- [x] Plugin-Kopien von `newsletter.xml`, `/app/(:any)`, `/app/ferienpass*` aus `gs-mmh-web-plugin/index.php` entfernen (sie liefen zuerst und waren defekt: Counter zählte nicht, `rss_feed`-Snippet fehlte). Erledigt; Tracker in `routes.php` legt die Tabelle jetzt selbst an. **Plugin-Änderung braucht eigenen Commit im Submodul.**
- [x] `page.changeStatus:after` nur noch einmal (`config/hooks.php` + Plugin); Template-Check `'notes'` → `'note'`
- [x] `DreamForm::register` nur einmal aufrufen (Plugin: Top-Level und `system.loadPlugins:after`)
- [x] `booking-request.json` (beide Routen, POST und GET) entfernt: nichts referenziert sie, das Formular nutzt `api/booking/submit`
- [x] Newsletter-HTML: gemeinsamer Antwort-Helfer in `api.php`, **beide URLs bleiben** (`api/newsletter/<slug>.html` könnte extern verlinkt sein; intern wird nur `newsletter-html` genutzt). Lokal geprüft: 200/404 wie vorher
- [x] Kommentar „before /app tracker so it wins“ in `routes.php` korrigieren oder obsolet machen

### Bugs
- [x] 7 Templates ohne `layout/foot`: contact, newsletter, newsletters, newsletter-unsubscribe, team, theme, themes
- [x] `horoskope/card.php`: `'rammelsberg'` vs Datei `hk_rammelberg.png`
- [x] `statusheader.php`: Literal `'test'`
- [x] `api.php`: Status `44` statt `404`
- [x] `public/index.php`: `'./assets'` (fehlender Slash)
- [x] `home.php`: `block"`-Typo
- [x] `sections/hero.php`: picsum-Fallback in Production. **Entscheidung: bleibt wie er ist**
- [ ] `rooms.php` / `room.php`: `cover()->toFile()` auf bereits aufgelöstem File. **Räume sind ein unfertiges Feature, bewusst unberührt**
- [x] `ferienpass/events.php` / `event_random.php`: `sort_by_start` durch Inline-Closure ersetzt (keine globale Funktion mehr), auskommentierte `var_dump` entfernt
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
- [x] `app_performance` aus Site (Template und Controller) gelöscht, kein Blueprint, kein Content. **Offen:** Plugin-Kopie sowie Plugin-`templates/` und `controllers/` → mit der Plugin-Phase
- [x] Snippets gelöscht: `utilities/content-card.php`, `newsletter/blogEntries.php`, `projects/projectTimelineEntry.php`, `integrations/performace*`, `ferienpass/csv_helper.php`
- [x] `blocks/line.php` gelöscht und `line` aus `note.yml` entfernt: das Divider-Block-Element hat es abgelöst, kein Content nutzt `line` mehr (Entscheidung)
- [x] `controllers/site.php` (leer) gelöscht
- [x] Controller: `about.php` lädt jetzt `team.php` (war identisch; `uber-uns` und `team` liefern weiter je 20 Karten). `?>` in `error.php` war schon weg
- [x] `getArchivedProjects` gelöscht
- [x] Funktionen: `getColor` (Plugin, samt README-Abschnitt "Helper Functions") und die Blockmethode `scheduleLabel` (Plugin; die gleichnamigen Vue-Computeds im Panel sind unabhängig und bleiben) gelöscht, beide ohne Aufrufer; die Sichtbarkeit regelt `isVisible()` in `mmh-site`. `mmhApiCoverSvgUrl` war schon gelöscht. **`mmhOvedaEventClientPayload` ist in Gebrauch** (`events-api.php:453`), nicht löschen
- [x] CSS: `.c-card*` (`designer.css`), leere `cta.css` (samt Import), `.project-teaser-tag(s)` entfernt; `.content-card--imageRight` gab es nicht mehr. **Befund:** `newsletter-unsubscribe.css` wird **nirgends importiert**, das Template nutzt aber genau diese Klassen (`newsletter-unsubscribe__*`), die Abmeldeseite ist also ungestylt. Einbinden wäre eine sichtbare Änderung, bewusst nicht gemacht
- [x] Debug-Reste: `console.log` in bookingForm, newsletterTeaser, contact-map, Signage-Screen (`templates/screen.php`, 7 Stück; `console.error`/`warn` bleiben), Panel-Writer-Marks des Web-Plugins (`Badge.js`, `Button.js`), `blocks/cta.vue` (leerer Watcher mit Log entfernt) und `DreamformDbOverview.vue` (ungenutzte Methode `displayForms`) entfernt; Web-Plugin neu gebaut (`index.js`). `var_dump`-Kommentar in `AccessController.php` entfernt. Das auskommentierte Mail-Handling in `hooks.php` gehört zu den Räumen (unfertig, bleibt)
- [x] Signage-Plugin: totes `'panel' => ['js','css']` (Kirby kennt den Schlüssel nicht, `index.js`/`index.css` im Plugin-Root lädt es von selbst) und die Route `signage/assets/js/` (Ordner `assets/js` existiert nicht) entfernt. Die CSS-Route bleibt, `templates/screen.php` nutzt sie

### Fehlende Referenzen klären
- [ ] `api/rooms/availability*.json` benötigt `nextcloudCalendarIntegration.php` (fehlt). **Räume sind unfertig, bewusst unberührt**
- [x] `dreamform/forms`-Snippet in `project.php` samt leerer `<section>` entfernt (wirkungslos, das Snippet existierte nicht). Die `dreamform-*`-CSS-Klassen in `formModals.php`, `newsletterTeaser.php` und `newsletter-unsubscribe.php` sind Markup-Klassen der eigenen Formulare und bleiben
- [x] 5 Signage-Snippets (`signage/player|standby|slide-blocks|slide-video|slide-calendar`): im Plugin nur registriert, die Dateien gab es nie und kein Code rief sie auf (der Player rendert clientseitig in `templates/screen.php`). Registrierungen entfernt; `blocks/signage-text` und `blocks/signage-heading` bleiben
- [x] Blocks: `testimonials.yml` → `testimonial.yml` umbenannt (die Registrierung zeigte auf eine nicht vorhandene Datei). `box` entfernt (Blueprint, Snippet, Registrierung, Übersetzungen, README, Einträge in `contact`/`project`/`theme`/`note`-Blueprints und `models/note.php`): **bestehende Box-Blöcke im Content (Earth Hour, Tag der Druckkunst) wurden nie gerendert, sie bleiben unsichtbar und können im Panel gelöscht werden**. `faq2` ist als `blocks/faq` registriert, harmlos; `searchbar` wird von `projects.php` genutzt, bleibt
- [x] Testimonial-Block neu gestaltet (Vorbild: Quote-Card auf `8_03-machmit-stipendien`): Zitat kursiv in Gewicht 300, Avatar mit Name und Rolle darunter (Person aus dem Team oder freie Angaben), wählbare Textfarbe (Normal, Projektfarbe, Gold), eigener weißer Hintergrund und eigene Textfarbe auch in gefärbten Zeilen. Der globale `blockquote`-Stil (Seitenstrich, Cyan-Glow, feste Breite `40ch`) wird überschrieben. Im Browser auf der Stipendien-Seite geprüft (Desktop und schmal)
- [x] `kirby3-dotenv` zurückgebaut: Prod-Config nutzt `getenv()`, der DDEV-Eintrag `bnomei.dotenv.environment` ist weg. **Voraussetzung auf dem Server: die Variablen (`MMH_DB_*`, `CONTENT_SALT`, `EMAIL_FROM`, `EMAIL_NAME`) müssen als echte Umgebungsvariablen gesetzt sein; eine `.env`-Datei wird nicht mehr gelesen**
- [x] `kirby`: verwaisten Submodul-Pointer aus dem Index entfernt (Kirby kommt per Composer)
- [x] Event-Model: `'model' => 'event'` aus der Route entfernt (Kirby fiel ohnehin auf `Page` zurück, Verhalten gleich)
- [ ] Content ohne Blueprint/Template (Stand nach Merge von `content/`, Branch `development`): `3_informations/informations.txt` (Kinder nutzen `default`), `components/page.txt` und `components/_changes/page.txt`, `timeline_entries.txt` in `2_projects/1_02-machmitwald/_drafts/weiter`, `1.txt` in `2_projects/_drafts/handels-und-serviceplattform`; `rooms/` hat `default.txt` **und** `rooms.txt`. Das ist Content im eigenen Repo, daher nur dokumentiert, nicht angefasst

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
- [x] Seiten: home, projects, project (mit/ohne Projektfarbe, Highlights), theme(s), notes, note, rooms, room, events, event, newsletters, newsletter, über-uns, member, contact, error, sitemap, `/app/horoskope`
- [ ] Breakpoints 390 / 768 / 1040 / 1440; Interaktionen: Mobile-Nav, Modals, Kalender-Overlay
- [x] Smoke-Checks (`npm run test:e2e`): Status, JS-Fehler, `</html>` vorhanden, genau ein `<main>`, Mobile-Menü-Toggle auf den früher defekten Seiten. 46 Tests grün, Räume als bekannt fehlerhaft markiert (HTTP 500 in `bookingForm.php:80`, unfertiges Feature)
- [x] Visual-Tests (`npm run test:visual`, `test:visual:update`): 15 Seiten × 2 Viewports, 3× hintereinander stabil. **Baselines werden nicht committet** (29 MB, plattformabhängig, Pfad in `.gitignore`): lokal vor einem Refactoring auf dem unveränderten Stand mit `test:visual:update` erzeugen, danach vergleichen
- [x] Content **und Plugins** in CI: Es zählt der **aktuelle Stand der Umgebung**, nicht der im Hauptrepo gepinnte Commit. PRs nach `main` rendern mit `production`, PRs nach `staging` (und Pushes) mit `staging`, sowohl für `web_content` als auch für `gs-mmh-web-plugin` und `gs-mmh-signage-plugin`. Gibt es im Plugin den Branch nicht (heute: Web-Plugin hat `main` und `staging`, Signage nur `main`), gilt `main`. Alle Commits werden **einmal pro Lauf aufgelöst** (`git ls-remote`, mit Wiederholung), alle Renderings eines Laufs sehen denselben Stand, ein Vergleich Basis/PR wird nie durch einen fremden Push verfälscht. Das umgeht auch den Fehler „not our ref“ bei ungepushten Plugin-Pointern. Das Ergebnis steht in der Job-Zusammenfassung. Override: `workflow_dispatch` (`content_ref`) bzw. die Umgebungsvariablen `WEB_PLUGIN_REF`, `SIGNAGE_PLUGIN_REF`. Seiten, die Redakteure entfernt haben, werden in CI übersprungen (`MMH_SKIP_MISSING=1`; Startseite und erwartete 404 nie). **Grenzen:** (1) `web_content/production` hinkt dem Live-System hinterher, solange dort ungepushte Commits liegen (Phase 3). (2) Die Pipeline deployt die **gepinnten** Plugin-Commits, CI testet die Branch-Köpfe: Wer den Pointer nicht nachzieht, testet etwas anderes als er ausliefert. Externe Bilder (picsum) und Mapbox werden im Visual-Test ersetzt/blockiert; Oveda/n8n werden nicht gemockt
- [x] **Gewollte Design-Änderungen:** Ein PR hakt die betroffenen Seiten in der Checkliste „Visuelle Änderungen“ seines PR-Textes an (oder `all`). Die Liste wird aus `tests/e2e/pages.js` **erzeugt** und in „Seiten“ und „Detailseiten“ gegliedert; bei Detailseiten (Feld `detail`, z. B. `/team/<name>`) gilt ein Haken für *alle* Seiten dieser Art, die Stichprobe (`/team/christian`) steht dabei, (`npm run pr-template`, Beschriftung über das Feld `label`), der CI-Job `frontend-lint` schlägt fehl, wenn Template und Seitenliste auseinanderlaufen (`pr-template:check`). Ausgewertet wird sie von `tests/tools/pr-template.js --parse` (mit 8 Node-Tests, `npm run test:tools`): nur der erzeugte Block zählt, andere Checkboxen des Templates nicht, Seiten, die es nicht mehr gibt, erzeugen eine Warnung. Nur die angehakten Seiten dürfen abweichen, **jede andere Abweichung lässt den Job weiter scheitern**, ein Nebeneffekt rutscht also nicht durch. Die Bilder (vorher/nachher/Diff) hängen als Artefakt `visual-diffs` am Lauf, die Job-Übersicht listet jede angekündigte Änderung und weist auf angehakte Seiten hin, die sich gar nicht geändert haben. Das Bearbeiten des PR-Textes startet nur den Visual-Check neu (Event `edited`, eigene Concurrency-Gruppe, die Code-Jobs werden dabei übersprungen und nicht abgebrochen). Lokal geprüft mit einer absichtlichen CSS-Änderung: ohne Ankündigung 5 Seiten rot, mit `home` 4 rot, mit `all` grün. Entscheidung gegen ein bloßes Label: ein Label wäre alles oder nichts
- [x] **Gewollte Änderungen lokal:** `npm run test:visual -- --expect member,team` (oder `all`) ist das lokale Gegenstück zur PR-Checkliste: die genannten Seiten dürfen abweichen, alle anderen nicht, Namen werden gegen `pages.js` geprüft (Tippfehler = Fehler mit Liste der gültigen Namen). Nach einem Fehlschlag nennt `test:visual` die abweichenden Seiten und die passenden Befehle zum Kopieren. `npm run test:visual:update -- member team` erneuert nur die Baselines dieser Seiten (Titelfilter mit Wortgrenze: `project` trifft nicht `project-step`). Der Wrapper ist `tests/tools/visual.js` (7 Node-Tests); CI ruft Playwright weiterhin direkt auf. Lokal geprüft mit einer absichtlichen CSS-Änderung: Hinweis nach dem Fehlschlag, `--expect` mit allen Seiten grün, teilweise Ankündigung lässt den Rest rot, Teil-Update ändert nur die genannten Baselines. **Grenze:** Verglichen wird mit `maxDiffPixelRatio` 0,01, kleine Änderungen (wenige Überschriften auf einer langen Seite) können unter dieser Schwelle bleiben; sie zu senken erhöht die Flakiness
- [ ] **Messhinweis (kein Codefehler):** Beim Prüfen eines vermeintlichen Race-Problems beim Erzeugen des CSS-Bundles (`mmhStylesheetBundle`) hat mich ein Messartefakt getäuscht: Das Löschen des Bundles auf dem **Host** und die verzögerte DDEV-Dateisynchronisierung (Mutagen) erzeugten gelegentliche 404 für das CSS. Mit Löschen **im Container** gab es beim Original-Code in 480 Anfragen keinen Fehler. Theoretisch bleibt eine kleine Lücke (`mmhStylesheetBundle` löscht vor dem Schreiben auch die Datei der aktuellen Version, ein paralleler Request könnte sie kurz vermissen; Schreiben nicht atomar), sie ist nicht reproduzierbar und wurde deshalb **nicht** geändert. Ein getesteter Entwurf liegt vor, falls gewünscht
- [~] CI-Jobs `smoke` (Code + Live-Content, `ci-smoke.sh`) und `visual` (Pull Requests und manuell, `ci-visual.sh`): rendert **Base-Commit und PR mit demselben Content** und vergleicht sie. Es werden keine Baselines gespeichert, die Basis *ist* die Baseline (unabhängig vom Betriebssystem, genau die Frage eines Refactorings). Das Skript läuft nur mit `CI=true` oder `CI_VISUAL_FORCE=1`, weil es mit `git checkout --force` den Arbeitsbaum wechselt. **Noch nicht auf GitHub gelaufen**; lokal geprüft wurden Syntax, Schutz und der PHP-Server-Teil (PHP-Built-in-Server mit `kirby/router.php`, CI-Config ohne Debug-Sperre: alle Seiten 200, CSS ausgeliefert). **Voraussetzung:** die vom Hauptrepo gepinnten Plugin-Commits müssen auf GitHub liegen (siehe unten)

### Lint & CI
- [x] Stylelint: 0 Fehler (Autofix: Range-Notation, Leerzeilen, `overflow`-Shorthand; das generierte Bundle `public/media/**` wird ignoriert)
- [x] ESLint: Browser-Globals, `lightbox/` ignoriert, `lint:js` ohne `--fix` nur auf `public/assets/js` (`lint:js:fix` für Autofix)
- [~] lint-staged-Glob für `public/assets/js/*.js` korrigiert; PHP im Hook fehlt weiterhin (läuft nur in DDEV)
- [x] PHP-CS-Fixer und PHPCS prüfen jetzt denselben Geltungsbereich (`site/`, `tests/`) mit denselben Ausschlüssen (Fremd-Plugins, `gs-mmh-signage-plugin`, `gs-mmh-web-plugin`); `Generic.WhiteSpace.ScopeIndent` und `Squiz.WhiteSpace.ScopeClosingBrace` sind in `templates/` und `snippets/` aus, weil der Fixer HTML-gemischte Dateien nicht einrückt (vorher: `format` sauber, `lint` 146 Fehler). `format:check` und `lint` laufen sauber
- [x] **Plugins (Web und Signage) nach derselben Strategie:** eigene `composer.json`-Skripte (`test`, `format`, `format:check`, `lint`, `lint:fix`), `.php-cs-fixer.dist.php`, `phpcs.xml`, `phpunit.xml` mit Bootstrap und ersten Tests (Web: `SeoMetadataTest`; Signage: `DurationCalculatorTest`, `ICalParserTest`), `.editorconfig` und `.github/workflows/ci.yml` (PHPUnit, PHPCS, Format-Check, Prettier). Paketnamen auf Kleinbuchstaben, Signage-Autoload als `classmap`. **Noch nicht auf GitHub gelaufen. Offen:** falscher Autoload-Eintrag in der `composer.json` des Web-Plugins (zeigt auf `classes`, echte Klassen liegen im Root unter `GsMmh\WebPlugin`)
- [x] Abhängigkeiten: `kirbyup`, `browser-sync`, `concurrently` aus dem Hauptrepo entfernt (`npm run serve|serve-php|sync` und `browsersync.js` entfallen, lokal läuft `composer start` bzw. ddev), Patch-Updates eingespielt, `npm audit` und `composer audit` ohne Funde. **Später:** `phpunit` 13 und `phpcs` 4 (Hauptversionen), PHP-Mindestversion auf 8.4 anheben (eigener Branch)
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

**Plugin-Pointer aufgelöst (Entscheidung):** `gs-mmh-web-plugin` und `gs-mmh-signage-plugin` sind keine Submodule des Hauptrepos mehr (Index, `.gitmodules`, `.gitignore`-Ausnahmen). Das Deployment stellt sie selbst um (Staging: `staging`/`main`, Production: `main`/`main`), Entwickler klonen sie nach `site/plugins/` (README). `git-content` bleibt vorerst Submodul (Drittanbieter).

**Detaillierte Checkliste pro Instanz: [CONTENT-SUBMODULE-MIGRATION.md](CONTENT-SUBMODULE-MIGRATION.md).**

Stand der Pipeline (von dir bestätigt): Sie checkt `content` rekursiv als Submodul aus, **kopiert den Ordner aber nicht**, weil er auf den Servern schon liegt. Auf Production gibt es redaktionelle Änderungen, die entweder lokal committet und nicht gepusht sind (git-content pusht nicht) oder außerhalb der Struktur liegen und von Hand committet werden müssen. Als Submodul ergibt `content/` damit keinen Sinn: der Pointer ist veraltet, ein `submodule update` würde den Content zurücksetzen, und der Checkout kostet jedes Mal ~586 MB.

Reihenfolge (nicht ändern): **Pipeline → Instanz umwandeln → Hauptrepo.**

- [ ] 1 Pipeline: die Plugins klonen (keine Submodule mehr), `content/` nie anfassen
- [ ] 2 Pro Instanz Backup und Bestandsaufnahme (Staging, dann Production)
- [ ] 3 Unpushed Commits (A) sichern, manuelle Änderungen (B) von Hand committen, alles zuerst auf einen `backup/*`-Branch pushen
- [ ] 4 Losgelösten HEAD in einen Branch (`production` / `staging`) überführen
- [ ] 5 `content/` zum eigenständigen Repo machen (`.git` aus `.git/modules/content` nach `content/.git`), **ohne** `submodule deinit`
- [x] 6 Hauptrepo: `content` steht nicht mehr im Index (kein Gitlink), `.gitmodules` kennt nur noch `git-content`, die README beschreibt `content/` als eigenes Repo (`web_content`). Die `.gitignore`-Regel war `/content/*` und ließ den Ordner als "untracked" erscheinen, sobald er ein eigenes Repo ist; jetzt `/content/`. Es gibt keine `DEVELOPMENT_SETUP`-Datei mehr, der Hinweis entfällt
- [ ] 7 Danach: git-content-Secret, Staging-Config, Backups nach einer Woche löschen
- [ ] Fehlende Staging-Config (`config.<staging-host>.php`) anlegen. Sonst läuft Staging mit `debug => true` und `panel.install => true`; die Debug-Sperre in `layout/head.php` hängt an `debug`, siehe Befunde
- [ ] Verifikation auf Staging: Panel-Speichern → Commit auf richtigem Branch; erneuter Deploy lässt `content/` unverändert; frischer Dev-Clone funktioniert mit der neuen README-Anleitung

---

## Phase 4: Logik-Struktur

### Architektur-Entscheidung: Seitenelemente (Stand 2026-10-16, Vorschlag, noch zu bestätigen)

**Ein Seitenelement lebt an einer Stelle im Hauptrepo:** Blueprint (`site/blueprints/blocks/`), Frontend-Snippet (`site/snippets/blocks/`), CSS, Übersetzungen (`mmh-site`) und Panel-Vorschau. Das Plugin `gs-mmh-web-plugin` liefert keine Seitenelemente mehr.

Panel-Vorschau ohne eigenen Build: **ein generischer Block-Preview** in `mmh-site` (`index.js` mit Template-String, kein kirbyup). Er schickt den Blockinhalt an eine Panel-API-Route, die dasselbe Frontend-Snippet rendert (`blocks/<typ>`), und zeigt das Ergebnis in einem isolierten Rahmen mit dem Site-CSS. Damit gibt es **ein** Rendering für Seite und Panel, kein Vue pro Block. Grenzen: Blöcke mit Formular/Dreamform und Karten (Mapbox) brauchen weiter eine einfache eigene Vorschau; die Route gilt nur für angemeldete Panel-Nutzer; die Seite wird als Kontext mitgegeben.

Bestandsaufnahme Plugin (Panel-Teil, `src/panel_components`): Block-Vorschauen accordion, box (**Block gibt es nicht mehr**, tot), button, card, cta, divider, download, faq, form, testimonial, timeline; Writer-Knoten (blockquote, headline, subheadline, title, titleXL, titleXXL); Writer-Marks (Badge, Button, Footnote, Highlight, Link); Sections `NewsletterRecipients`, `SeoPreview`; Views `DreamformDbOverview`, `DreamformDbForm`; Override `Layout.vue`.

Reihenfolge (jeder Schritt ein eigener PR, Plugin-Änderung je eigener Commit im Plugin-Repository; die Plugins sind keine Submodule mehr, ein Pointer muss nicht nachgezogen werden):
1. [x] Card-Block: Blueprint, Snippet, Übersetzungen ins Hauptrepo (Plugin-Dateien noch zu löschen)
2. [ ] Übrige Block-Definitionen umziehen: accordion, button, cta, divider, download, faq (`faq2`), testimonial, timeline, text. Blueprint, Snippet, Übersetzungen, CSS, Registrierungen im Plugin entfernen
3. [ ] Generischen Preview bauen (Pilot: card), danach block für block die Vue-Datei im Plugin ersetzen und löschen. `box.vue` sofort löschen
4. [ ] Writer-Knoten und -Marks: gehören zu den Elementen (Frontend-Ausgabe im Site-Code), Panel-Teil erst umziehen, wenn der Build im Hauptrepo steht
5. [ ] Plugin schrumpft auf Formularverarbeitung und App-Integration (Regel siehe Leitlinien). SEO-Section und Newsletter-Darstellung ziehen ins Hauptrepo; Newsletter-**Empfänger** hängen an der Formularverarbeitung und bleiben vorerst im Plugin

### Modul-Zuordnung (Vorschlag, vor Umsetzung zu bestätigen)

| Modul | Inhalt |
|---|---|
| `mmh-site` (Hauptrepo) | Projekt-Routen/Redirects (`MMH_MERGED_PROJECTS`), `events/(:num)`, `events.json`, sitemap/robots, SEO (Section und Metadaten), Status-/Archiv-Hooks, `isTimedContentVisible`, Stylesheet-Bundle, `mmhBackLink`, `mmhAvatarImage`, `mmhColorContrast`, Oveda-Bibliothek, **alle Blöcke samt Panel-Vorschau**, Newsletter-Darstellung (`newsletter.xml`, `newsletter-email.php`, `api/newsletter-html`) |
| `gs-mmh-web-plugin` (Plugin, bleibt) | angepasste Formularverarbeitung (Dreamform-Datenbank, Formulare, Einreichungen, Newsletter-Empfänger), App-Integration (`/app/*` inkl. Counter, Horoskope, Ferienpass, `api/latest-update`, `api/highlights`, `api/*-cover`) |
| `gs-mmh-signage-plugin` | bleibt separat; Namespace/Autoload korrigieren |
| Raumbuchung → `gs-mmh-web-plugin` | Entscheidung 2026-10-16: zieht jetzt ins Plugin und wird dort weiterentwickelt (siehe Umzugsplan unten). Feature bleibt unfertig, Verhalten wird beim Umzug nicht geändert |

### Umzugsplan Raumbuchung → Plugin (Entscheidung 2026-10-16)

Warum sie verteilt liegt: kein Architekturgrund. Das Feature kam am 2026-01-13 (`488e8d3`, zusammen mit Notes) ins Hauptrepo, nur das Panel-Area (`areas/rooms-booking.php`) liegt im Plugin. Ein Kirby-Plugin kann Templates, Blueprints, Snippets, Routen, API, Hooks und Assets selbst registrieren, alles lässt sich also in einem Ort bündeln. Im Hauptrepo bleiben nur die Inhaltsseiten (`content/rooms`).

Was heute im Hauptrepo liegt:
- Templates `room.php`, `rooms.php`; Blueprints `room`, `rooms`, `booking-request`, `booking-requests`
- Snippets `content-types/rooms/` (`bookingForm`, `bookingRequestHandler`, `googleCalendarIntegration`, `roomCard`, `equipmentIcon`, `emails/*`)
- `site/config/api.php` (Routen `booking/submit`, `rooms/availability.json`, `rooms/(:any)/availability.json`), `site/config/hooks.php` (Status-Wechsel einer Buchungsanfrage: E-Mail, Kalender)
- CSS `site/components/rooms.css`, Tests `rooms`/`room` in `tests/e2e/pages.js` (als bekannt fehlerhaft markiert)

Vorgehen (ein PR im Hauptrepo und ein Commit im Plugin je Schritt; Deployment-Reihenfolge beachten, kein Pointer nachzuziehen):
1. [ ] Plugin-Struktur `rooms/` anlegen (`blueprints`, `templates`, `snippets`, `routes`, `hooks`, `assets`); Registrierung im Plugin-`index.php`
2. [ ] Blueprints, Templates, Snippets verschieben; im Hauptrepo löschen. Site-Dateien haben Vorrang vor Plugin-Dateien: erst löschen, wenn das Plugin deployt ist, sonst fehlt die Seite kurz
3. [ ] Routen und Hooks aus `site/config/api.php` und `hooks.php` ins Plugin; `nextcloudCalendarIntegration.php` (fehlt, Verweis im Code) klären
4. [ ] CSS: das Plugin liefert `rooms.css` über das Bundle des Hauptrepos oder über eigene Assets (offen, siehe Stylesheet-Bundle)
5. [ ] Tests der Raumbuchung ins Plugin (PHPUnit), E2E-Seiten `rooms`/`room` bleiben im Hauptrepo, bis das Feature fertig ist

### Aufgaben
- [x] `mmh-site` anlegen: `index.php`, `extensions.php`, `.gitignore`-Ausnahme, `src/` mit PSR-4 (`Mmh\Site\` → `site/plugins/mmh-site/src/`, über das `autoload` der Haupt-`composer.json`; nach dem Pull einmal `composer dump-autoload`). Erste Klasse: `Visibility` (aus `helpers.php`: `isTimedContentVisible()` und `mmhTimedContentFields()` sind jetzt `Visibility::isVisible()` und `::fields()`)
- **Umzüge in Phase 4: je Baustein ein Commit (kein PR). Reihenfolge angepasst (2026-10-26):** `api-images` und `latest-update` gehören zur App-Schnittstelle und ziehen ins **Web-Plugin** (nicht nach `mmh-site`); Commit im Plugin-Repo, danach löschen im Hauptrepo (Site-Dateien haben Vorrang vor Plugin-Dateien: erst Plugin deployen). `oveda-event`/`events-api`: vorher klären (App oder Website), `newsletter-email` bleibt im Hauptrepo
- [ ] Bibliotheken aus `controllers/` verschieben: `api-images`, `events-api`, `latest-update`, `newsletter-email`, `oveda-event`
- [ ] Funktionen aus `snippets/` verschieben: `bookingRequestHandler`, `googleCalendarIntegration`
- [x] `controllers/blocks.php` ersetzt durch `->isVisible()` (siehe Phase 7)
- [ ] Duplikate zusammenführen: `mmhTimestampValue` = `latestUpdateTimestampValue`; CSS-Inlining (helpers vs newsletter-email); Hex-Parsing (`mmhColorContrast` vs `mmhApiHexToRgb`); `mmhAbsoluteUrl` vs `mmhOvedaAbsoluteUrl`
- [ ] `cover()` aus 6 Models in einen Trait mit einheitlich `?File`, Aufrufer anpassen
- [ ] Autoload: PSR-4 für `mmh-site`; kopierte Blöcke in beiden Plugin-`composer.json` korrigieren; `@include_once` entfernen; Plugin-IDs vereinheitlichen
- [ ] Web-Plugin entkoppeln (ruft `mmhAbsoluteUrl`, `mmhNewsletterMobileHtml` der Site; Site-Template lädt Plugin-Klasse per Pfad)
- [ ] `site/config/routes.php` und `hooks.php` leeren, wenn alles umgezogen ist
- [ ] Jeder Umzug ein eigener PR; PHPUnit + Playwright-Smoke grün

---

## Phase 5: Komponenten konsolidieren

Reihenfolge nach Nutzen; jeder Schritt gegen die Playwright-Baseline.

- [x] **Block-/Layout-Loop** → `utilities/blocks` und `utilities/layouts` (Parameter `wrapperClass`, `highlight`; Sichtbarkeit über `isTimedContentVisible`). Umgestellt: about, default, theme, project, note, contact (Visual-, Smoke- und Unit-Tests grün). **Offen:** `rooms`/`room` (unfertiges Feature, nutzen weiter `controllers/blocks.php`), `home` (hat keine Sichtbarkeitsprüfung; sie zu ergänzen wäre eine Verhaltensänderung)
- [x] **Karten (atomares Grundgerüst):** `utilities/card` ist das **eine** Karten-Layout (Medien-Bereich mit Bild/Platzhalter/Avatar und Overlay; Body mit Meta-Zeile, Titel, Untertitel, Text, freiem Body, Aktionen unten; alle Teile optional) + `card.css` (`data-variant`, `data-orientation` vertical/horizontal, `data-size` regular/featured). Atom `utilities/avatar-stack` (+ `avatarStack.css`) ersetzt die zwei kopierten Autoren-Stapel. Umgestellt: project, theme, project-update, newsletter, Archivkarte, **note** (regular und featured), **newsletter-entry**, **page-card**, **team member** (Variante `person`), **Kartenblock** (`site/snippets/blocks/card.php` überschreibt die Plugin-Kopie), Mitgliederseite (Projekte, Newsletter, Notizen nutzen jetzt dieselben Karten wie ihre Übersichten). Entfernt: `projectTeaserCard.css`, `projectUpdateTeaserCard.css`, `content-card.css`, `c-blog-card` (`blog.css`, `site.css`), `.note-card*`, die alten `.c-teamMemberCard`-Regeln, `.newsletter-card`/`.note-card` der Mitgliederseite. Regel: neue Karte = Parameter oder Variante am Grundgerüst, kein eigenes Karten-Markup/-CSS. **Bewusste Änderungen:** einheitliche Abstände, Hover bei allen Karten, gleiche Bildhöhe (21:9) statt 3:2 bei Notizen, neutraler Platzhalter, Meta-Zeile bei Notizen. `newsletter-email.php`: Inline-Styles für `c-card__*`, `c-newsletterTeaserCard`/`c-projectUpdateTeaser-card`/`c-newsletterEntryCard` bleiben als Zusatzklassen. **Nicht geprüft:** Themenkarte (kein Theme-Content lokal), E-Mail-Rendering visuell. **Offen:** `roomCard` (Räume, unfertig), `eventsListItem` (Oveda-Overlay-Karte und Listenvariante), `author-card` auf der Notizseite, Plugin-Kopie `blocks/card.php` löschen (eigener Commit im Plugin), Badges als eigenes Atom (`status-badge`, `newsletter-badge`, `.badge` der Einträge, `.statusheader` als Meta-Atom)
- [x] **Projektschritt-Karte:** `content-types/projects/projectStepCard` ist das **eine** Layout eines Projektschritts (Startseite, Newsletter: `context` feed mit Bild, Projekttitel und Auszug; Projektseite: `context` timeline ohne Bild mit Titel und voller Beschreibung). Gebaut auf `utilities/card` (Variante `update`), Meta-Zeile aus `statusheader`. `projectUpdateTeaserCard` und die alten `.project-step-timeline-card/-content/-date/...`-Regeln sind entfernt; die Timeline behält Linie und Marker. Die Class `c-projectUpdateTeaser-card` bleibt als Haken der Newsletter-Mail-Stile. **Sichtbare Änderung:** Timeline-Einträge auf Projektseiten haben jetzt das Kartenlayout (weiß, Datum rechts in der Meta-Zeile, kein grauer Hintergrund, Badge in regulärer Größe). Visual-Baselines von `project` und `home` neu ziehen
- [x] **Kontaktseite im Event-Look:** `contact.php` baut auf `sections/profile-header` (Titel, Intro; Bild der Seite oder Markenplatzhalter statt Zufallsfoto) und dem neuen `sections/glance` auf (Facts E-Mail, Telefon, Web, Adresse, Social, daneben die Karte); Öffnungszeiten, Formular und weitere Blöcke folgen darunter. `sections/glance` (Facts plus optionale Karte, `.c-glance*` in `glance.css`) ersetzt `c-event__glance` und wird von Event-Details und Kontakt gemeinsam genutzt; `sections/contact-map` ist entfernt. Die Karte nutzt `event-map.js` über Data-Attribute (Mapbox-Init-Kopie weniger). Der Platzhalter-Logo-Fehler in `profile-header.css` (Logo auf 100 % gezogen) ist behoben. **Sichtbare Änderung:** Kontaktseite komplett neu; Event-Details unverändert bis auf den Platzhalter. Die **Member-Seite** nutzt jetzt ebenfalls `sections/glance` (eigenes Markup, CSS `unified-contact-card` & Co. und das Inline-Mapbox-Skript sind entfernt; ohne Koordinaten zeigt die Karte das MachMit!Haus wie bisher). Die Social-Media-Sektion der Member-Seite ist entfallen: `MemberPage::socialLinks()` (Instagram, Facebook, LinkedIn, YouTube, GitHub, X; Handle oder URL) liefert die Profile als Facts mit Icon und Link in die Glance-Karte (Icons github und x neu). Neben der Karte stehen die Facts vertikal mittig (`align-content: center`); mit mehr Facts als Kartenhöhe wächst die Zeile und die Karte streckt sich mit. Baselines `contact`, `event` und `member` neu ziehen
- [x] **Glance als Layoutbaustein und Block:** `sections/glance` (Facts plus optionale Karte) ist der Baustein „Auf einen Blick“. Im Panel ist er der Block `facts` (jetzt „Auf einen Blick“): Fakten wie bisher, optional **Karte neben den Fakten** (Koordinaten, Marker-Beschriftung, Routenlink; der Token kommt aus `mmh.mapbox.token`/Kontaktseite). Der Block lädt Mapbox und `event-map.js` selbst (einmal je Seite über `mmhOnce()`), `event-map.js` startet jetzt alle `[data-event-map]` einer Seite. Kontaktseite: Hero bleibt, Social Media als Fact mit Link und Plattform-Icon (Handle = Instagram, volle URL wird erkannt; Icons instagram, facebook, linkedin, youtube neu), Öffnungszeiten als Darstellung `hours` des Tabellen-Blocks (Feld „Darstellung“, Automatik: Kontaktseite = Öffnungszeiten, sonst Tabelle; `hours.css`)
- [ ] **Heroes**: `c-hero`, rooms-hero, notes-hero (CSS identisch), note-hero, member/event/newsletter-Header → `sections/hero` mit Varianten
- [ ] **Horizontale Media-Card**: `c-blog-card` + `content-card` (page-card) zusammenführen
- [x] **Avatar** → `utilities/avatar` (Member-Seite oder freie Daten) + `avatar.css`: einheitlicher Initialen-Platzhalter (Markenverlauf, weiße Schrift; im Team-Strip weiterhin Projektfarbe). Umgestellt: noteCard, note, teamMemberCard, project-Team-Strip, member, Testimonial. `newsletter.php` ebenfalls umgestellt (Autor-Block und Footer; Visual-Test unverändert). **Offen:** Größen der Wrapper noch pro Komponente, doppelte `.author-avatar`-Regeln in `notes.css` und `newsletter.css`
- [x] **Modal:** ein Verhalten (`public/assets/js/modal.js`, einmal im Footer geladen) statt `page-cta-modal.js`, `project-form-modal.js`, Inline-`onclick` und eigenem Script im Newsletter-Teaser: `[data-modal-open="<id>"]` öffnet, `[data-modal-close]` schließt, Klick auf den Hintergrund und ESC schließen, Fokus geht zurück zum Auslöser, `dialog[data-modal-auto-open="<ms>"]` öffnet beim Laden (`shared/modal` Parameter `autoOpen`), `window.mmhModal.open/close` für Skripte. Das alte Auslöser-Attribut der Projektformulare (`data-form-modal-trigger`, Writer-Mark im Web-Plugin) wird weiter verstanden. Umgestellt: Newsletter-Einträge, Newsletter-Teaser/-Übersicht, Seiten-CTA-Modal, Projektformulare. Browser-geprüft (Playwright): öffnen, ESC, Schließen-Knopf, Hintergrund, Fokus zurück, Eintrags-Modal, keine JS-Fehler. **Nicht geprüft:** Auto-Öffnen des CTA-Modals und die Projektformular-Auslöser (kein passender Content lokal). **Offen:** `events-calendar-modal` auf `gs-c-modal`, falls es noch ein eigenes Modal gibt
- [x] **Profilkopf (Event-Look):** `sections/profile-header` (Slots `media`, `overlay`, `intro`; `shape` wide 16:9 oder round (runder Avatar wie überall)) + `profile-header.css`: Bild mit runden Ecken links, Titelblock (`utilities/page-title`) unten rechts. Event-Detail und Mitgliederseite nutzen ihn; die Kontaktkarte des Mitglieds steht jetzt unter dem Kopf. Entfernt: `.c-event__header/-media/-image/-dateBadge/-intro`, `.profile-image-large`, `.contact-profile-section`, `.member-info`. **Icons:** die drei Inline-SVGs der Kontaktkarte (gefüllt) und der Pfeil im Badge laufen jetzt über `utilities/icon` (ein Linien-Set, Feather-Geometrie, `currentColor`); in Templates/Snippets steht kein eigenes `<svg>` mehr. **Entscheidung:** das Newsletter-Cover bleibt bewusst als eigener Kopf (Bild mit Logo, Titel zentriert). **Nicht geprüft:** Event-Detail (kein Event-Content lokal, nur Syntax). **Offen:** Räume (`rooms.php`/`room.php`, unfertig)
- [x] **Fact (Icon, Titel, Wert):** `utilities/fact` (`icon`, `label`, `value`, `href` für den Wert als Link, `link` für einen zweiten Link darunter) und `utilities/facts` (Slot; `columns` 1/2, `surface` card/plain, `class` zum Platzieren) + `design-system/components/fact.css`: Icon im Markenkreis, kleine Beschriftung in Großbuchstaben, Wert. Umgestellt: Event-Fakten, Kontaktkarte des Mitglieds (E-Mail/Telefon/Adresse), Kontaktblock im Newsletter, `sections/contact-map`. Entfernt: `.c-event__fact*`, `.contact-item-inline`, `.contact-items`, `.contact-info/.contact-item` (newsletter.css). Newsletter-Mail: Inline-Stile für `c-fact*`. **Bewusste Änderungen:** Kontaktkarte nutzt jetzt Beschriftungen und Icon-Kreise statt Zeilen mit Hover-Effekt. **Nicht geprüft:** Event-Detail (Fakten) und E-Mail-Rendering visuell. **Offen:** Raum-Details (`quick-info-item`, Räume unfertig) **Redaktionsblock „Fakten“:** `site/blueprints/blocks/facts.yml` (Liste aus Icon, Beschriftung, Wert, optionalem Link; Spalten 1/2; Darstellung Karte/schlicht) + `site/snippets/blocks/facts.php`, in den gemeinsamen Block-Listen (`layoutContent`, `blocksContent`, Gruppe „Struktur“); neues Feld-Atom `fields/icon.yml` (Auswahl aus dem Icon-Set, wiederverwendbar für weitere Blocks). Test `FactsBlockTest`. **Nicht geprüft:** die Eingabemaske im Panel (nur über die Blueprint-Tests), keine Panel-Vorschau für den Block
- [~] **`theme` und `variant` einheitlich (additiv):** Feld-Atome `fields/blockTheme.yml` (eine Farbwelt für alle Blocks: default, soft, brand, dark, project) und `fields/cardVariant.yml` (auto / Bild oben). `utilities/blocks` setzt `data-theme`/`data-variant` am Block-Wrapper (Werte `default`/`auto` setzen nichts); `design-system/components/theme.css` macht daraus Oberflächen-Tokens (`--surface-bg`, `--surface-fg`), die Atome nutzen (Karte, Fakten); Buttons auf dunklen Flächen folgen der Schriftfarbe. Aktiv für den **Kartenblock** (Anordnung + Farbwelt, per `fields:` am Fieldset in `layoutContent`/`blocksContent`, ohne Änderung am Plugin) und den **Fakten-Block** (Farbwelt). Tests: `BlueprintLayoutTest`, `FactsBlockTest` (Wrapper-Attribute). Im Browser geprüft: alle vier Farbwelten auf Karten. **Offen (braucht Entscheidung):** die Plugin-Blocks haben eigene Farbfelder mit alten Werten (`testimonial.color`: default/project/gold, `divider.color`: light/dark/brand/project, `card.color` unbenutzt: primary/secondary); sie auf `theme` umzustellen hieße Content migrieren (Feldname und Werte stehen im Content) oder die Werte im Block-Snippet übersetzen; außerdem Layout-`highlight` (none/dark/primary/project/light) als gleiche Farbwelt
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

## Phase 7: Atomares Komponenten-System entlang der Kirby-Struktur

**Idee:** Kirby denkt den atomaren Aufbau selbst: **Felder** → **Blocks** (mit Varianten) → **Layouts** → **Seiten**. Kleine Elemente werden zu größeren zusammengesetzt, im Panel wie im Frontend. Wir bauen die Seite genau entlang dieser Hierarchie, statt eine zweite, parallele Komponentenwelt zu pflegen. Die Karten (Phase 5, `utilities/card`) sind das Muster: ein Grundgerüst, Varianten über Parameter, ein Wrapper pro Einsatz. Regel: **ein Baustein, eine Quelle, Varianten über Felder/Parameter, keine Sonderlösungen.**

### Recherche (Stand 2026-10-12, Kirby-Doku)
- **Blocks** rendern über ein Snippet nach Typ (`snippets/blocks/<typ>.php`, Variable `$block`; `echo $block` oder `snippet('blocks/' . $block->type(), ['block' => $block, ...])`). Block-Typen lassen sich in `fieldsets` ordnen und per `type: group` gruppieren ([Blocks-Feld](https://getkirby.com/docs/reference/panel/fields/blocks)).
- **Layouts** (`type: layout`): Zeilen aus Spaltenbreiten (bis 12 Spalten), `fieldsets` bestimmen, welche Blocks in die Spalten dürfen, **`settings`** hängen Felder an jede Layout-Zeile (`fields`/`tabs`, auch als wiederverwendbare Datei `settings: fields/settings` oder per `extends:`). Im Template liefern `$layout->attrs()->feld()` bzw. `$layout->feld()` die Einstellungen, `$column->span(12)` die Spaltenbreite. **Das Layout-Objekt wird nicht automatisch an Block-Snippets gegeben, es muss mit `['layout' => $layout]` übergeben werden** ([Layout-Feld](https://getkirby.com/docs/reference/panel/fields/layout)).
- **Block-Modelle** (`blockModels` im Plugin, Klasse je Block-Typ, erbt von `Kirby\Cms\Block`): eigene Methoden an jeder Stelle verfügbar, an der Kirby mit dem Block arbeitet; mit dem Schlüssel `default` lässt sich die Basisklasse für alle Typen ersetzen ([Block-Modelle](https://getkirby.com/docs/reference/plugins/extensions/block-models)).
- **Snippets mit Slots** (seit 3.9: `snippet('x', slots: true)`, `slot('name')`, im Snippet `$slot`/`$slots->name()`, schachtelbar) als Ersatz für die Closure-Parameter von `utilities/card` ([Snippets](https://getkirby.com/docs/guide/templates/snippets)).
- **Nicht belegt, vor der Umsetzung klären:** Wiederverwendung von Block-Blueprints per `extends` (Felder: **geprüft**, siehe Aufgaben; Block-Blueprints noch nicht), und ob Block-Snippets in der eingesetzten Kirby-Version eigene Controller bekommen. Im Code nachsehen bzw. testen.
- **Schon im Projekt:** `project.php` erzeugt einen Galerie-Block selbst (`new Kirby\Cms\Block([...])`): „virtuelle Blocks“ funktionieren, also kann Code dieselben Block-Snippets nutzen wie Redakteure.

### Die Hierarchie auf unsere Seite abgebildet
| Kirby-Ebene | Rolle | Bei uns |
|---|---|---|
| **Feld** (Atom) | kleinste Eingabe/Ausgabe | Text, Writer inline, Bild, Link, Toggle, `variant`/`theme`-Select; Writer-Marks (`button`, `badge`); im Code kleine Snippets `utilities/avatar`, `image`, `icon`, `badge`, `button` |
| **Block** (Molekül), mit Varianten | ein sinnvolles Element | `card`, `testimonial`, `cta`, `button`, `accordion`/`faq`, `timeline`, `form`, `gallery`, `download`, `hero` (neu), `card-list` (neu: Karten aus Seiten-Query); Variante über Feld `variant` (und `theme`) → `data-variant`; ein Blueprint, ein Snippet `blocks/<typ>.php`, das das Basis-Snippet (z. B. `utilities/card`) aufruft |
| **Layout** (Organismus) | Zeile mit Spalten und Einstellungen | ein gemeinsames Layout-Feld (Breiten, erlaubte Blocks) mit `settings`: Hervorhebung (`highlight`), Breite, Abstand, Veröffentlichung/Ende; ersetzt `wrapperClass`/`highlight` als Parameter von `utilities/layouts` |
| **Seite** | Blueprint + Template | Blueprint legt Tabs/Sektionen und das Layout-Feld fest; das Template rendert nur Layouts und seitentypische Teile (Projekt-Kopf, Termine), kein eigenes Karten-/Hero-Markup |

### Was das ändert
- **Eine Quelle für Redaktion und Code:** was Redakteure im Panel zusammenstecken und was Templates automatisch ausgeben (Projektlisten, Notizen, Newsletter, Teams), läuft durch dieselben Block-Snippets. Listen werden zu Layout-Zeilen mit `card-list`-Blocks (oder virtuellen Blocks).
- **Weniger Spezial-Templates:** `projects`, `themes`, `notes`, `newsletters`, `team` unterscheiden sich vor allem durch Query und Kartenvariante.
- **Sichtbarkeit zentral:** `isTimedContentVisible`/`controllers/blocks.php` werden zur Methode `isVisible()` im Standard-Block-Modell (`default`) und im Layout; Templates prüfen nichts mehr selbst.
- **Panel und Frontend im Gleichklang:** Block-Vorschauen im Panel (Vue im Web-Plugin) und das Frontend-Snippet teilen Varianten und Feldnamen.

### Aufgaben
- [ ] **Bestandsaufnahme:** jede Komponente, jedes Template und jeden Block der Hierarchie zuordnen (Tabelle oben vervollständigen); Doppelungen markieren (Buttons als Roh-Markup, 5 Badge-Vokabulare, Heroes, Modals, Accordion/FAQ-Kopien, Event-Karten, Sektionstitel)
- [x] **Gemeinsames Layout-Feld:** `site/blueprints/fields/layout.yml` (Typ, `size`, Standardlabel, 6 Zeilenbreiten), von `default`, `about`, `project`, `theme` per `extends: fields/layout` genutzt; überschrieben wird nur `label`, `layouts`, `fieldsets` und `settings`. Neue Feld-Atome für die Einstellungen: `fields/layoutHighlight.yml` (neu), `fields/blockPublishDate.yml`/`blockEndDate.yml` (gab es schon, jetzt auch für Layouts; Panel-Beschriftung dort „Anzeigen ab/bis“ statt „Veröffentlichungsdatum/Enddatum“). **Geprüft (Test `BlueprintLayoutTest`):** `extends` ersetzt Listen (`layouts`, `fieldsets`) komplett und führt Maps (`settings.fields`) nach Schlüssel zusammen; Kurzform `feld: fields/datei` in `settings.fields` wird beim Aufbau des Formulars aufgelöst. Ergebnis je Seitentyp identisch zu vorher (Zeilen, Blocks, Einstellungen). **Offen:** gemeinsame Fieldset-Gruppen (die Listen unterscheiden sich je Seitentyp, Vereinheitlichung ändert, was Redakteure im Panel sehen), `home`/`machmit` haben keine Layout-Felder
- [ ] **Gemeinsame Fieldset-Gruppen** (Inhalt, Medien, Aktion) als wiederverwendbare Liste statt Aufzählung je Blueprint
- [~] **Sichtbarkeit als Kirby-Methode:** lokales Plugin `site/plugins/mmh-site/` angelegt (`index.php`, `extensions.php`, `.gitignore`-Ausnahme) mit `isVisible()` für Blocks, Layouts und Seiten (`blockMethods`, `layoutMethods`, `pageMethods`; Regel weiter in `isTimedContentVisible()`, `helpers.php`). `utilities/blocks`, `utilities/layouts`, `default.php`, `rooms.php`, `room.php` fragen nur noch `->isVisible()`; `controllers/blocks.php` gelöscht (Test `VisibilityMethodsTest`, Smoke und Visual grün; `/rooms` liefert wie vorher 500 aus `bookingForm.php:80`). **Offen:** richtige Block-Modelle (`blockModels`, Klasse je Typ mit Logik wie `CardBlock::image()`/`::link()`), `default`-Modell, `src/` mit PSR-4 (Phase 4), ~~die Funktion `isTimedContentVisible()` selbst in die Plugin-Klasse umziehen~~ erledigt (`Mmh\Site\Visibility`)
- [~] **Badge als Atom:** `utilities/badge` (`label`, `theme`, `icon`, `to`/`themeTo` für Änderungen „A → B“ mit Verlauf, `size`) + `design-system/components/badge.css` (Themes als Tokens `--badge-bg-<theme>`: default, primary, secondary, success, warning, error, highlight, `project-*`, `recap|current|upcoming|news`, `team-*`). Ersetzt `status-badge`, `newsletter-badge`, `.badge[data-variant]`, `outlook-badge`, `team-badge` (Snippets `statusBadge`, `stepStatusBadge`, `newsletterItem`, `newsletterEntryCard`, `projectUpdateTeaserCard`, `member.php` sind jetzt Aufrufer; die toten `.timeline-status-pills`-Regeln und die Status-Regeln in `timeline.css`, `card.css`, `newsletter-item.css`, `teamMemberCard.css` sind entfernt). `newsletter-email.php`: Inline-Style jetzt für `c-badge`. **Zustandsfarben als Tokens:** Erfolg, Warnung und Fehler stehen einmal in `design-system/tokens/colors.css` (`--color-state-<zustand>-bg/-fg` kräftig, `-soft-bg/-soft-fg/-border` für Hinweisflächen); Badge, Newsletter-Rückmeldungen und Abmelde-Hinweis nutzen sie (vorher zwei verschiedene Grüns/Rots und feste Hex-Werte im Badge). Warnung hat dunkle Schrift auf dem kräftigen Gelb (Kontrast). **Offen:** `rooms.css` (`--color-success`/`--color-error` mit Fallback-Hex, Räume unfertig). **Bewusste Änderungen:** einheitliche Form und Größe, Newsletter-Badge in der Farbe „news“ (ohne Großbuchstaben). **Offen:** Raum-Badges (`badge-capacity`, `badge-accessible`, Räume unfertig), `eventsListItem__badge` (Events), `variant`/`theme` in den Block-Blueprints, Meta-Zeile `.statusheader` als eigenes Atom (`meta-line`), `button` als Atom
- [x] **Button als Atom:** `utilities/button` (`label`, `href` → Link, sonst `<button>`, `variant` primary/secondary/tertiary, `size`, `shape`, `role` destructive/success, `icon`, `type`, `class`, `attrs` mit `true`/`false`/`null`-Regeln). Aussehen weiter in `buttons.css` (`gs-c-btn`, `data-type/-size/-style/-role`). Umgestellt: Karten-Aktion (`utilities/card`), Kartenblock, Newsletter-Einträge (E-Mail, Website, Mehr lesen, Schließen), Newsletter-Teaser/-Übersicht/-Abmeldung, Seiten-CTA-Modal, Suchleiste, Events (Tickets, Kalender, Mehr Infos, Suche, Filter zurücksetzen, Blättern), Startseite, Projekt-Themenfelder. Der Tippfehler `data-size="regualr"` (Startseite, Newsletter-Teaser) ist damit weg. **Zustandsfarben:** `data-role='success'` neu, `destructive` nutzt jetzt die Tokens `--color-state-*`. **Offen (bewusst):** Filter-Pills (`projects-filter-pill`, `events-filter-pill`, `events-day-chip`: Toggle-Zustand per JS), Räume (`room*`, `bookingForm`), `formModals.php` (Konfiguration für DreamForm), Writer-Mark `button` und Button-Block im Web-Plugin (eigene Kopie), Inline-Mail-Stil in `newsletter-email.php`
- [x] **Zustands-Tokens aus den Paletten:** `--color-state-*` zeigen jetzt auf `pastel-green` (Erfolg), `ripe-mango` (Warnung), `ferocious` (Fehler) statt auf eigene Hex-Werte; Badge, Button und Meldungen teilen sie
- [x] **Meta-Zeile als Atom:** `utilities/meta-line` (Kirby-Slots `start`/`end`, erstes Snippet mit Slots im Projekt) + `design-system/components/meta-line.css`; ersetzt `.statusheader` (Projekt-/Update-/Newsletter-/Tagebuch-/Seiten-Karten, Modal der Newsletter-Einträge). E-Mail-Inline-Stil jetzt für `c-meta-line`. Bewusste Änderung: 2 px weniger Höhe der Update-Karten auf der Startseite. **Regel:** Klassifizierendes (Badge, Chips) links in `start`, das Datum immer rechts in `end` (Tagebuch-Karten, Eintragsseite über den Seitentitel-Slot `before`, Seiten-Karte)
- [x] **Seitentitel/-untertitel als Atom:** `utilities/page-title` (`title`, `subtitle`, `eyebrow`, `tone` default/inverse, `align`, `subtitleTag`; **trägt nie Grid- oder Abstandsklassen**, die Einbettung ist überall gleich: `<div class="grid-item" data-span="1/1">` bzw. `<div class="mb-4">` um den Aufruf) + `design-system/components/page-title.css`: **baut auf dem bestehenden Typosystem (`fonts.css`) auf**: Titel `font-titleXXL` (fett), Untertitel und Eyebrow `font-subheadline`; die CSS-Datei setzt nur den engen Zeilenabstand (0,25 rem), Ausrichtung und den Ton auf Bildern. Kein eigenes Größen-/Gewichtssystem im Atom. Umgestellt: projects, themes, theme, default, about, sitemap, contact, newsletters, newsletter-unsubscribe (Eyebrow), notes (Hero-Bild wie auf allen anderen Seiten, Titel darunter; die eigene Hero-Überlagerung `.notes-hero*` ist entfernt) und note (Hero-Bild, darunter Datum/Tags und Titel; `.note-hero*`/`.note-meta-hero` entfernt, `sections/hero` hat den Parameter `placeholder` für Seiten ohne Cover), project, member (Name + Rolle), home (Willkommen-Titel + Text), newsletter-Cover, event. Fehlerseite: Titel als Atom, Meldung bleibt Fließtext (ein ganzer Satz ist kein Untertitel). Entfernt: `.notes-hero-content h1/p`, `.note-title/-subtitle`, `.newsletter-title/-date`, `.c-event__title`, `.member-name/-role`; leere `<h1>` in `teamsSection.php`. **Sichtbare Änderungen (Visual-Tests angekündigt):** einheitliche Größe und Gewicht, Untertitel überall leicht und größer als Fließtext; `default` und `about` zeigen jetzt Titel/Untertitel aus den Feldern `headline`/`subheadline` (die Blueprints boten sie an, die Templates ignorierten sie), z. B. die Seite „Mehr Informationen“ zeigt dann „MachMit!Haus“ / „Alles was du wissen solltest“; Member-Titel größer; Willkommens-Text der Startseite als Untertitel. **Offen:** `rooms.php`/`room.php` (Räume), `events.php` (dynamischer Titel), Horoskop-Seite, Newsletter-Beschreibung (`description`) bleibt Fließtext unter dem Titel, Hero-Kopfbereiche (Bild + Titel) als `hero`-Block
- [x] **Page-Metas am Seitentitel + Chip-Atom:** `utilities/page-title` hat zwei Slots: `before` (Datum, Tags, Fakten über dem Titel) und `after` (Themenfelder, Tags, Team unter dem Untertitel). Neues Atom `utilities/chip` + `design-system/components/chip.css` (Themes `default`, `accent`, `free`, `tag` mit „#“; Link, wenn `href` gesetzt; `size` regular/small). Umgestellt: Eintrag (Datum + Tags), Event (Countdown, „Kostenlos“, Kategorien), Projekt (Themenfelder, Tags als Chips, Team-Leiste), Mitglied (Team-Badges), Tagebuch-Karten (Tags). Entfernt: `.note-meta/-date/-tags`, `.c-event__pills/-pill*`, `.project-topics/-tags/-tag-chip`, `.team-badges*`. **Offen:** die Themenfeld-Buttons bleiben `gs-c-btn` bis zum Button-Atom; `.tag` in anderen Karten-Metas (Projektkarten, falls vorhanden), Projektliste-Filter-Pills (`projects-filter-pill`)
- [ ] **`layout` an Blocks übergeben:** `utilities/blocks` reicht `['layout' => $layout]` durch, damit Blocks Layout-Einstellungen (Hervorhebung, Hintergrund) auswerten können statt CSS-Sonderfälle (`.project-layout-grid[data-highlight] ...`)
- [~] **Neue Blocks:** `card-list` **erledigt** (siehe unten, inkl. Option „Hervorhebung“: keine / erste Karte / alle; wirkt bei Einträgen (groß, volle Breite) und Newsletter-Ausgaben (Rahmen); Notizen- und Newsletter-Übersicht laufen jetzt über `utilities/card-list`). `hero` **erledigt** als Bildband für den Inhalt (`blocks/hero.php`, `blueprints/blocks/hero.yml`: Bild, Format Panorama/16:9; nutzt `sections/hero` mit den neuen Parametern `image` und `variant`); die Seiten zeigen ihr Cover weiter automatisch über denselben Baustein. **Offen:** Termine-Liste, Newsletter-Teaser, Team-Sektion als Blocks; die Seitentypen auf ein Layout-Feld umstellen (braucht eine Regel für Content ohne Layout); Projektliste mit Gruppierung nach Themenfeld bleibt im Template
- [x] **Kartenliste als Baustein (`utilities/card-list`) in Templates:** Seiten → Karten passend zum Seitentyp an einer Stelle (Parameter `pages`, `columns`); der Block `card-list` und die Templates nutzen sie. Umgestellt: `themes` (Themenfelder), `theme` und `projects` (Projektlisten, Archiv), Mitgliederseite (Projekte, Newsletter, Beiträge). Visual-Tests unverändert grün. **Nachgezogen:** `notes.php` und `newsletters.php` (über die Option `featured`). **Bewusst nicht umgestellt:** `home.php` (Update-Karten brauchen Projekt und Schritt), `team` (Galerien mit Titeln)
- [ ] **Templates ausdünnen:** `projects`, `themes`, `notes`, `newsletters`, `team`, `home` auf Layout + `card-list`/Hero umstellen, jeweils gegen die Visual-Tests (`--expect` für gewollte Änderungen)
- [ ] **Slots statt Closures** in `utilities/card` und `shared/modal` prüfen
- [ ] **Katalogseite** (nur für angemeldete Redakteure, nicht verlinkt): jeder Block-Typ in jeder Variante aus Fixtures als virtuelle Blocks; Grundlage für Visual-Tests (`pages.js`) und die „Wo gehört was hin?“-Doku
- [ ] **Regel durchsetzen:** Roh-Markup bestehender Bausteine im Template (`class="gs-c-btn"`, `class="status-badge"`) per Skript im CI verbieten, sobald der Baustein existiert; PR-Checkliste: „Block oder Variante statt Sonderlösung“
- [ ] **Reihenfolge je Baustein:** Block/Variante anlegen → alle Stellen umstellen → altes CSS/Markup löschen → Visual-Diff prüfen und Änderungen im PR ankündigen

---

## Offene Fragen
- Wie sieht die externe Deploy-Pipeline genau aus (Submodule, rsync, Ausschlüsse)?
- Staging-Host und dessen Config?
- Newsletter-Empfänger (`NewsletterRecipients`, Panel-Section) und der Versand: Formularverarbeitung im Plugin, oder Hauptrepo?
- Soll `gs-mmh-web-plugin` wegen des neuen Inhalts (Formulare + App) umbenannt werden?
- Gehört die App-Integration, die heute in `site/config` und `controllers/` liegt, wirklich ins Web-Plugin, oder in ein eigenes App-Plugin? (Beschluss: ins Plugin; Umbenennung offen)
- Wo liegt die produktive `kirby3-dotenv`-Abhängigkeit?
