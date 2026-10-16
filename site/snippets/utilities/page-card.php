<?php

/**
 * Page card: a whole page as a card (image left on wide cards).
 *
 * @var \Kirby\Cms\Page $page
 * @var string $class
 * @var string $dataSpan
 * @var bool $withChildren List the page's children below the text
 */

$dataSpan ??= '1/1';
$withChildren ??= false;
$cover = $page ? $page->cover() : null;
?>
<?php snippet('utilities/card', [
    'variant' => 'page',
    'tag' => $tag ?? 'article',
    'orientation' => 'horizontal',
    'class' => $class ?? 'grid-item',
    'attrs' => ['data-span' => $dataSpan],
    'image' => $cover && $cover->isNotEmpty() ? $cover : null,
    'imageOptions' => ['sizes' => '(min-width: 768px) 33vw, 100vw', 'alt' => $cover ? (string) $cover->alt() : ''],
    'meta' => $page->date()->isNotEmpty() ? function () use ($page) {
        ?>
        <?php snippet('utilities/meta-line', slots: true) ?>
          <?php slot('end') ?>
            <time class="font-footnote"><?= $page->date()->toDate('d.m.Y') ?></time>
          <?php endslot() ?>
        <?php endsnippet() ?>
        <?php
    } : null,
    'title' => $page->title()->html(),
    'titleHref' => $page->url(),
    'subtitle' => $page->subheadline()->isNotEmpty() ? $page->subheadline()->html() : null,
    'text' => $page->content_text()->isNotEmpty() ? $page->content_text()->kt() : null,
    'body' => $withChildren && $page->children()->isNotEmpty() ? function () use ($page) {
        echo '<ul class="c-card__list">';
        foreach ($page->children() as $child) {
            echo '<li><a href="' . esc($child->url()) . '">' . $child->title()->html() . '</a></li>';
        }
        echo '</ul>';
    } : null,
]) ?>
