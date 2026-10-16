<?php

/**
 * Card for one note (diary entry).
 *
 * @var \Kirby\Cms\Page $note The note page object
 * @var bool $featured Whether this is a featured card (larger layout)
 */

$featured ??= false;
$authors = $note->author()->toPages();
$tags = $note->tags()->split();
?>
<?php snippet('utilities/card', [
    'variant' => 'note',
    'tag' => $tag ?? 'article',
    'size' => $featured ? 'featured' : 'regular',
    'image' => $note->cover(),
    'imageOptions' => $featured
        ? ['role' => 'content', 'ratio' => '12:5', 'sizes' => '(min-width: 1200px) 1200px, 100vw', 'alt' => $note->title()->value()]
        : ['ratio' => '3:2', 'alt' => $note->title()->value()],
    'overlay' => $authors->count() > 0 ? function () use ($authors, $featured) {
        snippet('utilities/avatar-stack', ['persons' => $authors, 'size' => $featured ? 96 : 80]);
    } : null,
    'meta' => function () use ($note, $tags, $featured) {
        ?>
        <?php snippet('utilities/meta-line', slots: true) ?>
          <?php slot('start') ?>
            <?php foreach (array_slice($tags, 0, $featured ? count($tags) : 2) as $tag) : ?>
                <?php snippet('utilities/chip', ['label' => esc($tag), 'theme' => 'tag', 'size' => 'small']) ?>
            <?php endforeach ?>
          <?php endslot() ?>
          <?php slot('end') ?>
            <time datetime="<?= $note->published('c') ?>" class="font-footnote"><?= $note->published() ?></time>
          <?php endslot() ?>
        <?php endsnippet() ?>
        <?php
    },
    'title' => $note->title()->html(),
    'titleHref' => $note->url(),
    'subtitle' => $note->headline()->isNotEmpty() ? $note->headline()->excerpt($featured ? 200 : 80) : null,
    'text' => $note->text()->toBlocks()->first()?->text()->excerpt($featured ? 300 : 220) ?: null,
    'action' => ['label' => 'Weiterlesen', 'href' => $note->url()],
]) ?>
