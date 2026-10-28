<?php

declare(strict_types=1);

namespace Mmh\Site;

use Kirby\Cms\Page;

/**
 * Newsletters and notes get today's date as `published` the first time they
 * are listed, unless an editor set one. Moved from the web plugin's hook.
 */
final class PublishedDate
{
    private const TEMPLATES = ['newsletter', 'note'];

    /** Hook `page.changeStatus:after`. */
    public static function afterStatusChange(Page $newPage, Page $oldPage): void
    {
        if (!in_array($newPage->intendedTemplate()->name(), self::TEMPLATES, true)) {
            return;
        }

        if ($newPage->status() === 'listed' && $oldPage->status() !== 'listed' && $newPage->published()->isEmpty()) {
            $newPage->update(['published' => date('Y-m-d')]);
        }
    }
}
