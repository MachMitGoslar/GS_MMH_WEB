# MachMit!Haus Goslar Website - AI Coding Guidelines

## Project Overview
This is a **Kirby 4 CMS** website for MachMit!Haus in Goslar, built with PHP 8.1-8.3, using **Tailwind CSS** and a custom design system. The site manages community projects, events, and newsletters.

## Architecture & Key Concepts

### Core Structure
- **Backend**: Kirby CMS with file-based content in `/content/` (numbered folders like `1_projects/`, `2_wie-funktioniert-machmit/`)
- **Frontend**: PHP templates in `/site/templates/` with reusable snippets in `/site/snippets/`
- **Assets**: implemented in `/public/assets`
- **Design System**: Modular CSS components in `/public/assets/css/design-system/`

### Development Workflow
```bash
# Restart running development environment
ddev restart

# Start development server with live reload
ddev start
```

### Content Architecture
- **Collections**: Custom collections defined in `/site/collections/` (e.g., `project-updates.php`)
- **Controllers**: Additional Page logic in `/site/controllers/` (e.g., `home.php` fetches events)
- **Models**: Custom Page models in `/site/models/` (e.g., `ProjectPage.php` for project-specific methods)
- **Blueprints**: 
  - Page blueprints in `/site/blueprints/pages/` (e.g., `projects.yml`)
  - Field blueprints in `/site/blueprints/fields/` (e.g., `projectEntry.yml`) 
  - Site blueprint in `/site/blueprints/site.yml` for panel structure
  - Section Blueprints in `/site/blueprints/sections/` (e.g., `notes.yml`)
- **Views**: 
 - Page Templates in `/site/templates/` (e.g., `project.php` for project pages)
 - Snippets in `/site/snippets/` (e.g., `components/gs-c-card.php` for design system cards)
- **Structure**:
  - **Pages** on the front page consits of:
   - Blueprint controlling the CMS fields in the panel (e.g. `home.yml`)
   - Template rendering the content (e.g. `home.php`)
   - Controller for additional logic (e.g. `home.php` in controllers)
   - Model for custom methods (e.g. `HomePage.php` in models)
   - CSS Adjustments in `/public/assets/css/index.css`
  - **Components**:
   - Reusable snippets in `/site/snippets/components/` (e.g. `gs-c-btn.php`)
   - CSS components in `/public/assets/css/design-system/components/` (e.g. `gs-c-btn.css`)
   - Blueprints for component fields in `/site/blueprints/fields/` (e.g. `button.yml`) or sections in `/site/blueprints/sections/` (e.g. `button-section.yml`)
   - Panel adjustments in `/site/plugins/gs-mmh-web-plugin/index.php` and `/site/plugins/gs-mmh-web-plugin/*` for custom panel features and panel previews
  - **Spectial HTML Features**:
   - Newsletter template in `/site/templates/newsletter.php` with blueprint in `/site/blueprints/pages/newsletter.yml`
   - Debugging setup with Xdebug and test script in `/debug_test.php`
  - **Non HTML Features**:
   - JSON Rendering in `/site/templates/gs-mmh-web-plugin/index.php` for API-like data access




## Key Patterns & Conventions

### Kirby Template Structure
```php
// Standard template pattern
<?php snippet('general/head'); ?>
<?php snippet('general/header'); ?>
<main class="main">
    <!-- Content with grid system -->
</main>
<?php snippet('general/footer'); ?>
```

### Component System
- **Snippets**: Reusable components in `/site/snippets/components/`
- **Design System**: CSS components with BEM-style naming (`gs-c-btn`)
- **Grid System**: Custom grid with `data-span` attributes for layout


### Content Collections
```php
// Collections return filtered/sorted pages
return function ($site) {
    return $site->find('projects')->grandChildren()->sortBy("project_start_date", "desc");
};
```

## Already finished Features
- Styled Home Page with Hero, Project Teasers, Event List
- Newsletter Template matching provided PDF design


## Development Environment

-- DDEV with PHP 8.4, Xdebug, and VS Code running in a Docker container. The Basepath is: `http://gs-mmh-web.ddev.site` --

## Critical Files to Understand
- `/site/config/config.php` - Kirby configuration
- `/site/blueprints/site.yml` - Panel dashboard structure  
- `/browsersync.js` - Auto-reload configuration
- `/site/stylesheets/tailwind.css` - Custom Tailwind layers
- `/public/assets/css/design-system/components.css` - Design system imports

## Common Tasks
- **Add new page type**: Create blueprint in `/site/blueprints/pages/`, template in `/site/templates/` and modularize reusable parts into snippets/components while maintaining design system consistency and base design.
- **Create new component**: Add snippet in `/site/snippets/components/`, CSS in `/public/assets/css/design-system/components/`, and blueprint in `/site/blueprints/fields/` or `/site/blueprints/sections/` and handle neccessary preview elements in `/site/plugins/gs-mmh-web-plugin/`.
- **Style components**: Edit files in `/public/assets/css/design-system/components/`
- **Create Block** for the Panel: Add blueprint in `/site/plugins/gs-mmh-web-plugin/blueprints/blocks/`, Vue component in `/site/plugins/gs-mmh-web-plugin/src/panel_components/blocks/`, and register in `/site/plugins/gs-mmh-web-plugin/src/index.js`.
- **Create Node** for the Panel: Add blueprint in `/site/plugins/gs-mmh-web-plugin/blueprints/nodes/`, Vue component in `/site/plugins/gs-mmh-web-plugin/src/panel_components/nodes/`, and register in `/site/plugins/gs-mmh-web-plugin/src/index.js`. Research here: `https://github.com/getkirby/kirby/tree/main/panel/src/components/Forms/Writer/Nodes`
- **Create Marks or Buttons** for the Panel Writer: Add blueprint in `/site/plugins/gs-mmh-web-plugin/blueprints/writer-marks/` or `/site/plugins/gs-mmh-web-plugin/blueprints/writer-buttons/`, Vue component in `/site/plugins/gs-mmh-web-plugin/src/panel_components/writer_marks/` or `/site/plugins/gs-mmh-web-plugin/src/panel_components/writer_buttons/`, and register in `/site/plugins/gs-mmh-web-plugin/src/index.js`. Research here: `https://github.com/getkirby/kirby/tree/main/panel/src/components/Forms/Writer/Marks`
- **Style Pages**: Edit `/public/assets/css/index.css` or create page-specific CSS files.
- **Modify content structure**: Update blueprints and corresponding templates
- **Add collections**: Create PHP files in `/site/collections/` with page filtering logic