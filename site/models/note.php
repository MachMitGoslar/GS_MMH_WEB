<?php

/**
 * Page models extend Kirby's default page object.
 *
 * In page models you can define methods that are then available
 * everywhere in Kirby where you call a page of the extended type.
 *
 * In this example, we define the cover method that either returns
 * an image selected in the cover field or the first image in the folder.
 *
 * You can see the method in use in the `note.php` snippet.
 * and in the `site/blueprints/sections/notes.yml` image query
 *
 * We also define a custom date handler here, which keeps date formatting
 * for the published date consistent in templates, snippets and blueprints.
 *
 * More about models: https://getkirby.com/docs/guide/templates/page-models
 */
use Kirby\Cms\Page;

class NotePage extends Page
{
    public function cover()
    {
        return $this->content()->cover()->toFile() ?? $this->image();
    }



    public function published($format = null)
    {
        return parent::date()->toDate($format ?? 'd M, Y');
    }

    public function string_content()
    {
        $string_content = 'test';
        foreach ($this->text()->toBlocks() as $block) {
            if ($block->type() === 'text' || $block->type() === 'accordion' || $block->type() === 'box' || $block->type() === 'heading') {
                $string_content .= $block->text()->body();
            }
        }

        return new Kirby\Cms\Block([
            'type' => 'text',
            'content' => $string_content,
        ]);
    }
}
