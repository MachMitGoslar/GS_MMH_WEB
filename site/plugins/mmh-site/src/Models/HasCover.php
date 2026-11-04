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
}
