<?php

declare(strict_types=1);

namespace Mmh\Site\Blocks;

use Kirby\Cms\Block;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Content\Field;

/**
 * The editor-built card. A card shows either a page ("page" card: cover,
 * headline, text and link come from that page) or what the editor typed
 * ("manual" card). The block snippet asks this model instead of deciding
 * itself.
 */
class CardBlock extends Block
{
    /**
     * The selected page of a page card, otherwise null.
     */
    public function cardPage(): ?Page
    {
        return $this->content()->get('cardType')->value() === 'page'
            ? $this->content()->get('page')->toPage()
            : null;
    }

    /**
     * Where the card leads: the selected page, or the link field of a manual
     * card. Empty string without target.
     */
    public function link(): string
    {
        $page = $this->cardPage();

        return (string) ($page ? $page->url() : $this->content()->get('link')->toUrl());
    }

    /**
     * How the link is shown: "header" (image and title), "button" (footer) or
     * "none". Without a target there is no link at all.
     */
    public function linkStyle(): string
    {
        $mode = $this->content()->get('linkMode')->or('none')->value();

        return $this->link() === '' || !in_array($mode, ['header', 'button'], true) ? 'none' : $mode;
    }

    /**
     * The picture: the cover of the selected page (the page models return a
     * File, a Field or nothing), else the page's first image; for a manual
     * card the chosen file.
     */
    public function image(): ?File
    {
        $page = $this->cardPage();

        if ($page === null) {
            return $this->content()->get('image')->toFile();
        }

        $cover = $page->cover();

        if ($cover instanceof File) {
            $image = $cover;
        } elseif (is_object($cover) && method_exists($cover, 'toFile')) {
            $image = $cover->toFile();
        } else {
            $image = null;
        }

        return $image ?? $page->image();
    }

    public function headline(): Field
    {
        $page = $this->cardPage();

        return $page ? $page->headline()->or($page->title()) : $this->content()->get('headline');
    }

    public function subheadline(): Field
    {
        $page = $this->cardPage();

        return $page ? $page->subheadline() : $this->content()->get('subheadline');
    }

    /**
     * The rich text below the headline, or null (a page card shows the page's
     * text, a manual card its own content blocks).
     */
    public function body(): ?Field
    {
        $page = $this->cardPage();

        if ($page) {
            return $page->text();
        }

        return $this->content()->get('cardType')->value() === 'manual'
            ? $this->content()->get('description_content')
            : null;
    }
}
