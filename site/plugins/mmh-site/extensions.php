<?php

/**
 * Extensions of the site's own plugin, kept in a file of their own so tests can
 * boot a Kirby instance with exactly these (see tests/Unit/VisibilityMethodsTest).
 *
 * Timed content: pages, layouts and blocks share one question, "may this be
 * shown now?" (publish_date / end_date, editors and ?preview see everything).
 * Templates and snippets ask `$block->isVisible()` and never check dates
 * themselves. The rule lives in Mmh\Site\Visibility (src/Visibility.php).
 */

use Mmh\Site\Visibility;

return [
    'blockMethods' => [
        'isVisible' => fn () => Visibility::isVisible($this),
    ],
    'layoutMethods' => [
        'isVisible' => fn () => Visibility::isVisible($this),
    ],
    'pageMethods' => [
        'isVisible' => fn () => Visibility::isVisible($this),
    ],
    // Panel labels of the card block (blueprints/blocks/card.yml)
    'translations' => [
        'en' => [
            'field.blocks.card.name' => 'Card',
            'field.blocks.card.fields.cardType.options.page' => 'Create card from page',
            'field.blocks.card.fields.cardType.options.manual' => 'Create manual card',
            'field.blocks.card.fields.linkMode.label' => 'Link',
            'field.blocks.card.fields.linkMode.options.none' => 'No link',
            'field.blocks.card.fields.linkMode.options.header' => 'Link the header',
            'field.blocks.card.fields.linkMode.options.button' => 'Button in the footer',
        ],
        'de' => [
            'field.blocks.card.name' => 'Karte',
            'field.blocks.card.fields.cardType.label' => 'Kartentyp',
            'field.blocks.card.fields.cardType.options.page' => 'Aus Seite erstellen',
            'field.blocks.card.fields.cardType.options.manual' => 'Direkt erstellen',
            'field.blocks.card.fields.linkMode.label' => 'Verlinkung',
            'field.blocks.card.fields.linkMode.options.none' => 'Keine Verlinkung',
            'field.blocks.card.fields.linkMode.options.header' => 'Header verlinken',
            'field.blocks.card.fields.linkMode.options.button' => 'Button im Footer',
        ],
    ],
];
