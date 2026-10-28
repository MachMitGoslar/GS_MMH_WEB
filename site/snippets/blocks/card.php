<?php

/**
 * Card block: the editor-built card, rendered with the one card layout
 * (utilities/card). Overrides the copy in gs-mmh-web-plugin.
 *
 * @var \Kirby\Cms\Block $block
 */

$link = $block->link();
$linkStyle = $block->linkStyle();
$hasButtonLink = $linkStyle === 'button';
$hasHeaderLink = $linkStyle === 'header';

$image = $block->image();
$headline = $block->headline();
$subheadline = $block->subheadline();
$text = $block->body();

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
