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

use Kirby\Cms\Page;
use Mmh\Site\BlockPreview;
use Mmh\Site\Blocks\CardBlock;
use Mmh\Site\PublishDates;
use Mmh\Site\SeoMetadata;
use Mmh\Site\Visibility;

return [
    // Routes of the website (version, redirects, events, feed, not-allowed)
    'routes' => require __DIR__ . '/routes.php',
    // Panel area to send a newsletter (uses the recipients of gs-mmh-web-plugin)
    'areas' => [
        'newsletter-mail' => require __DIR__ . '/areas/newsletter-mail.php',
    ],
    // The end of a timed page must not lie before its start
    'hooks' => [
        'page.update:before' => function ($newPage, $values, $strings) {
            PublishDates::assertOrder($values);
        },
    ],
    // Panel: the preview of a block is the website's own rendering (index.js)
    'api' => [
        'routes' => [
            [
                'pattern' => 'mmh-site/block-preview',
                'method' => 'POST',
                'auth' => true,
                'action' => function () {
                    $type = (string) kirby()->request()->get('type');
                    $content = kirby()->request()->get('content');
                    $model = (string) kirby()->request()->get('model');

                    if (!BlockPreview::isPreviewable($type)) {
                        throw new Kirby\Exception\NotFoundException('No preview for this block type');
                    }

                    $parent = null;

                    if ($model !== '') {
                        $found = $this->parent(trim($model, '/'));
                        $parent = $found instanceof Page ? $found : null;
                    }

                    return [
                        'html' => BlockPreview::document($type, is_array($content) ? $content : [], $parent),
                    ];
                },
            ],
        ],
    ],
    // Template of the block catalog (route in routes.php)
    'templates' => [
        'catalog' => __DIR__ . '/templates/catalog.php',
    ],
    // Block types with logic of their own (blueprints/blocks/<type>.yml)
    'blockModels' => [
        'card' => CardBlock::class,
    ],
    'blockMethods' => [
        'isVisible' => fn () => Visibility::isVisible($this),
    ],
    'layoutMethods' => [
        'isVisible' => fn () => Visibility::isVisible($this),
    ],
    'pageMethods' => [
        'isVisible' => fn () => Visibility::isVisible($this),
        // Resolved SEO and social sharing values of this page
        'seo' => fn (): SeoMetadata => new SeoMetadata($this),
    ],
    // Site-wide SEO defaults, where no page context exists
    'siteMethods' => [
        'seo' => fn (): SeoMetadata => new SeoMetadata(null),
    ],
    // Panel labels of the block types (blueprints/blocks/<type>.yml)
    'translations' => [
        'en' => [
            'field.blocks.accordion.name' => 'Accordion block',
            'field.blocks.divider.name' => 'Divider',
            'field.blocks.faq.name' => 'FAQ Section',
            'field.blocks.testimonial.name' => 'Testimonial',
            'field.blocks.card.name' => 'Card',
            'field.blocks.card.fields.cardType.options.page' => 'Create card from page',
            'field.blocks.card.fields.cardType.options.manual' => 'Create manual card',
            'field.blocks.card.fields.linkMode.label' => 'Link',
            'field.blocks.card.fields.linkMode.options.none' => 'No link',
            'field.blocks.card.fields.linkMode.options.header' => 'Link the header',
            'field.blocks.card.fields.linkMode.options.button' => 'Button in the footer',
        ],
        'de' => [
            'field.blocks.accordion.name' => 'Akkordion Block',
            'field.blocks.divider.name' => 'Trenner',
            'field.blocks.faq.name' => 'FAQ Sektion',
            'field.blocks.testimonial.name' => 'Testimonial',
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
