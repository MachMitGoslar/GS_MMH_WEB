<?php

/**
 * Avatar stack: overlapping round avatars of a few persons, each linking to
 * their page, with a "+n" counter for the rest. Size and position come from
 * the surrounding component (e.g. the overlay of a card).
 *
 * @var \Kirby\Cms\Pages $persons Member pages
 * @var int $limit How many avatars are shown (default 2)
 * @var int $size Pixel size of the generated avatar images (default 96)
 */

$limit ??= 2;
$size ??= 96;

if ($persons->count() === 0) {
    return;
}
?>
<div class="c-avatar-stack avatar-stack" data-reveal>
  <?php foreach ($persons->limit($limit) as $index => $person) : ?>
    <a href="<?= $person->url() ?>" class="c-avatar-stack__item" style="--stack-index: <?= $index ?>" title="<?= $person->title()->html() ?>">
        <?php snippet('utilities/avatar', ['person' => $person, 'name' => $person->title()->value(), 'size' => $size]) ?>
    </a>
  <?php endforeach ?>
  <?php if ($persons->count() > $limit) : ?>
    <span class="c-avatar-stack__more font-footnote">+<?= $persons->count() - $limit ?></span>
  <?php endif ?>
</div>
