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
$modalId = 'newsletter-subscribe-modal';
$titleId  = $modalId . '-title';

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
    <h2 class="font-title2 color-fg-light mb-3"><?= $site->newsletterTeaserHeadline() ?></h2>
    <p class="font-subheadline color-fg-light mb-3"><?= $site->newsletterTeaserSubheadline() ?></p>
    <p class="font-body color-fg-light"><?= $site->newsletterTeaserText() ?></p>
  </div>
  <div>
    <button
      class="gs-c-btn"
      data-type="primary"
      data-size="regular"
      data-style="pill"
      onclick="document.getElementById('<?= $modalId ?>').showModal()"
    ><?= $site->newsletterTeaserButtonText() ?></button>
  </div>
</div>

<?php snippet('shared/modal', [
    'id'        => $modalId,
    'modifier'  => 'newsletter-subscribe-modal',
    'ariaLabel' => $titleId,

    'slotTitle' => function () use ($titleId, $modalHeadline, $modalText) {
        ?>
        <h2 class="font-headline font-line-height-narrow" id="<?= $titleId ?>"><?= esc($modalHeadline) ?></h2>
        <p class="font-body mt-2"><?= esc($modalText) ?></p>
        <?php
    },

    'slotContent' => function () use ($modalId, $firstNameLabel, $lastNameLabel, $emailLabel, $privacyText) {
        ?>
        <form
          class="newsletter-subscribe-form"
          id="<?= $modalId ?>-form"
          novalidate
        >
          <div class="newsletter-subscribe-form__fields">
            <div class="dreamform-field">
              <label class="dreamform-label" for="<?= $modalId ?>-first-name"><?= esc($firstNameLabel) ?> <span class="dreamform-required" aria-hidden="true">*</span></label>
              <input class="dreamform-input" type="text" id="<?= $modalId ?>-first-name" name="first_name" required autocomplete="given-name">
            </div>
            <div class="dreamform-field">
              <label class="dreamform-label" for="<?= $modalId ?>-last-name"><?= esc($lastNameLabel) ?> <span class="dreamform-required" aria-hidden="true">*</span></label>
              <input class="dreamform-input" type="text" id="<?= $modalId ?>-last-name" name="last_name" required autocomplete="family-name">
            </div>
            <div class="dreamform-field">
              <label class="dreamform-label" for="<?= $modalId ?>-email"><?= esc($emailLabel) ?> <span class="dreamform-required" aria-hidden="true">*</span></label>
              <input class="dreamform-input" type="email" id="<?= $modalId ?>-email" name="email" required autocomplete="email">
            </div>
            <div class="dreamform-field">
              <label class="dreamform-checkbox">
                <input type="checkbox" name="consent" required>
                <span><?= $privacyText ?></span>
              </label>
            </div>
          </div>
          <div class="newsletter-subscribe-form__feedback font-footnote mt-3" role="alert" aria-live="polite" hidden></div>
        </form>
        <?php
    },

    'slotFooter' => function () use ($modalId, $submitButtonText) {
        ?>
        <button type="button" id="<?= $modalId ?>-cancel" class="gs-c-btn" data-type="secondary" data-size="small" onclick="this.closest('dialog').close()">Abbrechen</button>
        <button type="submit" form="<?= $modalId ?>-form" class="gs-c-btn" data-type="primary" data-size="small" data-style="pill"><?= esc($submitButtonText) ?></button>
        <?php
    },
]) ?>

<script>
(function () {
  const form = document.getElementById('<?= $modalId ?>-form');
  if (!form) return;

  form.addEventListener('submit', async function (event) {
    event.preventDefault();

    const submitBtn = document.querySelector('[form="<?= $modalId ?>-form"][type="submit"]');
    const cancelBtn = document.getElementById('<?= $modalId ?>-cancel');
    const feedback  = form.querySelector('.newsletter-subscribe-form__feedback');

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    submitBtn.disabled = true;
    feedback.hidden = true;

    try {
      const data = new FormData(form);
      const response = await fetch('/newsletter-anmelden.json', {
        method: 'POST',
        body: new URLSearchParams(data),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      });

      const result = await response.json();

      feedback.textContent = result.message;
      feedback.dataset.success = result.success ? 'true' : 'false';
      feedback.hidden = false;

      if (result.success) {
        form.reset();
        submitBtn.disabled = true;
        cancelBtn.innerHTML = "Fertig";
      } else {
        submitBtn.disabled = false;
        cancelBtn.innerHTML = 'Schließen';
      }
    } catch {
      feedback.textContent = 'Ein Fehler ist aufgetreten. Bitte versuche es erneut.';
      feedback.dataset.success = 'false';
      feedback.hidden = false;
      submitBtn.disabled = false;
    }
  });
}());
</script>
