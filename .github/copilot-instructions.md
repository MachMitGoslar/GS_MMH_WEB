# MachMit!Haus Goslar Website - AI Coding Guidelines

## Project Overview
This is a **Kirby 5 CMS** website for MachMit!Haus in Goslar, built with PHP 8.1-8.4, using a custom design system. The site manages community projects, events, notes/journal entries and newsletters.

The project follows a **3-layer architecture**:
1. **Design System** (Plugin) - Framework-level, reusable components
2. **Site Components** - MachMit!Haus-specific implementations
3. **Content** - Page templates, blueprints, and content structure

## Architecture & Key Concepts

### Core Structure
- **Backend**: Kirby CMS with file-based content in `/content/` (numbered folders like `1_projects/`, `2_wie-funktioniert-machmit/`)
- **Frontend**: PHP templates in `/site/templates/` with organized snippets in `/site/snippets/`
- **Assets**: Implemented in `/public/assets/`
- **Design System**: Framework CSS in `/public/assets/css/design-system/` + site-specific CSS in `/public/assets/css/site/`
- **Custom Plugin**: `/site/plugins/gs-mmh-web-plugin/` - Pure framework/design system (no site-specific logic)
- **Configuration**: Modular config in `/site/config/` (config.php, routes.php, hooks.php)
- **Helpers**: Site-specific helper functions in `/site/helpers.php`

### Development Workflow
```bash
# Start development server with live reload
ddev start

# Restart running development environment
ddev restart

# Development URL
http://gs-mmh-web.ddev.site
```

## Project Organization

### CSS Architecture
```
/public/assets/css/
├── design-system/              # Framework-level styles
│   ├── tokens/
│   │   ├── colors.css         # Design tokens (color variables)
│   │   └── fonts.css          # Typography system
│   ├── components/
│   │   ├── buttons.css        # Button component system
│   │   ├── forms.css          # Form controls
│   │   ├── badge.css          # Generic badges
│   │   ├── gallery.css        # Image galleries
│   │   ├── timeline.css       # Timeline component
│   │   └── ...                # Other framework components
│   ├── layout/
│   │   ├── grid.css           # 12-column grid system
│   │   └── spacing.css        # Spacing utilities
│   ├── components.css         # Imports all design-system CSS
│   └── layout.css             # Imports layout CSS
├── site/                       # Site-specific styles
│   ├── components/
│   │   ├── projectTeaserCard.css
│   │   ├── teamMemberCard.css
│   │   ├── newsletter.css
│   │   └── eventsList.css
│   ├── layout/
│   │   ├── header.css
│   │   ├── footer.css
│   │   └── hero.css
│   └── site.css               # Imports all site-specific CSS
└── index.css                   # Main entry point (imports both)
```

### Snippet Organization
```
/site/snippets/
├── layout/                     # Site structure components
│   ├── head.php               # <head> content
│   ├── header.php             # Site header with navigation
│   ├── footer.php             # Site footer
│   ├── foot.php               # Scripts before </body>
│   └── mainLayout.php         # Master layout wrapper
├── sections/                   # Large page sections
│   └── hero.php               # Hero section
├── content-types/              # Domain-specific components
│   ├── projects/
│   │   ├── timeline.php       # Project timeline
│   │   ├── statusBadge.php    # Project status indicator
│   │   ├── teaserCard.php     # Project preview card
│   │   └── updateCard.php     # Project update card
│   ├── team/
│   │   ├── memberCard.php     # Team member card
│   │   ├── memberGallery.php  # Team gallery grid
│   │   └── rolesGallery.php   # Role-filtered gallery
│   ├── newsletter/
│   │   ├── newsletterTeaser.php
│   │   ├── item.php
│   │   ├── blogEntries.php
│   │   └── rss_feed.php       # RSS generation
│   ├── events/
│   │   └── listItem.php
│   └── ferienpass/
│       ├── csv_helper.php
│       ├── event_random.php
│       └── events.php
├── integrations/               # External systems
│   └── performace.php         # App tracking snippet
├── utilities/                  # Generic helpers
│   └── content-card.php       # Generic content card
└── blocks/                     # Kirby block snippets
    └── line.php               # (Most blocks now in plugin)
```

### Configuration Files
```
/site/config/
├── config.php                  # Base configuration
├── config.localhost.php        # Local development overrides
├── config.ddev.php            # DDEV container config
├── config.mmh.goslar.de.php   # Production config
├── routes.php                 # Custom routes (newsletter RSS, app APIs)
├── hooks.php                  # Kirby hooks (auto-publish, project status)
└── helpers.php → /site/helpers.php (loaded in config.php)
```

### Plugin Structure (gs-mmh-web-plugin)
**IMPORTANT**: The plugin is now **purely framework/design system**. It contains NO site-specific logic.

