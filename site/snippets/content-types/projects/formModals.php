<?php

use Kirby\Cms\Pages;
use Kirby\Toolkit\A;

$formsRoot = site()->find('forms');
$forms = $formsRoot
    ? $formsRoot->children()->listed()->filterBy('intendedTemplate', 'form')
    : new Pages([]);

if ($forms->isEmpty()) {
    return;
}

$formAttr = [
    'form' => ['class' => 'dreamform project-form-modal__form'],
    'row' => [],
    'column' => [],
    'field' => ['class' => 'dreamform-field'],
    'label' => ['class' => 'dreamform-label'],
    'error' => ['class' => 'dreamform-error'],
    'input' => ['class' => 'dreamform-input'],
    'button' => ['class' => 'gs-c-btn', 'data-type' => 'primary', 'data-size' => 'regular', 'data-style' => 'pill'],
    'textarea' => [
        'field' => [],
        'label' => [],
        'error' => [],
        'input' => ['class' => 'dreamform-textarea'],
    ],
    'text' => [
        'field' => [],
        'label' => [],
        'error' => [],
        'input' => ['class' => 'dreamform-input', 'autocomplete' => 'name'],
    ],
    'select' => [
        'field' => [],
        'label' => [],
        'error' => [],
        'input' => ['class' => 'dreamform-select'],
    ],
    'number' => [
        'field' => [],
        'label' => [],
        'error' => [],
        'input' => ['class' => 'dreamform-input'],
    ],
    'file' => [
        'field' => [],
        'label' => [],
        'error' => [],
        'input' => ['class' => 'dreamform-file-upload'],
    ],
    'email' => [
        'field' => [],
        'label' => [],
        'error' => [],
        'input' => ['class' => 'dreamform-input', 'autocomplete' => 'email'],
    ],
    'radio' => [
        'field' => [],
        'label' => [],
        'error' => [],
        'input' => ['class' => 'dreamform-radio'],
        'row' => [],
    ],
    'checkbox' => [
        'field' => [],
        'label' => [],
        'error' => [],
        'input' => ['class' => ''],
        'row' => ['class' => 'dreamform-checkbox'],
    ],
    'success' => [],
    'inactive' => [],
];

?>
<div class="project-form-modals" data-form-modal-root>
  <?php foreach ($forms as $form) :
      $modalId = 'form-modal-' . preg_replace('/[^a-zA-Z0-9_-]+/', '-', $form->id());
      $titleId = $modalId . '-title';
      $mergedAttr = A::merge($formAttr, [
        'form' => [
            'class' => 'dreamform project-form-modal__form',
            'data-form-modal-form' => $form->id(),
        ],
      ]);

      snippet('shared/modal', [
        'id' => $modalId,
        'modifier' => 'project-form-modal',
        'ariaLabel' => $titleId,

        'slotTitle' => function () use ($form, $titleId) {
            ?>
              <h2 class="project-form-modal__title" id="<?= esc($titleId, 'attr') ?>"><?= $form->title()->html() ?></h2>
              <?php
        },

        'slotContent' => function () use ($form, $mergedAttr) {
            snippet('dreamform/form', [
                'form' => $form,
                'attr' => $mergedAttr,
            ]);
        },
      ]);
  endforeach ?>
</div>
