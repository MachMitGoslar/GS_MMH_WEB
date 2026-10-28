<?php

declare(strict_types=1);

namespace Mmh\Site;

use Kirby\Cms\Blocks;
use Kirby\Cms\Page;
use Kirby\Cms\User;

/**
 * Sample content for the block catalog (/catalog, editors only): every block
 * type in every variant, as virtual blocks. The catalog shows how the blocks
 * look and is the basis for visual tests and the "where does what belong"
 * documentation. Add an entry whenever a block type or variant is added.
 */
final class Catalog
{
    /**
     * Block types without sample content on purpose (they need real pages,
     * forms or files). The test fails for any other type without entry.
     */
    public const NOT_SHOWN = [
        'accordion' => 'needs a list of questions and answers',
        'button' => 'needs a link target',
        'card-list' => 'lists real pages',
        'cta' => 'contains buttons',
        'download' => 'needs a file',
        'faq2' => 'needs questions',
        'form' => 'needs a DreamForm form',
        'gallery' => 'needs images',
        'hero' => 'needs an image',
        'image' => 'needs an image',
        'searchbar' => 'lists real projects',
        'table' => 'structure of rows, add with the next table variant',
        'timeline' => 'needs project steps',
    ];

    /**
     * @param Page|null $sample a real page to show page cards with; without it those entries are left out
     * @return array<string, list<array{label: string, content: array<string, mixed>}>> by block type
     */
    public static function entries(?Page $sample = null): array
    {
        $text = static fn (string $html): string => json_encode(
            [['type' => 'text', 'content' => ['text' => $html]]],
            JSON_UNESCAPED_UNICODE,
        );

        $manual = [
            'cardType' => 'manual',
            'headline' => 'Beispielkarte',
            'subheadline' => 'Ein kurzer Untertitel',
            'description_content' => $text('<p>Der Text einer Karte, die direkt im Block eingegeben wurde.</p>'),
        ];

        $cards = [
            ['label' => 'Direkt eingegeben, horizontal, ohne Link', 'content' => $manual],
            ['label' => 'Direkt eingegeben, vertikal, Link am Titel', 'content' => $manual + [
                'variant' => 'vertical',
                'linkMode' => 'header',
                'link' => 'https://example.org/',
            ]],
            ['label' => 'Direkt eingegeben, horizontal, Button im Fuß', 'content' => $manual + [
                'linkMode' => 'button',
                'link' => 'https://example.org/',
                'linktext' => 'Mehr erfahren',
            ]],
        ];

        if ($sample !== null) {
            foreach (['horizontal' => 'horizontal', 'vertical' => 'vertical'] as $variant => $label) {
                $cards[] = [
                    'label' => 'Aus Seite „' . $sample->title()->value() . '“, ' . $label . ', Link am Titel',
                    'content' => [
                        'cardType' => 'page',
                        'page' => '- ' . $sample->id(),
                        'variant' => $variant,
                        'linkMode' => 'header',
                    ],
                ];
            }
        }

        $divider = static fn (string $line, string $thickness, string $color, string $spacing): array => [
            'label' => $line . ', ' . $thickness . ', ' . $color . ', Abstand ' . $spacing,
            'content' => ['lineStyle' => $line, 'thickness' => $thickness, 'color' => $color, 'spacing' => $spacing],
        ];

        return [
            'card' => $cards,
            'divider' => [
                $divider('solid', 'thin', 'light', 'regular'),
                $divider('dashed', 'regular', 'dark', 'small'),
                $divider('dotted', 'thick', 'brand', 'large'),
            ],
            'testimonial' => [
                ['label' => 'Freie Angaben', 'content' => [
                    'source' => 'free',
                    'quote' => '<p>Hier kann ich mit anderen etwas bewegen.</p>',
                    'name' => 'Beispiel Person',
                    'jobPosition' => 'Ehrenamtlich',
                    'company' => 'MachMit!Haus',
                ]],
            ],
            'facts' => [
                ['label' => 'Eine Spalte, schlicht', 'content' => [
                    'columns' => '1',
                    'surface' => 'plain',
                    'items' => [
                        ['icon' => 'mail', 'label' => 'E-Mail', 'value' => 'info@example.org', 'link' => 'mailto:info@example.org'],
                        ['icon' => 'map-pin', 'label' => 'Ort', 'value' => 'Markt 7', 'link' => ''],
                    ],
                ]],
                ['label' => 'Zwei Spalten, Karte', 'content' => [
                    'columns' => '2',
                    'surface' => 'card',
                    'items' => [
                        ['icon' => 'calendar', 'label' => 'Wann', 'value' => 'Jeden Montag', 'link' => ''],
                        ['icon' => 'map-pin', 'label' => 'Wo', 'value' => 'MachMit!Haus', 'link' => ''],
                    ],
                ]],
            ],
        ];
    }

    /**
     * The block types that have a snippet in the site or in the web plugin.
     *
     * @param list<string> $snippetDirs directories holding blocks/<type>.php
     * @return list<string>
     */
    public static function typesWithSnippet(array $snippetDirs): array
    {
        $types = [];

        foreach ($snippetDirs as $dir) {
            foreach (glob($dir . '/*.php') ?: [] as $file) {
                $types[basename($file, '.php')] = true;
            }
        }

        $types = array_keys($types);
        sort($types);

        return $types;
    }

    /**
     * One block per call, so every sample gets the wrapper of utilities/blocks.
     *
     * @param array<string, mixed> $content
     */
    public static function blocks(string $type, array $content, Page $parent): Blocks
    {
        return Blocks::factory([['type' => $type, 'content' => $content]], ['parent' => $parent]);
    }

    /**
     * Only admins and editors see the catalog.
     */
    public static function allowedFor(?User $user): bool
    {
        return $user !== null && in_array($user->role()->name(), ['admin', 'editor'], true);
    }

    /**
     * The virtual page the catalog template renders on: not indexed.
     */
    public static function page(): Page
    {
        return Page::factory([
            'slug' => 'catalog',
            'template' => 'catalog',
            'content' => ['title' => 'Block-Katalog', 'robots' => 'noindex'],
        ]);
    }
}
