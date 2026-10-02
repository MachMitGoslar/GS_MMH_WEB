<?php

/** @var \Kirby\Cms\Block $block */
use Kirby\Toolkit\Html;

$caption = $block->caption();
$crop = $block->crop()->isTrue();
$ratio = $block->ratio()->or('auto');
?>
<figure<?= Html::attr(['data-ratio' => $ratio, 'data-crop' => $crop], null, ' ') ?>>
  <ul class="grid">
    <?php foreach ($block->images()->toFiles() as $image) : ?>
    <li class="grid-item grid-item-span4">
    <a href="<?= $image->width() > 1920 ? $image->resize(1920)->url() : $image->url() ?>" data-fslightbox="gallery">
      <?php snippet('utilities/image', [
          'file' => $image,
          'role' => 'card',
          'sizes' => '(min-width: 1024px) 33vw, 100vw',
          'class' => 'c-gallery-image',
      ]) ?>
    </a>
    </li>
    <?php endforeach ?>
  </ul>
  <?php if ($caption->isNotEmpty()) : ?>
  <figcaption>
        <?= $caption ?>
  </figcaption>
  <?php endif ?>
</figure>