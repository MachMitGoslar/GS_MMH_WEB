<?php

/** @var \Kirby\Cms\Block $block */
use Kirby\Toolkit\Html;
use Kirby\Toolkit\Str;

// Override des Kirby-Core-Blocks: lokale Bilder laufen über utilities/image
// (WebP, srcset, width/height, lazy loading), externe URLs bleiben unverändert.
$alt = $block->alt();
$caption = $block->caption();
$crop = $block->crop()->isTrue();
$link = $block->link();
$ratio = $block->ratio()->or('auto');
$file = null;
$src = null;

if ($block->location() == 'web') {
    $src = $block->src()->esc();
} elseif ($file = $block->image()->toFile()) {
    $alt = $alt->or($file->alt());
}
?>
<?php if ($file || $src) : ?>
<figure<?= Html::attr(['data-ratio' => $ratio, 'data-crop' => $crop], null, ' ') ?>>
  <?php if ($link->isNotEmpty()) : ?>
  <a href="<?= Str::esc($link->toUrl()) ?>">
  <?php endif ?>
    <?php if ($file) : ?>
      <?php snippet('utilities/image', [
          'file' => $file,
          'role' => 'content',
          'sizes' => '(min-width: 1200px) 1200px, 100vw',
          'alt' => $alt->value(),
      ]) ?>
    <?php else : ?>
      <img src="<?= $src ?>" alt="<?= $alt->esc() ?>" loading="lazy" decoding="async">
    <?php endif ?>
  <?php if ($link->isNotEmpty()) : ?>
  </a>
  <?php endif ?>

  <?php if ($caption->isNotEmpty()) : ?>
  <figcaption>
    <?= $caption ?>
  </figcaption>
  <?php endif ?>
</figure>
<?php endif ?>
