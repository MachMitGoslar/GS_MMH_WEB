<?php

/**
* @var Kirby\Cms\Site $site
* @var Kirby\Cms\Page $page
* @var bool|null $showTeaser
*/
$showTeaser = $showTeaser ?? true;
$modalPage = $site->find('newsletter-modal');
$modalValue = static function (string $field, string $fallback) use ($modalPage, $site): string {
    if ($modalPage?->{$field}()->isNotEmpty() === true) {
        return (string) $modalPage->{$field}();
    }

    return (string) $site->{$field}()->or($fallback);
};
$modalHeadline = $modalValue('newsletterModalHeadline', 'Newsletter abonnieren');
$modalText = $modalValue('newsletterModalText', 'Erhalte Neuigkeiten aus dem MachMit!Haus direkt per E-Mail.');
$firstNameLabel = $modalValue('newsletterModalFirstNameLabel', 'Vorname');
$lastNameLabel = $modalValue('newsletterModalLastNameLabel', 'Nachname');
$emailLabel = $modalValue('newsletterModalEmailLabel', 'E-Mail-Adresse');
$submitButtonText = $modalValue('newsletterModalSubmitButtonText', 'Anmelden');

if ($modalPage?->newsletterModalPrivacyText()->isNotEmpty() === true) {
    $privacyText = $modalPage->newsletterModalPrivacyText()->kirbytextinline()->value();
} elseif ($site->newsletterModalPrivacyText()->isNotEmpty() === true) {
    $privacyText = $site->newsletterModalPrivacyText()->kirbytextinline()->value();
} else {
    $privacyTextBefore = (string) $site->newsletterModalPrivacyTextBefore()->or('Hiermit erkläre ich mich mit der Übermittlung, Speicherung und Verwendung meiner Daten einverstanden. Ich habe die');
    $privacyLinkText = (string) $site->newsletterModalPrivacyLinkText()->or('Datenschutzinformationen');
    $privacyUrl = (string) $site->newsletterModalPrivacyUrl()->or('https://www.goslar.de/datenschutz');
    $privacyTextAfter = (string) $site->newsletterModalPrivacyTextAfter()->or('gelesen und akzeptiere diese.');
    $privacyText = esc($privacyTextBefore) . ' <a href="' . esc($privacyUrl, 'attr') . '" target="_blank" rel="noopener">' . esc($privacyLinkText) . '</a> ' . esc($privacyTextAfter);
}
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

    'slotContent' => function () use ($firstNameLabel, $lastNameLabel, $emailLabel, $submitButtonText, $privacyText) {
        ?>
        <form class="dreamform newsletter-subscribe-form" action="<?= url('newsletter-anmelden.json') ?>" method="post" novalidate>
          <div class="newsletter-subscribe-honeypot" aria-hidden="true">
            <label for="newsletter-subscribe-website">Website</label>
            <input id="newsletter-subscribe-website" name="website" type="text" tabindex="-1" autocomplete="off">
          </div>

          <div class="dreamform-field-group newsletter-subscribe-field-group">
            <div class="dreamform-field">
              <label class="dreamform-label" for="newsletter-subscribe-first-name"><?= esc($firstNameLabel) ?> <em>*</em></label>
              <input class="dreamform-input" id="newsletter-subscribe-first-name" name="first_name" type="text" autocomplete="given-name" required>
            </div>
            <div class="dreamform-field">
              <label class="dreamform-label" for="newsletter-subscribe-last-name"><?= esc($lastNameLabel) ?> <em>*</em></label>
              <input class="dreamform-input" id="newsletter-subscribe-last-name" name="last_name" type="text" autocomplete="family-name" required>
            </div>
          </div>

          <div class="dreamform-field-group newsletter-subscribe-field-group">
            <div class="dreamform-field newsletter-subscribe-full">
              <label class="dreamform-label" for="newsletter-subscribe-email"><?= esc($emailLabel) ?> <em>*</em></label>
              <input class="dreamform-input" id="newsletter-subscribe-email" name="email" type="email" autocomplete="email" required>
            </div>
          </div>

          <div class="dreamform-field newsletter-subscribe-consent-section">
            <div class="dreamform-checkbox">
              <input id="newsletter-subscribe-privacy" name="privacy_accepted" type="checkbox" value="1" required>
              <label for="newsletter-subscribe-privacy"><?= $privacyText ?></label>
            </div>
          </div>

          <p class="newsletter-subscribe-feedback" role="status" aria-live="polite"></p>

          <div class="newsletter-subscribe-actions">
            <button class="gs-c-btn" data-type="primary" data-size="regular" data-style="pill" type="submit"><?= esc($submitButtonText) ?></button>
          </div>
        </form>
        <?php
    },
]) ?>

<script>
  (() => {
    const dialog = document.getElementById('newsletter-subscribe-modal');
    if (!dialog) return;

    const openButton = document.querySelector('.newsletter-subscribe-open');
    const form = dialog.querySelector('.newsletter-subscribe-form');
    const feedback = dialog.querySelector('.newsletter-subscribe-feedback');
    const firstInput = dialog.querySelector('input[name="first_name"]');

    openButton?.addEventListener('click', () => {
      dialog.showModal();
      window.setTimeout(() => firstInput?.focus(), 20);
    });

    dialog.addEventListener('close', () => openButton?.focus());

    form?.addEventListener('submit', async event => {
      event.preventDefault();

      const submitButton = form.querySelector('button[type="submit"]');
      feedback.textContent = '';
      feedback.dataset.type = '';
      submitButton.disabled = true;

      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          headers: { Accept: 'application/json' },
        });
        const result = await response.json();

        feedback.textContent = result.message || 'Danke für deine Anmeldung.';
        feedback.dataset.type = result.success ? 'success' : 'error';

        if (result.success) {
          form.reset();
          window.setTimeout(() => dialog.close(), 1400);
        }
      } catch {
        feedback.textContent = 'Die Anmeldung konnte nicht gesendet werden. Bitte versuche es später erneut.';
        feedback.dataset.type = 'error';
      } finally {
        submitButton.disabled = false;
      }
    });
  })();
</script>
