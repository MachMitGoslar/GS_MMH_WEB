<?php

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
