<?php

/**
 * Profile header: media on the left (event photo, member portrait), the title
 * block on the right. One look for event and member pages.
 * Slots: media (image, avatar or placeholder), overlay (optional, laid over the
 * media, e.g. a date badge) and intro (usually utilities/page-title).
 *
 * <?php snippet('sections/profile-header', ['shape' => 'wide'], slots: true) ?>
 *   <?php slot('media') ?> ... <?php endslot() ?>
 *   <?php slot('intro') ?> ... <?php endslot() ?>
 * <?php endsnippet() ?>
 *
 * A div on purpose: the global `header` rule in layout/header.css forces
 * height: 3rem, overflow: hidden and position: sticky.
 *
 * @var string|null $shape wide (16:9 media, default: events) or round (round portrait: people, like every avatar)
 * @var \Kirby\Template\Slots $slots
 */

$shape ??= 'wide';
?>
<div class="c-profile-header" data-shape="<?= esc($shape) ?>">
  <div class="c-profile-header__media">
    <?= $slots->media() ?>
    <?php if ($overlay = $slots->overlay()) : ?>
        <?= $overlay ?>
    <?php endif ?>
  </div>
  <div class="c-profile-header__intro">
    <?= $slots->intro() ?>
  </div>
</div>
