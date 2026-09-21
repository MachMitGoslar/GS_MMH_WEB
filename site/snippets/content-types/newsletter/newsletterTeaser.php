<?php

/**
* @var Kirby\Cms\Site $site
* @var Kirby\Cms\Page $page
* @var bool|null $showTeaser
*/
$showTeaser = $showTeaser ?? true;
$modalPage = $site->find('newsletter-modal');
$newsletterForm = \GsMmh\WebPlugin\NewsletterRecipients::formPage();
$modalValue = static function (string $field, string $fallback) use ($modalPage, $site): string {
    if ($modalPage?->{$field}()->isNotEmpty() === true) {
        return (string) $modalPage->{$field}();
    }

    return (string) $site->{$field}()->or($fallback);
};
$modalHeadline = $modalValue('newsletterModalHeadline', 'Newsletter abonnieren');
$modalText = $modalValue('newsletterModalText', 'Erhalte Neuigkeiten aus dem MachMit!Haus direkt per E-Mail.');
?>
<?php if ($showTeaser === true) : ?>
<div class="c-newsletter-teaser grid-item" data-span="1/2">
  <div class="mb-5">
    <h2 class="font-title2 color-fg-light mb-3"><?= $site->newsletterTeaserHeadline() ?></h2>
    <p class="font-subheadline color-fg-light mb-3"><?= $site->newsletterTeaserSubheadline() ?></p>
    <p class="font-body color-fg-light"><?= $site->newsletterTeaserText() ?></p>
  </div>
  <div>
    <button class="gs-c-btn newsletter-subscribe-open" data-type="primary" data-size="regualr" data-style="pill" type="button" aria-haspopup="dialog" aria-controls="newsletter-subscribe-modal"><?=$site->newsletterTeaserButtonText()?></button>
  </div>
</div>
<?php endif ?>

<?php snippet('shared/modal', [
    'id' => 'newsletter-subscribe-modal',
    'modifier' => 'newsletter-subscribe-modal',
    'ariaLabel' => 'newsletter-subscribe-title',

    'slotTitle' => function () use ($modalHeadline, $modalText) {
        ?>
        <h2 class="font-title mb-2" id="newsletter-subscribe-title"><?= esc($modalHeadline) ?></h2>
        <p class="font-body mb-3"><?= esc($modalText) ?></p>
        <?php
    },

    'slotContent' => function () use ($newsletterForm) {
        ?>
        <?php snippet('dreamform/form', [
            'form' => $newsletterForm,
            'attr' => [
                'form' => ['class' => 'dreamform newsletter-subscribe-form'],
                'field' => ['class' => 'dreamform-field'],
                'label' => ['class' => 'dreamform-label'],
                'error' => ['class' => 'dreamform-error'],
                'input' => ['class' => 'dreamform-input'],
                'button' => ['class' => 'gs-c-btn', 'data-type' => 'primary', 'data-size' => 'regular', 'data-style' => 'pill'],
                'success' => ['class' => 'newsletter-subscribe-feedback', 'role' => 'status', 'aria-live' => 'polite', 'tabindex' => '-1'],
                'text' => [
                    'input' => ['class' => 'dreamform-input', 'autocomplete' => 'name'],
                ],
                'email' => [
                    'input' => ['class' => 'dreamform-input', 'autocomplete' => 'email'],
                ],
                'checkbox' => [
                    'field' => ['class' => 'dreamform-field newsletter-subscribe-consent-section'],
                    'input' => ['class' => 'dreamform-checkbox-input'],
                    'row' => ['class' => 'dreamform-checkbox'],
                ],
            ],
        ]) ?>
        <?php
    },
]) ?>

<script>
  (() => {
    const dialog = document.getElementById('newsletter-subscribe-modal');
    if (!dialog) return;

    const openButton = document.querySelector('.newsletter-subscribe-open');
    const form = dialog.querySelector('.newsletter-subscribe-form');
    const firstInput = dialog.querySelector('input[name="first_name"]');
    const success = dialog.querySelector('.newsletter-subscribe-feedback');

    openButton?.addEventListener('click', () => {
      dialog.showModal();
      window.setTimeout(() => firstInput?.focus(), 20);
    });

    dialog.addEventListener('close', () => {
      openButton?.focus();
    });

    if (!form && success) {
      dialog.showModal();
      success.focus();
    }
  })();
</script>