```
/site/plugins/gs-mmh-web-plugin/
├── index.php                   # Plugin registration (blocks, snippets only)
├── blueprints/
│   ├── blocks/                # Block blueprints (18 blocks)
│   │   ├── accordion.yml
│   │   ├── button.yml
│   │   ├── card.yml
│   │   ├── timeline.yml
│   │   ├── gallery.yml
│   │   └── ...
│   └── fields/
│       └── buttonType.yml
├── snippets/
│   ├── blocks/                # Block rendering snippets (18 blocks)
│   │   ├── accordion.php
│   │   ├── button.php
│   │   ├── card.php
│   │   ├── timeline.php
│   │   ├── gallery.php
│   │   └── ...
│   └── writer-marks/
│       └── button.php
└── src/
    ├── index.js               # Panel component registration
    ├── design-system.css      # Panel preview styles
    └── panel_components/
        ├── blocks/            # Vue block components
        ├── nodes/             # Vue node components
        └── writer_marks/      # Writer mark components
```

### Content Architecture
- **Collections**: Custom collections in `/site/collections/` (e.g., `project-updates.php`)
- **Controllers**: Page logic in `/site/controllers/` (e.g., `home.php`, `about.php`)
- **Models**: Custom Page models in `/site/models/` (e.g., `default.php`, `project.php`)
- **Blueprints**:
  - Page blueprints in `/site/blueprints/pages/` (e.g., `home.yml`, `project.yml`)
  - Field blueprints in `/site/blueprints/fields/` (e.g., `cover.yml`, `weeklyDates.yml`)
  - Site blueprint in `/site/blueprints/site.yml` for panel structure
  - Section blueprints in `/site/blueprints/sections/` (e.g., `notes.yml`, `projects.yml`)
- **Templates**:
  - Page templates in `/site/templates/` (e.g., `home.php`, `project.php`)
  - Use snippets from organized structure above

## Key Patterns & Conventions

### Kirby Template Structure
```php
// Standard template pattern (UPDATED PATHS)
<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>
<main class="main">
    <!-- Content with grid system -->
    <?php snippet('sections/hero'); ?>

    <!-- Content-type specific components -->
    <?php foreach ($projects as $project): ?>
        <?php snippet('content-types/projects/teaserCard', ['project' => $project]); ?>
    <?php endforeach; ?>
</main>
<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
```

### Helper Functions
Site-specific helper functions are in `/site/helpers.php`:

```php
// Example: Get project status color class
$color = getProjectStatusColor($status); // Returns: 'planning', 'active', 'done', etc.
```

### Component System
- **Framework Components**: Buttons, forms, badges → Plugin provides blocks
- **Site Components**: Project cards, team cards → `/site/snippets/content-types/`
- **Design System**: CSS with BEM-style naming (`gs-c-btn`)
- **Grid System**: Custom grid with `data-span` attributes for layout

### Content Collections
```php
// Collections return filtered/sorted pages
return function ($site) {
    return $site->find('projects')->grandChildren()->sortBy("project_start_date", "desc");
};
```

### Routes & Hooks
Routes and hooks are now in separate config files for clarity:

```php
// /site/config/routes.php - Custom routes
return [
    [
        'pattern' => 'newsletter.xml',
        'action' => function () {
            $content = snippet('content-types/newsletter/rss_feed', [...], true);
            return new Response($content, 'application/xml');
        },
    ],
    // App API routes...
];

// /site/config/hooks.php - Event hooks
return [
    'page.update:after' => function ($newPage, $oldPage) {
        // Auto-update project status when steps change
    },
    'page.changeStatus:after' => function ($newPage, $oldPage) {
        // Auto-set publish dates
    },
];
```

## Already Finished Features
- Styled Home Page with Hero, Project Teasers, Event List
- Newsletter Template matching provided PDF design
- Clean separation between framework (plugin) and site-specific code
- Modular CSS architecture with design tokens
- Organized snippet structure by purpose

## Development Environment
DDEV with PHP 8.4, Xdebug, and VS Code running in a Docker container.
**Base URL**: `http://gs-mmh-web.ddev.site`

## Critical Files to Understand
- `/site/config/config.php` - Main Kirby configuration (loads routes, hooks, helpers)
- `/site/config/routes.php` - Custom route definitions
- `/site/config/hooks.php` - Kirby event hooks
- `/site/helpers.php` - Site-specific helper functions
- `/site/blueprints/site.yml` - Panel dashboard structure
- `/public/assets/css/index.css` - Main CSS entry point
- `/public/assets/css/design-system/components.css` - Framework CSS imports
- `/public/assets/css/site/site.css` - Site-specific CSS imports
- `/site/plugins/gs-mmh-web-plugin/index.php` - Plugin registration (framework only)

## Common Tasks

