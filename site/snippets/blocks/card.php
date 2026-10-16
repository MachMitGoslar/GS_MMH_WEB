<?php

/**
 * Card block: the editor-built card, rendered with the one card layout
 * (utilities/card). Overrides the copy in gs-mmh-web-plugin.
 *
 * @var \Kirby\Cms\Block $block
 */

use Kirby\Cms\File;

$cardType = $block->cardType()->value();
$page = $cardType === 'page' ? $block->page()->toPage() : null;

// Link target: page cards link to the selected page, manual cards to the link field
$link = $page ? $page->url() : $block->link()->toUrl();
$linkMode = $block->linkMode()->or('none')->value();

if (empty($link)) {
    $linkMode = 'none';
}

$hasButtonLink = $linkMode === 'button';
$hasHeaderLink = $linkMode === 'header' && $hasButtonLink === false;

// Cover image: page models may return a File, a Field or nothing at all
if ($page) {
    $cover = $page->cover();

    if ($cover instanceof File) {
        $image = $cover;
    } elseif (is_object($cover) === true && method_exists($cover, 'toFile') === true) {
        $image = $cover->toFile();
    } else {
        $image = null;
    }

    $image ??= $page->image();
} else {
    $image = $block->image()->toFile();
}

$headline = $page ? $page->headline()->or($page->title()) : $block->headline();
$subheadline = $page ? $page->subheadline() : $block->subheadline();
$text = $cardType === 'manual' ? $block->description_content() : ($page ? $page->text() : null);

// Footer button styling, same structure as the button block
$buttonType = $block->buttontype()->toObject();

if ($block->isEmpty()) {
    return;
}

snippet('utilities/card', [
    'variant' => 'block',
    'tag' => 'div',
    'orientation' => $block->variant()->value() === 'vertical' ? 'vertical' : 'horizontal',
    'image' => $image,
    'imageOptions' => ['ratio' => '1:1', 'alt' => $image ? $image->alt()->value() : ''],
    'placeholder' => false,
    'imageHref' => $hasHeaderLink ? $link : null,
    'imageLabel' => $hasHeaderLink ? ($headline->value() ?: ($image ? $image->alt()->value() : '')) : null,
    'title' => $headline,
    'titleHref' => $hasHeaderLink ? $link : null,
    'body' => function () use ($subheadline, $text) {
        ?>
        <?php if ($subheadline->isNotEmpty()) : ?>
          <h4 class="font-subheadline font-line-height-narrow"><?= $subheadline ?></h4>
        <?php endif ?>
        <?php if ($text) : ?>
          <div class="c-card__richtext">
            <?php foreach ($text->toBlocks() as $contentBlock) : ?>
                <?= $contentBlock ?>
            <?php endforeach ?>
          </div>
        <?php endif ?>
        <?php
    },
    'actions' => $hasButtonLink ? function () use ($link, $buttonType, $block) {
        ?>
        <?php snippet('utilities/button', [
            'label' => esc($block->linktext()->or('Mehr erfahren')),
            'href' => $link,
            'variant' => $buttonType->color()->or('primary')->value(),
            'size' => $buttonType->size()->or('regular')->value(),
            'shape' => $buttonType->style()->or('pill')->value(),
            'attrs' => ['data-button' => 'true'],
        ]) ?>
        <?php
    } : null,
]);
