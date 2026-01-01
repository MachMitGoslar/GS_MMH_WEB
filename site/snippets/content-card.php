<?php
/**
 * Content Card Snippet
 * Reusable component for news, projects, reviews, and previews
 *
 * @param object $item - The content item (from structure field)
 */

$layout = $item->layout()->value() ?? 'imageLeft';
$hasImage = false;
$imageUrl = '';
$imageAlt = '';

// Check for image in the item
if (isset($item) && $item->image()->isNotEmpty()) {
    if ($imageFile = $item->image()->toFile()) {
        $hasImage = true;
        $imageUrl = $imageFile->url();
        $imageAlt = $imageFile->alt()->or($item->headline()->value());
    }
} elseif (isset($item) && method_exists($item, 'cover_image') && $item->cover_image()->isNotEmpty()) {
    if ($imageFile = $item->cover_image()->toFile()) {
        $hasImage = true;
        $imageUrl = $imageFile->url();
        $imageAlt = $imageFile->alt()->or($item->headline()->value());
    }
}

$cardClass = 'content-card';
if ($hasImage) {
    $cardClass .= ' content-card--has-image content-card--' . $layout;
}
?>

<div class="<?= $cardClass ?>">
  
  <div class="content-card__content">
    <?php if ($hasImage && $layout === 'imageLeft') : ?>
      <div class="content-card__image content-card__image--left" style="background-image: url('<?= $imageUrl ?>');">
      </div>
    <?php endif ?>
    
    <div class="content-card__text">
      <div class="content-card__title"><?= $item->headline() ?></div>
      
      <?php if ($item->subheadline()->isNotEmpty()) : ?>
        <div class="content-card__subtitle">
            <?= $item->subheadline() ?>
      </div>
      <?php endif ?>
      
      <?php if ($item->content_text()->isNotEmpty()) : ?>
        <div class="content-card__description"><?= $item->content_text()->kt() ?></div>
      <?php endif ?>
      
      <?php if ($item->date()->isNotEmpty()) : ?>
        <div class="content-card__date"><?= $item->date()->toDate('d.m.Y') ?></div>
      <?php endif ?>
      
      <?php if ($item->location()->isNotEmpty()) : ?>
        <div class="content-card__location">📍 <?= $item->location() ?></div>
      <?php endif ?>
      
      <?php if ($item->link()->isNotEmpty()) : ?>
        <div class="content-card__action-link">
        <span>🔗</span>
        <a href="<?= $item->link() ?>" class="content-card__link-text" target="_blank">
            <?= $item->link() ?>
        </a>
        </div>
      <?php elseif ($item->mailto()->isNotEmpty()) : ?>
        <div class="content-card__action-link">
          <span>📖</span> 
          <a href="<?= $item->mailto() ?>" class="content-card__link-text">
            <?= $item->mailto() ?>
          </a>
        </div>
      <?php endif ?>
    </div>
    
    <?php if ($hasImage && $layout === 'imageRight') : ?>
      <div class="content-card__image content-card__image--right">
        <img src="<?= $imageUrl ?>" alt="<?= $imageAlt ?>" loading="lazy">
      </div>
    <?php endif ?>
  </div>
</div>