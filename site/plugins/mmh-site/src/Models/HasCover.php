<?php

declare(strict_types=1);

namespace Mmh\Site\Models;

use Kirby\Cms\File;

/**
 * The cover of a page: the file chosen in its `cover` field, otherwise the
 * first image of the page, otherwise null. Used by the page models, so
 * templates can rely on `$page->cover()` being a File or null.
 */
trait HasCover
{
    public function cover(): ?File
    {
        return $this->content()->get('cover')->toFile() ?? $this->image();
    }

    /**
     * Focus point of the chosen cover for this page (field `cover_focus`,
     * "x% y%"). Null = the focus of the file applies.
     */
    public function coverFocus(): ?string
    {
        if ($this->content()->get('cover')->toFile() === null) {
            return null;
        }

        return $this->content()->get('cover_focus')->value() ?: null;
    }
}