### Add New Page Type
1. Create blueprint in `/site/blueprints/pages/` (e.g., `gallery.yml`)
2. Create template in `/site/templates/` (e.g., `gallery.php`)
3. Optionally create controller in `/site/controllers/` (e.g., `gallery.php`)
4. Optionally create model in `/site/models/` (e.g., `gallery.php`)
5. Modularize reusable parts into snippets in `/site/snippets/content-types/`
6. Add page-specific styles in `/public/assets/css/site/pages/` if needed

### Create Site-Specific Component
1. Add snippet in `/site/snippets/content-types/{domain}/` (e.g., `content-types/events/eventCard.php`)
2. Add CSS in `/public/assets/css/site/components/` (e.g., `eventCard.css`)
3. Import CSS in `/public/assets/css/site/site.css`
4. Use in templates: `<?php snippet('content-types/events/eventCard', ['event' => $event]); ?>`

### Create Framework Block (Plugin)
1. Add blueprint in `/site/plugins/gs-mmh-web-plugin/blueprints/blocks/` (e.g., `testimonial.yml`)
2. Add snippet in `/site/plugins/gs-mmh-web-plugin/snippets/blocks/` (e.g., `testimonial.php`)
3. Add Vue component in `/site/plugins/gs-mmh-web-plugin/src/panel_components/blocks/` (e.g., `testimonial.vue`)
4. Register block in `/site/plugins/gs-mmh-web-plugin/index.php`:
   ```php
   'blueprints' => [
       'blocks/testimonial' => __DIR__ . '/blueprints/blocks/testimonial.yml',
   ],
   'snippets' => [
       'blocks/testimonial' => __DIR__ . '/snippets/blocks/testimonial.php',
   ],
   ```
5. Register Vue component in `/site/plugins/gs-mmh-web-plugin/src/index.js`

### Create Panel Node
Add blueprint in `/site/plugins/gs-mmh-web-plugin/blueprints/nodes/`, Vue component in `/site/plugins/gs-mmh-web-plugin/src/panel_components/nodes/`, and register in `/site/plugins/gs-mmh-web-plugin/src/index.js`.
Research: https://github.com/getkirby/kirby/tree/main/panel/src/components/Forms/Writer/Nodes

### Create Writer Marks/Buttons
Add blueprint in `/site/plugins/gs-mmh-web-plugin/blueprints/writer-marks/`, Vue component in `/site/plugins/gs-mmh-web-plugin/src/panel_components/writer_marks/`, and register in `/site/plugins/gs-mmh-web-plugin/src/index.js`.
Research: https://github.com/getkirby/kirby/tree/main/panel/src/components/Forms/Writer/Marks

### Style Framework Components
Edit files in `/public/assets/css/design-system/components/` for reusable components.
Ensure they use design tokens from `/public/assets/css/design-system/tokens/`.

### Style Site-Specific Components
Edit files in `/public/assets/css/site/components/` or `/public/assets/css/site/layout/`.
Import new files in `/public/assets/css/site/site.css`.

### Add Custom Route
Edit `/site/config/routes.php` and add new route definition:
```php
[
    'pattern' => 'api/custom.json',
    'action' => function () {
        $data = ['status' => 'ok'];
        return new Response(json_encode($data), 'application/json');
    },
],
```

### Add Event Hook
Edit `/site/config/hooks.php` and add new hook:
```php
'page.create:after' => function ($page) {
    // Custom logic after page creation
},
```

### Add Helper Function
Edit `/site/helpers.php` and add new function:
```php
function customHelper($input): string
{
    // Your logic here
    return $output;
}
```

### Modify Content Structure
1. Update blueprints in `/site/blueprints/pages/`
2. Update corresponding templates in `/site/templates/`
3. Update controllers if needed in `/site/controllers/`
4. Update snippets if needed in `/site/snippets/content-types/`

### Add Collection
Create PHP file in `/site/collections/` with page filtering logic:
```php
// /site/collections/recent-notes.php
return function ($site) {
    return $site->find('notes')
        ->children()
        ->listed()
        ->sortBy('published', 'desc')
        ->limit(5);
};
```

## Key Architecture Principles

1. **Plugin = Framework**: The gs-mmh-web-plugin contains ONLY reusable, generic components. No site-specific routes, hooks, or business logic.

2. **Site = Implementation**: All MachMit!Haus-specific code lives in `/site/` - routes, hooks, helpers, site-specific snippets and CSS.

3. **CSS Separation**: Design tokens and framework components in `design-system/`, site-specific styles in `site/`.

4. **Organized Snippets**: Snippets are organized by purpose - layout, sections, content-types, integrations, utilities.

5. **Modular Config**: Routes, hooks, and helpers are in separate files for clarity and maintainability.

6. **Helper Functions**: Site-specific helper functions in `/site/helpers.php`, loaded in config.

When in doubt, ask: "Could another Kirby site use this?" If yes → Plugin. If no → Site.
