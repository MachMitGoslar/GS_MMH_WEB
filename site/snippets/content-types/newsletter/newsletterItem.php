<?php
/**
 * Newsletter Item Component
 *
 * Displays a newsletter item with card design including image, date, and description
 *
 * @var \Kirby\Cms\Page $newsletter - The newsletter page object
 * @var string $class - Additional CSS classes (optional)
 */

$class = $class ?? '';
// Use publish_date if available, otherwise fallback to published/modified date
$publishDate = null;
if ($newsletter->publish_date()->isNotEmpty()) {
    $publishDate = $newsletter->publish_date();
} elseif ($newsletter->published() && ! $newsletter->published()->isEmpty()) {
    $publishDate = $newsletter->published();
} else {
    $publishDate = $newsletter->modified();
}

// Get the first image from the newsletter, or use a fallback
$heroImage = $newsletter->cover();
if ($heroImage && ! $heroImage->exists()) {
    // Try to get first image from newsletter content
    $heroImage = $newsletter->images()->last();
}
?>

<article class="c-newsletterTeaserCard <?= $class ?>">
  <div>
    <?php if ($heroImage && $heroImage->isNotEmpty()) : ?>
        <?php $url = $heroImage->crop(800, 400)->url(); ?>
      <img class="hero" src="<?= $url ?>" alt="<?= $newsletter->title()->html() ?>" loading="lazy">
    <?php else : ?>
      <img class="hero" src="https://picsum.photos/800/400?random=newsletter" alt="<?= $newsletter->title()->html() ?>" loading="lazy">
    <?php endif ?>
  </div>
  
  <div class="content">
    <div class="statusheader">
      <div class="newsletter-badge">
        <span class="newsletter-badge-icon">📧</span>
        <span class="newsletter-badge-text">Newsletter</span>
      </div>
      <time class="font-footnote" datetime="<?= $publishDate->toDate('Y-m-d') ?>">
        <?= $publishDate->toDate('d.m.Y') ?>
      </time>
    </div>

    <a href="<?= $newsletter->url() ?>">
      <h3 class="font-headline font-line-height-narrow mb-2"><?= $newsletter->title()->html() ?></h3>
    </a>
    
    <?php if ($newsletter->greeting_text()->isNotEmpty()) : ?>
      <p class="font-body"><?= $newsletter->greeting_text()->excerpt(120) ?></p>
    <?php endif ?>
    
    <a href="<?= $newsletter->url() ?>" class="gs-c-btn" data-type="secondary" data-size="regular" data-style="pill">
      Newsletter lesen
    </a>
  </div>
</article>