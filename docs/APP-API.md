# App-Schnittstelle (Goslar-App)

Die JSON-Endpunkte, die das Dashboard der Goslar-App abruft. Sie sind **öffentlich** (kein Login), nur lesend (`GET`) und leben im Plugin `gs-mmh-web-plugin` (`routes/app-api.php`, Klassen in `classes/`). Das Kartenformat der App steht in [app-card-format.txt](app-card-format.txt).

Basis-URL: `https://<host>/api/…`

## Überblick

| Endpunkt | Liefert | Format |
|---|---|---|
| `GET /api/latest-update` | neuestes Update (Newsletter oder Projektschritt) als Karte | Entry View |
| `GET /api/highlights` | Highlights der zweiten Ansicht | Index View |
| `GET /api/projects/index.json` | alle für die App freigegebenen Projekte | Index View |
| `GET /api/projects/<slug>/card.json` | Karte eines Projekts | Entry View |
| `GET /api/projects/<slug>/index.json` | Project Updates dieses Projekts | Index View |
| `GET /api/{newsletter,notes,app}-cover/<slug>.{jpg,svg}` | generierte Titelbilder (1200 × 630) | Bild |

## Project Updates im Dashboard

Jedes Projekt hat Project Updates (Seiten vom Typ `project_step` unterhalb des Projekts). Sie lassen sich als Dashboard-Karte mit Update-Liste ausliefern. Das Zusammenspiel folgt dem App-Format:

```
Dashboard                      Tippen auf die Karte             Tippen auf einen Eintrag
┌──────────────┐  card.json   ┌─────────────────────┐  index.json  ┌─────────────────────┐
│ Projektkarte │ ───────────▶ │ Liste der Updates   │ ───────────▶ │ Webview: Projektseite│
└──────────────┘  call_to_    └─────────────────────┘  call_to_    └─────────────────────┘
                  action_url                           action_url
```

### Freigabe (Opt-in)

Ein Projekt erscheint nur, wenn im Panel am Projekt der Schalter **Goslar-App → „Updates im App-Dashboard anbieten“** (`app_widget`) aktiv ist. Ohne Freigabe antworten Karte und Liste mit `404`, und das Projekt fehlt in `projects/index.json`.

### Karte: `GET /api/projects/<slug>/card.json`

Entry View. `<slug>` ist der Slug der Projektseite (die URL `/projects/<slug>`).

```json
{
  "title": "MachMit!Wald",
  "description": "Es gibt eine zweite Fläche! Alle Tafeln, die ab dem 01.11.2025 gekauft werden, findet ihr bei der Fläche am Bocksberg.",
  "image_url": "https://<host>/media/pages/projects/02-machmitwald/…/plaketten-1200x630-crop-q88.jpg",
  "call_to_action_url": "https://<host>/api/projects/02-machmitwald/index.json",
  "published_at": "2025-11-01T00:00",
  "widget_type": null
}
```

| Feld | Quelle |
|---|---|
| `title` | Titel des Projekts |
| `description` | Text des **neuesten** Updates (HTML entfernt, auf 160 Zeichen gekürzt); ohne Update der Subtitel des Projekts |
| `image_url` | Cover des Projekts als JPEG 1200 × 630 |
| `call_to_action_url` | der Index des Projekts (nächste Ansicht der App) |
| `published_at` | Datum des neuesten Updates (`YYYY-MM-DDTHH:MM`, Zeitzone Europe/Berlin); ohne Update die letzte Änderung des Projekts |
| `widget_type` | immer `null` |

### Liste: `GET /api/projects/<slug>/index.json`

Index View, **neueste zuerst**. Enthalten sind alle veröffentlichten (gelisteten) Projektschritte, keine Entwürfe.

```json
[
  {
    "id": 763434280,
    "title": "2. MachMit!Wald",
    "description": "Es gibt eine zweite Fläche! …",
    "image_url": "https://<host>/media/pages/projects/…/plaketten-1200x630-crop-q88.jpg",
    "call_to_action_url": "https://<host>/projects/02-machmitwald",
    "published_at": "2025-11-01T00:00"
  }
]
```

