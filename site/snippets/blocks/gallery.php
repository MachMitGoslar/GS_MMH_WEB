<?php
/** @var \Kirby\Cms\Block $block */
$caption = $block->caption();
$crop = $block->crop()->isTrue();
$ratio = $block->ratio()->or('auto');
?>
<figure<?= Html::attr(['data-ratio' => $ratio, 'data-crop' => $crop], null, ' ') ?>>
  <ul class="grid">
    <?php foreach ($block->images()->toFiles() as $image) : ?>
    <li class="grid-item grid-item-span4">
    <a href="<?= $image->url() ?>" data-fslightbox="gallery">
      <img src="<?= $image->url() ?>" alt="<?= $image->alt()->esc() ?>" class="c-gallery-image" />

      <!-- <?= $image?> -->
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