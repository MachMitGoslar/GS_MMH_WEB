<?php
/**
 * Newsletter Item Component
 *
 * Displays a newsletter item with card design including image, date, and description
 *
 * @var \Kirby\Cms\Page $newsletter - The newsletter page object
 * @var string $class - Additional CSS classes (optional)
 * @var bool $featured - Highlighted look (optional)
 */

$class = trim(($class ?? '') . (($featured ?? false) ? ' newsletter-item--featured' : ''));
// Use publish_date if available, otherwise fallback to published/modified date
$publishDate = null;
if ($newsletter->publish_date()->isNotEmpty()) {
    $publishDate = $newsletter->publish_date();
} elseif ($newsletter->published() && !$newsletter->published()->isEmpty()) {
    $publishDate = $newsletter->published();
} else {
    $publishDate = $newsletter->modified();
}
$publishTimestamp = mmhTimestampValue($publishDate);

// Get the first image from the newsletter, or use a fallback
$heroImage = $newsletter->cover();
if ($heroImage && !$heroImage->exists()) {
    // Try to get first image from newsletter content
    $heroImage = $newsletter->images()->last();
}
?>
<?php snippet('utilities/card', [
    'variant' => 'newsletter',
    'class' => trim('c-newsletterTeaserCard grid-item ' . $class),
    'attrs' => ['data-span' => '1/4'],
    'image' => $heroImage && $heroImage->isNotEmpty() ? $heroImage : null,
    'imageOptions' => [
        'ratio' => '2:1',
        'sizes' => '(min-width: 1024px) 25vw, (min-width: 640px) 50vw, 100vw',
        'alt' => $newsletter->title()->value(),
    ],
    'meta' => function () use ($publishTimestamp) {
        ?>
        <?php snippet('utilities/meta-line', slots: true) ?>
          <?php slot('start') ?>
            <?php snippet('utilities/badge', ['label' => 'Newsletter', 'theme' => 'news', 'icon' => 'mail']) ?>
          <?php endslot() ?>
          <?php slot('end') ?>
            <time class="font-footnote" datetime="<?= date('Y-m-d', $publishTimestamp) ?>"><?= date('d.m.Y', $publishTimestamp) ?></time>
          <?php endslot() ?>
        <?php endsnippet() ?>
        <?php
    },
    'title' => $newsletter->title()->html(),
    'subtitle' => $newsletter->greeting_text()->isNotEmpty() ? $newsletter->greeting_text()->excerpt(120) : null,
    'action' => ['label' => 'Newsletter lesen', 'href' => $newsletter->url()],
]) ?>
