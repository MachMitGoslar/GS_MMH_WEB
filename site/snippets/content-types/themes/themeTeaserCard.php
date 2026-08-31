<?php
/**
 * Card for one Themenfeld. Mirrors projectTeaserCard so both read the same
 * inside a `ul.grid`.
 *
 * @var \Kirby\Cms\Page $theme
 */
$themeCover = $theme->cover();
$countActive = $theme->activeProjects()->count();
$countAll = $theme->projects()->count();
?>
<li class="c-projectTeaserCard">
  <div class="hero-wrapper">
    <?php if ($themeCover) : ?>
      <img class="hero" src="<?= $themeCover->url() ?>"<?= $themeCover->focus()->isNotEmpty() ? ' style="object-position: ' . $themeCover->focus() . '"' : '' ?> alt="<?= $themeCover->alt() ?>" />
    <?php else : ?>
        <?php snippet('utilities/imagePlaceholder') ?>
    <?php endif; ?>
  </div>
  <div class="content">
    <h3 class="font-headline"><?= $theme->headline()->or($theme->title()) ?></h3>

    <?php if ($theme->subheadline()->isNotEmpty()) : ?>
      <p class="font-footnote mb-3"><?= $theme->subheadline() ?></p>
    <?php endif ?>

    <p class="font-footnote mb-3">
      <?= $countActive ?> laufende<?= $countAll > $countActive ? ' von ' . $countAll . ' Projekten' : ($countActive === 1 ? 's Projekt' : ' Projekte') ?>
    </p>

    <a href="<?= $theme->url() ?>" class="gs-c-btn" data-type="secondary" data-size="regular" data-style="pill">Zum Themenfeld</a>
  </div>
</li>
