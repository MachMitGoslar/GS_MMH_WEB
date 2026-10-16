<?php

/**
 * Profile backdrop: a soft, blurred hero behind the head of a profile page.
 * It shows the person's portrait, blurred and tinted in the color of their team
 * (data-team: staff, volunteer, partner, issuer), and fades out into the page.
 * Purely decorative (aria-hidden). Without a portrait only the tint remains.
 * Place it as the first child of a positioned section; look in profile-header.css.
 *
 * @var \Kirby\Cms\Page $person Member page (cover)
 * @var string|null $team First team of the person
 */

$team ??= null;
$cover = $person->cover();
$cover = $cover instanceof \Kirby\Cms\File ? $cover : null;
?>
<div class="c-profile-backdrop"<?= $team ? ' data-team="' . esc($team) . '"' : '' ?> aria-hidden="true">
  <?php if ($cover) : ?>
        <?php snippet('utilities/avatar', ['person' => $person, 'size' => 480, 'loading' => 'eager']) ?>
  <?php endif ?>
</div>
