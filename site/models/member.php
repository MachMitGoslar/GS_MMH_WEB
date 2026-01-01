<?php

use Kirby\Cms\Block;
use Kirby\Cms\Page;

class MemberPage extends Page
{
    public function cover()
    {
        if ($this->content()->cover() && $this->content()->cover()->exists()) {
            return $this->content()->cover()->toFile();
        } else {
            return $this->image();
        }
    }

    public function description()
    {
        if (! $this->content()->description()->isNotEmpty()) {
            $block = new Block([
                "type" => "text",
                "content" => "Ich hätte so gern eine Beschreibung!!".file_get_contents('http://loripsum.net/api/plaintext'),
            ]);

            return $block->content()->body();
        } else {
            return $this->content()->description();
        }
    }

    public function hasAnyContactInfo()
    {
        return $this->email()->isNotEmpty() || $this->phone()->isNotEmpty();
    }

    public function hasSocialMedia()
    {
        return $this->facebook()->isNotEmpty()
            || $this->instagram()->isNotEmpty()
            || $this->linkedin()->isNotEmpty()
            || $this->github()->isNotEmpty()
            || $this->youtube()->isNotEmpty()
            || $this->x()->isNotEmpty();
    }
}
