<?php

/**
 * Glance: "auf einen Blick", the fact grid with an optional map beside it.
 * One layout for event details and the contact page. The facts are the slot
 * (utilities/fact items); the map is a Mapbox canvas filled by event-map.js
 * (data attributes, no inline script). The caller loads layout/mapbox in the
 * head and assets/js/event-map.js after the page.
 *
 * <?php snippet('sections/glance', ['map' => [...]], slots: true) ?>
 *   <?php snippet('utilities/fact', [...]) ?>
 * <?php endsnippet() ?>
 *
 * @var array|null $map lat, lng, token, label (marker), aria (description), href + linkLabel (optional route link)
 * @var string|null $class Extra classes
 * @var string|null $label aria-label of the section (default: Auf einen Blick)
 * @var \Kirby\Template\Slots $slots
 */

$map ??= null;
$class ??= null;
$label ??= 'Auf einen Blick';
$hasMap = is_array($map) && ($map['token'] ?? '') !== '' && ($map['lat'] ?? null) !== null && ($map['lng'] ?? null) !== null;
?>
<section class="<?= esc(trim('c-glance ' . ($class ?? ''))) ?>" data-has-map="<?= $hasMap ? 'true' : 'false' ?>" aria-label="<?= esc($label) ?>">
  <?php snippet('utilities/facts', ['class' => 'c-glance__facts'], slots: true) ?>
    <?= $slot ?>
  <?php endsnippet() ?>

  <?php if ($hasMap) : ?>
    <div class="c-glance__map">
      <div
        class="c-glance__mapCanvas"
        id="<?= esc('glance-map-' . substr(md5(uniqid('', true)), 0, 8)) ?>"
        data-event-map
        data-lat="<?= esc($map['lat']) ?>"
        data-lng="<?= esc($map['lng']) ?>"
        data-token="<?= esc($map['token']) ?>"
        data-label="<?= esc($map['label'] ?? '') ?>"
        role="img"
        aria-label="<?= esc($map['aria'] ?? 'Karte') ?>"
      ></div>
        <?php if (!empty($map['href'])) : ?>
        <a class="c-glance__mapLink" href="<?= esc($map['href']) ?>" target="_blank" rel="noopener noreferrer">
            <?php snippet('content-types/events/eventIcon', ['icon' => 'map-pin', 'size' => 14]) ?>
          <span><?= esc($map['linkLabel'] ?? 'Route planen') ?></span>
        </a>
        <?php endif ?>
    </div>
  <?php endif ?>
</section>
