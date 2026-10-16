<?php
use Kirby\Toolkit\Str;

/**
* @var Kirby\Cms\Site $site
* @var Kirby\Cms\Page $page
* @var tobimori\DreamForm\Models\FormPage $modalForm
*
* @var bool|null $showTeaser
*/
$showTeaser = $showTeaser ?? true;

$modalHeadline = $site->newsletterModalHeadline()->or('Newsletter abonnieren');
$modalText = $site->newsletterModalText()->or('Erhalte Neuigkeiten aus dem MachMit!Haus direkt per E-Mail.');
$modalForm = $site->registrationForm();
?>
<?php if ($showTeaser === true) : ?>
<div class="c-newsletter-teaser grid-item" data-span="1/2">
  <div class="mb-5">
    <h2 class="font-title2 color-fg-light mb-3"><?= $site->newsletterTeaserHeadline() ?></h2>
    <p class="font-subheadline color-fg-light mb-3"><?= $site->newsletterTeaserSubheadline() ?></p>
    <p class="font-body color-fg-light"><?= $site->newsletterTeaserText() ?></p>
  </div>
  <div>
    <?php snippet('utilities/button', [
        'label' => $site->newsletterTeaserButtonText(),
        'class' => 'newsletter-subscribe-open',
        'attrs' => ['aria-haspopup' => 'dialog', 'aria-controls' => 'newsletter-subscribe-modal', 'data-modal-open' => 'newsletter-subscribe-modal'],
    ]) ?>
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

    'slotContent' => function () use ($modalForm) {
        ?>
        <?=snippet('content-elements/form', ['form' => $modalForm->toPage(), 'className' => 'newsletter-subscribe-form']); ?>
        <?php
    },
]) ?>
<script>
  (() => {
    const dialog = document.getElementById('newsletter-subscribe-modal');
    if (!dialog) return;

    const form = dialog.querySelector('.newsletter-subscribe-form');

    const success = dialog.querySelector('div#<?= Str::replace($modalForm->id(), '- page://', '') ?>');
    const errors = form?.querySelectorAll('.dreamform-error');
    
    const submit = form?.querySelector('button[type="submit"]');

    if(form && submit) {
      submit.addEventListener('click', (event) => {
        event.preventDefault();
        form.requestSubmit();
      });
    }
    if (errors) {
      for(const error of errors.entries()) {
       if (error[1].innerHTML) {
         dialog.showModal();
         error[1].focus();
         break;
       }
      }
    }

    if (!form && success) {
      dialog.showModal();
      success.focus();
    }

  })();
</script>