| Feld | Quelle |
|---|---|
| `id` | stabile positive Zahl, abgeleitet aus der UUID des Updates. Bleibt gleich, wenn weitere Updates dazukommen oder wegfallen |
| `title` | Anriss (`headline`) des Updates, sonst sein Titel |
| `description` | Beschreibung des Updates (HTML entfernt, auf 160 Zeichen gekürzt), sonst der Anriss |
| `image_url` | Bild des Updates, sonst das Cover des Projekts (JPEG 1200 × 630) |
| `call_to_action_url` | die Projektseite (Webview) |
| `published_at` | Projektstart des Updates (`project_start_date` plus optionale `project_start_time`); ohne Datum seine letzte Änderung |

### Übersicht: `GET /api/projects/index.json`

Index View aller freigegebenen Projekte, das Projekt mit dem neuesten Update zuerst. `call_to_action_url` ist die Projektseite (Webview), `description` der Subtitel, `id` wie oben aus der UUID des Projekts.

### Fehler

Unbekannte oder nicht freigegebene Projekte:

```
HTTP 404
{"status": "error", "message": "Projekt nicht gefunden"}
```

## Seiten und Karten unter /app/

Neben den JSON-Endpunkten unter `/api/` liefert das Plugin ein paar Routen unter `/app/` (Code: `routes/app.php`):

| Endpunkt | Liefert |
|---|---|
| `GET /app/horoskop_card` | Karte „Goslarer Horoskope“ (Entry View) mit zufälligem Sternzeichen-Bild; `call_to_action_url` ist `/app/horoskope` |
| `GET /app/horoskope` | HTML-Seite (Webview) mit den zwölf Tageshoroskopen, Daten von einem n8n-Webhook |
| `GET /app/ferienpass.json?data=<Programm>` | zufälliger Ferienpass-Termin als Karte; Programm 74, wenn `data` fehlt |
| `GET /app/ferienpass_index.json?data=<Programm>` | alle Termine des Programms (Index View) |

Fehler der Ferienpass-Schnittstelle kommen als `{"error": "…"}` mit HTTP 200. Anfragen an `/app/*` werden pro URL und Tag in der Tabelle `app_requests` gezählt (die Horoskop-Routen aktuell nicht, siehe `docs/CLEANUP.md`).

## Die übrigen Endpunkte

- **`/api/latest-update`**: Entry View des neuesten Newsletters oder Projektschritts (`call_to_action_url` verweist auf `/api/highlights`).
- **`/api/highlights`**: Index View mit festen Einträgen (Heute im MM!H, neuestes Update, Projekte, Newsletter, Tagebuch, Über uns, WhatsApp Community).
- **Titelbilder** (`newsletter-cover`, `notes-cover`, `app-cover`): als `.jpg` oder `.svg`. Das JPEG wird beim ersten Abruf erzeugt und unter `media/api-covers/` zwischengespeichert. Voraussetzung: PHP-Erweiterung Imagick.

## Betrieb

- **Zwischenspeicher:** Das Plugin setzt keine eigenen Cache-Header, die Antworten werden bei jedem Abruf berechnet.
- **Bilder:** Die Bilder der Project Updates sind Kirby-Thumbnails unter `media/` und entstehen beim ersten Abruf.
- **Plugin zuerst ausrollen:** Das Hauptrepo ruft nur das Plugin auf. Die Routen liegen im Plugin, deshalb muss es vor dem Hauptrepo deployt sein.

## Wo liegt was?

| Teil | Ort |
|---|---|
| Routen | `site/plugins/gs-mmh-web-plugin/routes/app-api.php` |
| Project Updates | `…/classes/ProjectWidget.php` |
| Latest Update | `…/classes/LatestUpdate.php` |
| Titelbilder | `…/classes/CoverImages.php` |
| Horoskope, Ferienpass, Zähler | `…/classes/Horoscope.php`, `Ferienpass.php`, `AppRequests.php`; Seite `…/snippets/app/horoscope-list.php`; Routen `…/routes/app.php` |
| Freigabe-Schalter | `site/blueprints/pages/project.yml` (`app_widget`) |
| Tests | `…/tests/Unit/` (`ProjectWidgetTest`, `AppApiRoutesTest`, `LatestUpdateTest`, `CoverImagesTest`) |

Lokal prüfen: `curl -s https://<host>/api/projects/<slug>/card.json | jq`
