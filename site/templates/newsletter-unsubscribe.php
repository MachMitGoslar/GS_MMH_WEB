<?php

/**
 * Newsletter unsubscribe page.
 *
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 */

use GsMmh\WebPlugin\NewsletterRecipients;
use Kirby\Toolkit\V;

if (!class_exists(NewsletterRecipients::class)) {
    require_once kirby()->root('plugins') . '/gs-mmh-web-plugin/NewsletterRecipients.php';
}

$token = trim((string) get('token'));
$recipient = $token !== '' ? NewsletterRecipients::findByToken($token) : null;
$status = null;
$message = '';

if (kirby()->request()->is('POST')) {
    $token = trim((string) kirby()->request()->get('token'));
    $email = trim((string) kirby()->request()->get('email'));

    try {
        if ($token !== '') {
            NewsletterRecipients::deleteByToken($token);
        } elseif (V::email($email) === true) {
            NewsletterRecipients::deleteByEmail($email);
        } else {
            throw new Exception('Bitte gib eine gültige E-Mail-Adresse ein.');
        }

        $status = 'success';
        $message = $page->successText()->or('Du wurdest erfolgreich vom Newsletter abgemeldet.')->value();
        $recipient = null;
    } catch (Throwable $exception) {
        $status = 'error';
        $message = $exception->getMessage();
    }
}
?>
<?php snippet('layout/head', slots: true); ?>
<?php endsnippet() ?>
<?php snippet('layout/header'); ?>

<main>
  <div class="mb-4">
    <?= snippet('sections/hero') ?>
  </div>

  <div class="newsletter-unsubscribe">
  <section class="content newsletter-unsubscribe__content">
    <div class="mb-4">
      <?php snippet('utilities/page-title', [
          'eyebrow' => 'MachMit!Haus Newsletter',
          'title' => esc($page->headline()->or('Newsletter abmelden')),
      ]) ?>
    </div>

    <?php if ($status === 'success') : ?>
      <div class="newsletter-unsubscribe__notice" data-type="success">
        <?= esc($message) ?>
      </div>
        <?php snippet('utilities/button', ['label' => 'Zur Startseite', 'href' => url()]) ?>
    <?php else : ?>
        <?php if ($status === 'error') : ?>
        <div class="newsletter-unsubscribe__notice" data-type="error">
            <?= esc($message) ?>
        </div>
        <?php endif ?>

      <div class="newsletter-unsubscribe__intro font-body mb-5">
        <?= $page->intro()->kt() ?>
      </div>

      <form class="dreamform newsletter-unsubscribe__form" method="post">
        <?php if ($recipient !== null) : ?>
          <input type="hidden" name="token" value="<?= esc($token, 'attr') ?>">
          <p class="font-body mb-4">
            Möchtest du <strong><?= esc($recipient['email']) ?></strong> vom Newsletter abmelden?
          </p>
        <?php else : ?>
            <?php if ($token !== '' && kirby()->request()->is('GET')) : ?>
            <div class="newsletter-unsubscribe__notice" data-type="error">
              Dieser Abmeldelink ist ungültig oder wurde bereits verwendet.
            </div>
            <?php endif ?>
          <div class="dreamform-field">
            <label class="dreamform-label" for="newsletter-unsubscribe-email">E-Mail-Adresse <em>*</em></label>
            <input class="dreamform-input" id="newsletter-unsubscribe-email" name="email" type="email" autocomplete="email" required>
          </div>
        <?php endif ?>

        <?php snippet('utilities/button', ['label' => 'Vom Newsletter abmelden', 'type' => 'submit']) ?>
      </form>
    <?php endif ?>
  </section>
  </div>
</main>

<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
