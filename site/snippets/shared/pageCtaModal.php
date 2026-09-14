<?php

/**
 * Auto-opening CTA modal for selected pages.
 *
 * @var Kirby\Cms\Page $page
 */

if ($page->cta_modal_enabled()->toBool() !== true) {
    return;
}

$headline = $page->cta_modal_headline()->or('Mach mit!');
$text = $page->cta_modal_text();
$continueText = $page->cta_modal_continue_text()->or('Weiter');
$continueUrl = trim((string) ($page->cta_modal_continue_url()->toUrl() ?? ''));

if ($text->isEmpty() === true) {
    return;
}

$modalId = 'page-cta-modal-' . $page->slug();
$titleId = $modalId . '-title';

snippet('shared/modal', [
    'id' => $modalId,
    'modifier' => 'page-cta-modal',
    'ariaLabel' => $titleId,

    'slotTitle' => function () use ($headline, $titleId) {
        ?>
        <h2 class="font-title page-cta-modal__title" id="<?= esc($titleId, 'attr') ?>"><?= esc($headline) ?></h2>
        <?php
    },

    'slotContent' => function () use ($text) {
        ?>
        <div class="font-body page-cta-modal__text">
          <?= $text->kirbytext() ?>
        </div>
        <?php
    },

    'slotFooter' => function () use ($continueText, $continueUrl) {
        ?>
        <?php if ($continueUrl !== '') : ?>
          <a class="gs-c-btn" data-type="primary" data-size="regular" data-style="pill" href="<?= esc($continueUrl, 'attr') ?>">
            <?= esc($continueText) ?>
          </a>
        <?php else : ?>
          <button class="gs-c-btn" data-type="primary" data-size="regular" data-style="pill" type="button" data-cta-modal-close>
            <?= esc($continueText) ?>
          </button>
        <?php endif ?>
        <?php
    },
]);
?>

<?= js('assets/js/page-cta-modal.js?version=' . filemtime(kirby()->root('index') . '/assets/js/page-cta-modal.js')) ?>
