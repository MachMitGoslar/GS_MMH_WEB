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
    <?php foreach ($forms as $form): ?>
        <?php $modalId = 'form-modal-' . preg_replace('/[^a-zA-Z0-9_-]+/', '-', $form->id()) ?>
        <dialog class="gs-c-modal project-form-modal" id="<?= esc($modalId, 'attr') ?>" aria-labelledby="<?= esc($modalId, 'attr') ?>-title">
            <button class="gs-c-modal__close" type="button" data-form-modal-close aria-label="Formular schließen">✕</button>
            <div class="gs-c-modal__body">
                <h2 class="project-form-modal__title" id="<?= esc($modalId, 'attr') ?>-title"><?= $form->title()->html() ?></h2>
                <?php snippet('dreamform/form', [
                    'form' => $form,
                    'attr' => A::merge($formAttr, [
                        'form' => [
                            'class' => 'dreamform project-form-modal__form',
                            'data-form-modal-form' => $form->id(),
                        ],
                    ]),
                ]) ?>
            </div>
        </dialog>
    <?php endforeach ?>
</div>
