<?php

/**
 * Card for one Themenfeld.
 *
 * @var \Kirby\Cms\Page $theme
 */
$countActive = $theme->activeProjects()->count();
$countAll = $theme->projects()->count();
?>
<?php snippet('utilities/card', [
    'variant' => 'theme',
    'image' => $theme->cover(),
    'title' => $theme->headline()->or($theme->title()),
    'subtitle' => $theme->subheadline()->isNotEmpty() ? $theme->subheadline() : null,
    'body' => function () use ($countActive, $countAll) {
        ?>
        <p class="font-footnote mb-3">
          <?= $countActive ?> laufende<?= $countAll > $countActive ? ' von ' . $countAll . ' Projekten' : ($countActive === 1 ? 's Projekt' : ' Projekte') ?>
        </p>
        <?php
    },
    'action' => ['label' => 'Zum Themenfeld', 'href' => $theme->url()],
]) ?>
