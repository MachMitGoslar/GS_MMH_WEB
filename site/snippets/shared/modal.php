<?php

/**
 * Shared modal component — wraps native <dialog> with standardized slots.
 *
 * @var string                          $id           dialog[id]
 * @var string|null                     $modifier     extra CSS class (e.g. 'newsletter-entry-modal')
 * @var \Kirby\Cms\File|string|null     $hero         hero image — File object or URL string
 * @var string|null                     $heroAlt      alt text for hero image
 * @var string|null                     $ariaLabel    aria-labelledby value (ID of a title element inside a slot)
 * @var callable|null                   $slotTitle    title section slot
 * @var callable|null                   $slotContent  main body content slot
 * @var callable|null                   $slotFooter   footer / actions slot
 */

$modifier   = $modifier  ?? '';
$heroAlt    = $heroAlt   ?? '';
$ariaLabel  = $ariaLabel ?? null;
$slotTitle  = $slotTitle  ?? null;
$slotContent = $slotContent ?? null;
$slotFooter = $slotFooter ?? null;

$heroUrl = null;
if (!empty($hero)) {
    $heroUrl = is_string($hero) ? $hero : $hero->url();
}

$classes = trim('gs-c-modal ' . $modifier . ($heroUrl ? ' gs-c-modal--has-hero' : ''));

?>
<dialog
  class="<?= esc($classes, 'attr') ?>"
  id="<?= esc($id, 'attr') ?>"
  <?= $ariaLabel ? 'aria-labelledby="' . esc($ariaLabel, 'attr') . '"' : '' ?>
  onclick="if(event.target===this)this.close()"
>
  <div class="gs-c-modal__close-bar">
    <button class="gs-c-modal__close" type="button" onclick="this.closest('dialog').close()" aria-label="Schließen">✕</button>
  </div>

  <?php if ($heroUrl): ?>
    <img class="gs-c-modal__hero" src="<?= esc($heroUrl, 'attr') ?>" alt="<?= esc($heroAlt, 'attr') ?>">
  <?php endif ?>

  <div class="gs-c-modal__body" onclick="event.stopPropagation()">
    <?php if ($slotTitle): ?>
      <div class="gs-c-modal__title-section">
        <?php ($slotTitle)() ?>
      </div>
    <?php endif ?>

    <?php if ($slotContent): ?>
      <div class="gs-c-modal__content">
        <?php ($slotContent)() ?>
      </div>
    <?php endif ?>

    <?php if ($slotFooter): ?>
      <div class="gs-c-modal__footer">
        <?php ($slotFooter)() ?>
      </div>
    <?php endif ?>
  </div>
</dialog>
