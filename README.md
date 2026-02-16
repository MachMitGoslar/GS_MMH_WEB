# GS_MMH_WEB

Website and application platform for [MachMit!Haus Goslar](https://mmh.goslar.de) -- a community maker space in Goslar, Germany. Built with Kirby 5 CMS.

## Tech Stack

| Layer       | Technology                                     |
|-------------|------------------------------------------------|
| CMS         | [Kirby 5.3+](https://getkirby.com/) (file-based) |
| PHP         | 8.3 / 8.4                                      |
| Database    | MariaDB 10.11 (optional, for analytics)        |
| Web Server  | Nginx-FPM (DDEV) / Apache                      |
| CSS         | Custom design system, no framework              |
| JS          | Vanilla ES6+, Mapbox GL, FSLightbox            |
| Dev Env     | [DDEV](https://ddev.com/) (Docker)             |
| Build       | [kirbyup](https://github.com/johannschopplich/kirbyup) (plugin panel assets) |

## Quick Start

```bash
# 1. Clone the repository
git clone --recurse-submodules https://github.com/MachMitGoslar/GS_MMH_WEB.git
cd GS_MMH_WEB

# 2. Install PHP dependencies
composer install

# 3. Start DDEV
ddev start

# 4. Open in browser
ddev launch
```

Panel: https://gs-mmh-web.ddev.site/panel

See [DEVELOPMENT_SETUP.md](DEVELOPMENT_SETUP.md) for detailed setup, debugging, and troubleshooting.

## Features

### Content Management
- **Projects** -- Status-tracked community projects (Planung, Vorbereitung, Aktiv, Auswertung, Abgeschlossen) with milestone updates
- **Rooms** -- Bookable spaces with equipment lists, pricing, and an integrated booking request system
- **Events** -- Recurring events with calendar, Ferienpass (vacation pass) integration
- **Newsletters** -- Bi-monthly newsletters with weekly calendar, upcoming dates, article sections, RSS feed, and print-to-PDF
- **Notes/Blog** -- Articles with rich block-based content
- **Team** -- Staff profiles with photos and roles

### Integrations
- **Room Booking** -- Form submission, email notifications, optional Google Calendar sync
- **Ferienpass API** -- JSON endpoints for the MachMit mobile app
- **Newsletter RSS** -- Feed at `/newsletter.xml`
- **App Analytics** -- Request tracking to MariaDB for the mobile app
- **Git Content** -- Automatic content versioning via git

### Design System
- Custom CSS tokens (colors, typography, spacing)
- Figma-to-code token pipeline
- Responsive grid (1/8/12 columns across breakpoints)
- Component library: buttons, cards, badges, forms, gallery, timeline, newsletter, hero

## Project Structure

```
GS_MMH_WEB/
├── .ddev/                      # DDEV Docker configuration
├── content/                    # Kirby file-based content (gitignored)
├── kirby/                      # Kirby CMS core
├── public/                     # Web root (docroot)
│   ├── assets/
│   │   ├── css/
│   │   │   ├── index.css       # Main entry point
│   │   │   ├── design-system/  # Tokens, components, layout
│   │   │   └── site/           # Page-specific styles
│   │   ├── js/                 # Lightbox, etc.
│   │   └── svg/                # Icons and logos
│   └── index.php               # Application entry point
├── site/
│   ├── blueprints/             # Panel field definitions (YAML)
│   │   └── pages/              # 21 page blueprints
│   ├── config/
│   │   ├── config.php          # Main config (loads routes, hooks, api)
│   │   ├── routes.php          # Custom route definitions
│   │   ├── hooks.php           # Lifecycle hooks
│   │   ├── api.php             # API endpoints
│   │   └── config.*.php        # Environment overrides
│   ├── controllers/            # Page controllers
│   ├── snippets/
│   │   ├── layout/             # head, header, footer, mainLayout
│   │   ├── sections/           # hero
│   │   ├── blocks/             # download, gallery, searchbar, line
│   │   ├── content-types/      # Domain-specific snippets
│   │   │   ├── projects/       # Timeline, status badges
│   │   │   ├── rooms/          # Booking handler, email templates
│   │   │   ├── newsletter/     # RSS feed, teaser cards
│   │   │   ├── ferienpass/     # Mobile app API responses
│   │   │   ├── notes/          # Note cards
│   │   │   └── team/           # Member galleries
│   │   ├── integrations/       # Performance tracking
│   │   └── utilities/          # Shared content cards
│   ├── templates/              # 19 page templates
│   ├── plugins/                # Kirby plugins
│   └── helpers.php             # Global helper functions
├── storage/                    # Cache, sessions, logs (gitignored)
├── vendor/                     # Composer dependencies
├── composer.json
├── package.json
├── DEVELOPMENT_SETUP.md
├── DEBUG_SETUP.md
└── PRECOMMIT_SETUP.md
```

## Page Templates

| Template         | Description                                      |
|------------------|--------------------------------------------------|
| `home`           | Homepage with events, project updates, newsletter teaser |
| `default`        | Generic page with layout blocks                  |
| `about`          | About MachMit!Haus                               |
| `contact`        | Contact form (DreamForm)                         |
| `events`         | Event calendar and listings                      |
| `projects`       | Projects archive with status filtering           |
| `project`        | Single project with timeline                     |
| `project_step`   | Project milestone / update                       |
| `rooms`          | Room listing overview                            |
| `room`           | Room detail with booking form                    |
| `newsletters`    | Newsletter archive                               |
| `newsletter`     | Single newsletter with weekly calendar, articles |
| `notes`          | Blog / notes archive                             |
| `note`           | Single blog post                                 |
| `team`           | Team overview                                    |
| `member`         | Team member profile                              |
| `calendar`       | Calendar view                                    |
| `app_performance`| Mobile app analytics dashboard                   |
| `special`        | Special / info pages                             |

## Routes

| Pattern                      | Method | Response | Description                          |
|------------------------------|--------|----------|--------------------------------------|
| `newsletter.xml`             | GET    | XML      | Newsletter RSS feed                  |
| `/app/(:any)`                | GET    | JSON     | Mobile app request tracker           |
| `/app/ferienpass.json`       | GET    | JSON     | Random Ferienpass event              |
| `/app/ferienpass_index.json` | GET    | JSON     | All Ferienpass events                |
| `booking-request.json`       | POST   | JSON     | Room booking form submission         |
| `booking-request.json`       | GET    | JSON     | Booking API status check             |

## Hooks

| Hook                        | Trigger                              | Action                                                |
|-----------------------------|--------------------------------------|-------------------------------------------------------|
| `page.update:after`         | Project step saved                   | Syncs parent project status from `project_status_to`  |
| `page.changeStatus:after`   | Newsletter/note published            | Auto-sets `published` date to today                   |
| `page.changeStatus:after`   | Booking request status change        | Sends approval/denial email, creates Google Calendar event |

## Plugins

| Plugin              | Description                                        |
|---------------------|----------------------------------------------------|
| `gs-mmh-web-plugin` | Main custom plugin: blocks, writer marks, routes, hooks (git submodule, see its [README](site/plugins/gs-mmh-web-plugin/README.md)) |
| `gs-mmh-signage`    | Digital signage screen management                  |
| `git-content`       | Automatic git versioning of content changes        |
| `kirby-dreamform`   | Form builder and submission handler (v2.1)         |
| `locator`           | Map/location picker field (v2.0)                   |
| `helpers`           | Shared utility functions                           |

## Design System

### CSS Architecture

```
public/assets/css/
├── index.css                          # Entry point (imports below)
├── design-system/
│   ├── tokens/                        # Design tokens (colors, fonts, spacing)
│   ├── components/                    # Reusable component styles
│   │   ├── buttons.css, badge.css, forms.css
│   │   ├── gallery.css, timeline.css, video.css
│   │   ├── header.css, footer.css, hero.css
│   │   ├── newsletter.css (incl. @media print)
│   │   └── ...
│   ├── layout/
│   │   ├── grid.css                   # Responsive grid (1 / 8 / 12 cols)
│   │   └── spacing.css                # Margin/padding utilities
│   └── components.css, layout.css     # Barrel imports
└── site/
    ├── components/                    # Site-specific component overrides
    ├── layout/                        # Site layout styles
    └── pages/                         # Per-page styles
```

### Breakpoints

| Name    | Width      | Grid Columns |
|---------|------------|--------------|
| Mobile  | < 490px    | 1            |
| Tablet  | 490-767px  | 1            |
| Desktop | 768-1039px | 8            |
| Wide    | 1040-1439px| 12           |
| Max     | > 1440px   | 12 (83rem max-width) |

### Color Palette

| Token family       | Usage                  |
|--------------------|------------------------|
| `ripe-mango`       | Primary / brand accent |
| `dead-pixel`       | Neutrals / text        |
| `cornflower`       | Planning status        |
| `ferocious`        | Error / alert          |
| `pastel-green`     | Success                |
| `violaceous-greti` | Review status          |

## Configuration

### Environment Configs

| File                              | Environment      |
|-----------------------------------|------------------|
| `site/config/config.php`          | Base (all envs)  |
| `config.localhost.php`            | Local PHP server |
| `config.gs-mmh-web.ddev.site.php` | DDEV            |
| `config.mmh.goslar.de.php`       | Production       |

### Google Calendar (Optional)

To enable Google Calendar integration for room bookings:

1. Create a Google Cloud Service Account
2. Download the JSON key to `site/config/google-service-account.json`
3. Set the path in `config.php`:
   ```php
   'google' => ['calendar' => ['credentials' => __DIR__ . '/google-service-account.json']]
   ```
4. Share each room's Google Calendar with the service account email

## Development

### NPM Scripts

| Command            | Description                            |
|--------------------|----------------------------------------|
| `npm run serve`    | PHP server + BrowserSync (live reload) |
| `npm run format`   | Format JS, CSS, PHP (Prettier + php-cs-fixer) |
| `npm run lint`     | Lint JS, CSS, PHP with auto-fix        |
| `npm run pre-commit` | Format + lint (runs via husky)      |

### Code Style

| Language | Tool            | Config               | Standard  |
|----------|-----------------|----------------------|-----------|
| PHP      | PHP-CS-Fixer    | `.php-cs-fixer.php`  | PSR-12    |
| JS       | ESLint          | `eslint.config.js`   | Custom    |
| CSS      | Stylelint       | `.stylelintrc.json`  | Standard  |
| All      | Prettier        | `.prettierrc`        | 2-space, single quotes |

### Plugin Development

The main custom plugin (`gs-mmh-web-plugin`) is a git submodule with its own build pipeline:

```bash
cd site/plugins/gs-mmh-web-plugin
npm install
npm run dev    # Hot reload during development
npm run build  # Production build
```

See the [plugin README](site/plugins/gs-mmh-web-plugin/README.md) for block/component documentation.

## Documentation

| File                                              | Content                       |
|---------------------------------------------------|-------------------------------|
| [DEVELOPMENT_SETUP.md](DEVELOPMENT_SETUP.md)      | DDEV setup, PHP server, debugging, troubleshooting |
| [DEBUG_SETUP.md](DEBUG_SETUP.md)                   | Xdebug configuration          |
| [PRECOMMIT_SETUP.md](PRECOMMIT_SETUP.md)          | Git hooks and linting          |
| [Plugin README](site/plugins/gs-mmh-web-plugin/README.md) | Blocks, marks, routes, hooks |

## License

GPL-3.0
