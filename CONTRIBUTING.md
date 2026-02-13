# Contributing to GS_MMH_WEB

Vielen Dank, dass du zum MachMit!Haus Goslar Webprojekt beitragen willst! Dieses Dokument beschreibt den Workflow -- sowohl fuer Entwickler als auch fuer alle, die Fehler melden oder Features vorschlagen moechten.

---

## Fuer alle: Issues & Feature Requests

Du musst nicht programmieren koennen, um zum Projekt beizutragen. Feedback, Fehlermeldungen und Ideen sind genauso wertvoll.

### Bug melden

1. Oeffne ein [neues Issue](../../issues/new?template=bug_report.yml)
2. Beschreibe, was passiert ist und was du erwartet hast
3. Fuege Screenshots oder Browser-Informationen hinzu, wenn moeglich

### Feature vorschlagen

1. Oeffne ein [neues Issue](../../issues/new?template=feature_request.yml)
2. Beschreibe die Idee und warum sie hilfreich waere
3. Wenn moeglich, skizziere wie es aussehen oder funktionieren koennte

---

## Fuer Entwickler: Code beitragen

### Voraussetzungen

- Git
- Docker Desktop + [DDEV](https://ddev.com/)
- Node.js 18+
- PHP 8.3+ (via DDEV oder lokal)

Siehe [DEVELOPMENT_SETUP.md](DEVELOPMENT_SETUP.md) fuer die vollstaendige Einrichtung.

### Workflow

```
1. Branch erstellen
2. Aenderungen entwickeln & testen
3. Code formatieren & Lint-Checks bestehen
4. Pull Request erstellen
```

### 1. Branch erstellen

Erstelle immer einen Branch vom aktuellen `main`:

```bash
git checkout main
git pull origin main
git checkout -b <type>/<kurze-beschreibung>
```

**Branch-Namenskonvention:**

| Prefix      | Verwendung                        | Beispiel                          |
|-------------|-----------------------------------|-----------------------------------|
| `feature/`  | Neue Funktionen                   | `feature/room-booking-calendar`   |
| `fix/`      | Fehlerbehebungen                  | `fix/newsletter-print-layout`     |
| `docs/`     | Nur Dokumentation                 | `docs/update-readme`              |
| `refactor/` | Code-Umstrukturierung             | `refactor/snippet-organization`   |
| `style/`    | Rein visuelle Aenderungen (CSS)   | `style/mobile-nav-spacing`        |

### 2. Entwickeln & Testen

```bash
# DDEV starten
ddev start

# Seite im Browser oeffnen
ddev launch

# Bei Plugin-Aenderungen: Panel-Assets neu bauen
cd site/plugins/gs-mmh-web-plugin
npm run build
```

**Pruefe vor dem Commit:**

- [ ] Seite laesst sich ohne PHP-Fehler aufrufen
- [ ] Aenderungen funktionieren auf Desktop und Mobil
- [ ] Panel-Funktionen sind nicht beeintraechtigt
- [ ] Keine Konsolenfehler im Browser

### 3. Code formatieren & Lint-Checks

Der Code muss den Projektstandards entsprechen. Nutze die vorhandenen Skripte:

```bash
# Alles formatieren (JS, CSS, PHP)
npm run format

# Lint-Checks ausfuehren
npm run lint
```

**Standards:**

| Sprache | Tool         | Standard                          |
|---------|--------------|-----------------------------------|
| PHP     | PHP-CS-Fixer | PSR-12                            |
| JS/Vue  | Prettier     | 2 Spaces, Single Quotes, Semicolons |
| CSS     | Stylelint    | Standard-Regeln                   |

Bei Commits wird automatisch `npm run pre-commit` via Husky ausgefuehrt.

### 4. Pull Request erstellen

```bash
# Aenderungen committen
git add <dateien>
git commit -m "type(scope): beschreibung"

# Branch pushen
git push origin <branch-name>
```

Erstelle dann einen Pull Request auf GitHub gegen `main`.

**Commit-Nachricht Format:**

```
type(scope): kurze beschreibung

feat(rooms): add equipment list to booking form
fix(newsletter): resolve print layout overlap
docs(readme): add contributing guidelines
style(header): fix mobile menu z-index
refactor(config): split routes into separate file
```

**Pull Request Checkliste:**

- [ ] Branch ist aktuell mit `main`
- [ ] Code ist formatiert (`npm run format`)
- [ ] Lint-Checks bestehen (`npm run lint`)
- [ ] Aenderungen sind getestet
- [ ] PR-Beschreibung erklaert _was_ und _warum_

---

## Projektstruktur (Kurzuebersicht)

| Verzeichnis              | Inhalt                                |
|--------------------------|---------------------------------------|
| `site/templates/`        | Seiten-Templates (PHP)                |
| `site/snippets/`         | Wiederverwendbare Template-Teile      |
| `site/blueprints/pages/` | Panel-Felddefinitionen (YAML)         |
| `site/config/`           | Kirby-Konfiguration, Routen, Hooks    |
| `site/plugins/`          | Kirby-Plugins (gs-mmh-web-plugin etc.)|
| `public/assets/css/`     | Design System und Seiten-Styles       |

Mehr Details im [README.md](README.md).

---

## Fragen?

Erstelle ein [Issue](../../issues/new) oder sprich das Team direkt an.
