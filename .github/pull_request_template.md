## Beschreibung

<!-- Was wurde geändert und warum? -->

## Art der Änderung

- [ ] Bug Fix
- [ ] Neues Feature
- [ ] Refactoring (keine funktionale Änderung)
- [ ] Style / Design
- [ ] Dokumentation
- [ ] Sonstiges

## Betroffener Bereich

- [ ] Startseite
- [ ] Projekte
- [ ] Räume / Buchung
- [ ] Newsletter
- [ ] Veranstaltungen
- [ ] Team
- [ ] Blog / Notizen
- [ ] Panel (Verwaltung)
- [ ] Design System / CSS
- [ ] Plugin (gs-mmh-web-plugin)
- [ ] Konfiguration / Infrastruktur

## Checkliste

- [ ] Code ist formatiert (`npm run format`)
- [ ] Lint-Checks bestehen (`npm run lint`)
- [ ] Änderungen sind lokal getestet
- [ ] Desktop und Mobil geprüft
- [ ] Keine Konsolenfehler im Browser
- [ ] Panel-Funktionen sind nicht beeinträchtigt

## Screenshots

<!-- Falls visuelle Änderungen: Vorher/Nachher Screenshots einfügen -->

## Visuelle Änderungen

<!--
Der CI-Job "Visual regression" rendert den Basis-Branch und diesen PR und vergleicht beide.
Ändert dieser PR das Aussehen ABSICHTLICH, hake die betroffenen Seiten an. Nur diese Seiten
dürfen abweichen, jede andere Abweichung lässt den Job weiter scheitern. `all` erlaubt jede
Seite. Die Vorher/Nachher/Diff-Bilder hängen als Artefakt "visual-diffs" am Lauf.
Keine visuelle Änderung beabsichtigt: nichts ankreuzen.
Nach dem Bearbeiten des PR-Textes läuft der Check automatisch neu.
-->

<!-- visual-pages:start -->
<!-- erzeugt aus tests/e2e/pages.js: npm run pr-template -->
- [ ] `all` — jede Seite darf sich ändern
- [ ] `home` — Startseite (`/`)
- [ ] `projects` — Projektübersicht (`/projects`)
- [ ] `project` — Projektseite (`/projects/01-goslar-app`)
- [ ] `project-step` — Projektschritt (`/projects/01-goslar-app/version-4-3-0`)
- [ ] `project-archive` — Projektarchiv (`/project-archive`)
- [ ] `notes` — Tagebuch (`/notes`)
- [ ] `note` — Tagebucheintrag (`/notes/eine-neue-website`)
- [ ] `newsletter_index` — Newsletter-Übersicht (`/newsletter`)
- [ ] `newsletter` — Newsletter (`/newsletter/november-2025`)
- [ ] `team` — Team (`/team`)
- [ ] `member` — Teammitglied (`/team/christian`)
- [ ] `about` — Über uns (`/uber-uns`)
- [ ] `informations` — Mehr Informationen (`/informations`)
- [ ] `impressum` — Impressum (`/impressum`)
- [ ] `not-allowed` — Zugriff verweigert (`/not-allowed`)
- [ ] `not-found` — Fehlerseite (404) (`/not-found`)
<!-- visual-pages:end -->

## Verwandtes Issue

<!-- z.B. Closes #123 -->
