<?php
/**
 * Contact page
 *
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 */
?>
<?php
$email = $page->email()->value();
$phone = $page->phone()->value();
$website = $page->website()->value();
$mapboxToken = mmhMapboxToken();
$lat = $page->lat()->isNotEmpty() ? $page->lat()->value() : null;
$lng = $page->lng()->isNotEmpty() ? $page->lng()->value() : null;
$hasMap = $mapboxToken !== '' && $lat !== null && $lng !== null;

// Social media: a handle (Instagram) or a full profile URL
$social = trim($page->social()->value());
$socialFact = null;
if ($social !== '') {
    if (preg_match('#^https?://#i', $social)) {
        $host = strtolower(preg_replace('#^www\.#', '', (string) parse_url($social, PHP_URL_HOST)));
        $icon = match (true) {
            str_contains($host, 'instagram') => 'instagram',
            str_contains($host, 'facebook') => 'facebook',
            str_contains($host, 'linkedin') => 'linkedin',
            str_contains($host, 'youtube'), str_contains($host, 'youtu.be') => 'youtube',
            default => 'globe',
        };
        $socialFact = ['icon' => $icon, 'value' => $host . rtrim((string) parse_url($social, PHP_URL_PATH), '/'), 'href' => $social];
    } else {
        $handle = ltrim($social, '@');
        $socialFact = ['icon' => 'instagram', 'value' => '@' . $handle, 'href' => 'https://www.instagram.com/' . rawurlencode($handle) . '/'];
    }
}
?>
<?php snippet('layout/head', slots: true); ?>
<?php if ($hasMap) : ?>
    <?php slot('head') ?>
    <?php snippet('layout/mapbox') ?>
    <?php endslot() ?>
<?php endif ?>
<?php endsnippet() ?>
<?php snippet('layout/header'); ?>

<main class="main main--contact">
  <div class="mb-4">
    <?= snippet('sections/hero') ?>
  </div>

  <div class="c-contact content">

    <?php snippet('utilities/page-title', [
        'title' => $page->title()->html(),
        'after' => $page->intro()->isNotEmpty() ? fn () => print ('<div class="font-body">' . $page->intro()->kt() . '</div>') : null,
    ]) ?>

    <?php snippet('sections/glance', [
        'label' => 'Kontakt',
        'map' => $hasMap ? [
            'lat' => $lat,
            'lng' => $lng,
            'token' => $mapboxToken,
            'label' => strip_tags((string) $page->address()->kt()),
            'aria' => 'Karte: ' . strip_tags((string) $page->address()->kt()),
        ] : null,
    ], slots: true) ?>
      <?php if ($email !== '') : ?>
            <?php snippet('utilities/fact', ['icon' => 'mail', 'label' => 'E-Mail', 'value' => esc($email), 'href' => 'mailto:' . $email]) ?>
      <?php endif ?>
      <?php if ($phone !== '') : ?>
            <?php snippet('utilities/fact', ['icon' => 'phone', 'label' => 'Telefon', 'value' => esc($phone), 'href' => 'tel:' . preg_replace('/[^0-9+]/', '', $phone)]) ?>
      <?php endif ?>
      <?php if ($website !== '') : ?>
            <?php snippet('utilities/fact', ['icon' => 'globe', 'label' => 'Web', 'value' => esc(preg_replace('#^https?://#', '', $website)), 'href' => $website]) ?>
      <?php endif ?>
      <?php if ($page->address()->isNotEmpty()) : ?>
            <?php snippet('utilities/fact', ['icon' => 'map-pin', 'label' => 'Adresse', 'value' => strip_tags((string) $page->address()->kt(), '<br>')]) ?>
      <?php endif ?>
      <?php if ($socialFact) : ?>
            <?php snippet('utilities/fact', ['icon' => $socialFact['icon'], 'label' => 'Social Media', 'value' => esc($socialFact['value']), 'href' => $socialFact['href'], 'external' => true]) ?>
      <?php endif ?>
    <?php endsnippet() ?>

    <div class="designer">
      <?php snippet('utilities/blocks', ['blocks' => $page->text()->toBlocks()]) ?>
    </div>
  </div>
</main>

<?php if ($hasMap) : ?>
    <?= js('assets/js/event-map.js?version=' . filemtime(kirby()->root('index') . '/assets/js/event-map.js')) ?>
<?php endif ?>

<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
