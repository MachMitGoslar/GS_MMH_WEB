<?php

declare(strict_types=1);

namespace Mmh\Site;

use Kirby\Cms\Fieldsets;
use Kirby\Cms\Page;
use Throwable;

/**
 * Panel preview of a block: the same snippet the website uses, inside the
 * site's layout classes and stylesheet, as a standalone HTML document for an
 * iframe (srcdoc). One rendering for the page and the panel.
 */
final class BlockPreview
{
    /** The panel shows blocks smaller than the page does, to keep the overview. */
    public const ZOOM = 0.75;

    /**
     * Block types the panel may preview: those with a snippet of their own.
     */
    public static function isPreviewable(string $type): bool
    {
        return preg_match('!^[a-z][a-z0-9-]*$!', $type) === 1
            && is_file(kirby()->root('snippets') . '/blocks/' . $type . '.php');
    }

    /**
     * The panel holds field values in its own form (a pages or files field is a
     * list of objects); the snippets read what is stored (a list of ids).
     * Kirby's form converts one into the other.
     *
     * @param array<string, mixed> $content
     * @return array<string, mixed>
     */
    public static function storedValues(string $type, array $content, ?Page $parent = null): array
    {
        try {
            $fieldset = Fieldsets::factory([$type], ['parent' => $parent ?? Catalog::page()])->first();

            return $fieldset->form($fieldset->fields(), $content)->toStoredValues();
        } catch (Throwable) {
            return $content;
        }
    }

    /**
     * @param array<string, mixed> $content the block's field values as stored
     * @param Page|null $parent the page being edited, so blocks can use their page
     */
    public static function document(string $type, array $content, ?Page $parent = null): string
    {
        $parent ??= Catalog::page();

        try {
            $html = snippet('utilities/blocks', [
                'blocks' => Catalog::blocks($type, $content, $parent),
            ], true);
        } catch (Throwable $e) {
            $html = '<p>' . esc($e->getMessage()) . '</p>';
        }

        // Folded content (accordion, FAQ) is shown unfolded, so the editor sees all of it
        $html = preg_replace('~<details(?![^>]*\sopen)~', '<details open', $html) ?? $html;

        return '<!doctype html><html lang="de"><head><meta charset="utf-8">'
            . '<base href="' . esc(kirby()->url('index'), 'attr') . '/" target="_blank">'
            . css(Stylesheet::bundle())
            . '<style>html,body{background:transparent;margin:0}body{overflow:hidden;zoom:' . self::ZOOM . '}</style>'
            . '</head><body><main class="main"><section class="grid content">'
            . '<div class="grid-item" data-span="1/1">' . $html . '</div>'
            . '</section></main></body></html>';
    }
}
