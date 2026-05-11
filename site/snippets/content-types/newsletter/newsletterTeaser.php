<?php

/**
* @var Kirby\Cms\Site $site
* @var Kirby\Cms\Page $page
*/
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
<div class="c-newsletter-teaser grid-item" data-span="1/2">
  <div class="mb-5">
    <h2 class="font-title2 color-fg-light mb-3"><?=$site->newsletterTeaserHeadline()?></h2>
    <p class="font-subheadline color-fg-light mb-3"><?=$site->newsletterTeaserSubheadline()?></p>
    <p class="font-body color-fg-light"><?=$site->newsletterTeaserText()?></p>
  </div>
  <div>
    <button class="gs-c-btn newsletter-subscribe-open" data-type="primary" data-size="regualr" data-style="pill" type="button" aria-haspopup="dialog" aria-controls="newsletter-subscribe-modal"><?=$site->newsletterTeaserButtonText()?></button>
  </div>
</div>

<div class="newsletter-subscribe-modal" id="newsletter-subscribe-modal" aria-hidden="true">
  <div class="newsletter-subscribe-backdrop" data-newsletter-subscribe-close></div>
  <section class="newsletter-subscribe-dialog" role="dialog" aria-modal="true" aria-labelledby="newsletter-subscribe-title">
    <button class="newsletter-subscribe-close" type="button" aria-label="Schließen" data-newsletter-subscribe-close>&times;</button>
    <h2 class="font-title mb-2" id="newsletter-subscribe-title"><?= esc($modalHeadline) ?></h2>
    <p class="font-body mb-3"><?= esc($modalText) ?></p>

    <form class="dreamform newsletter-subscribe-form" action="<?= url('api/newsletter/subscribe') ?>" method="post">
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
  </section>
</div>

<script>
  (() => {
    const modal = document.getElementById('newsletter-subscribe-modal');
    if (!modal) return;

    const openButton = document.querySelector('.newsletter-subscribe-open');
    const closeButtons = modal.querySelectorAll('[data-newsletter-subscribe-close]');
    const form = modal.querySelector('.newsletter-subscribe-form');
    const feedback = modal.querySelector('.newsletter-subscribe-feedback');
    const firstInput = modal.querySelector('input[name="first_name"]');

    const openModal = () => {
      modal.setAttribute('aria-hidden', 'false');
      document.documentElement.classList.add('newsletter-subscribe-is-open');
      window.setTimeout(() => firstInput?.focus(), 20);
    };

    const closeModal = () => {
      modal.setAttribute('aria-hidden', 'true');
      document.documentElement.classList.remove('newsletter-subscribe-is-open');
      openButton?.focus();
    };

    openButton?.addEventListener('click', openModal);
    closeButtons.forEach(button => button.addEventListener('click', closeModal));
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') {
        closeModal();
      }
    });

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
          headers: {
            Accept: 'application/json',
          },
        });
        const result = await response.json();

        feedback.textContent = result.message || 'Danke für deine Anmeldung.';
        feedback.dataset.type = result.success ? 'success' : 'error';

        if (result.success) {
          form.reset();
          window.setTimeout(closeModal, 1400);
        }
      } catch (error) {
        feedback.textContent = 'Die Anmeldung konnte nicht gesendet werden. Bitte versuche es später erneut.';
        feedback.dataset.type = 'error';
      } finally {
        submitButton.disabled = false;
      }
    });
  })();
</script>
