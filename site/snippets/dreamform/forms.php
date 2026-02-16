<?php
/**
 * Reusable DreamForm renderer
 *
 * Usage:
 * snippet('components/form', ['page' => $page]);
 */

if (!isset($page) || !$page->form()->isNotEmpty()) {
    return;
}
?>

<div class="px-16">
    <?php snippet('dreamform/form', [
            'form' => $page->form()->toPage(),
            'attr' => [
                    'form' => ['class' => 'dreamform'],
                    'row' => [],
                    'column' => [],
                    'field' => ['class' => 'dreamform-field'],
                    'label' => ['class' => 'dreamform-label'],
                    'error' => ['class' => 'dreamform-error'],
                    'input' => ['class' => 'dreamform-input'],
                    'button' => ['class' => 'dreamform-button dreamform-submit'],

                    'textarea' => [
                            'input' => ['class' => 'dreamform-textarea'],
                    ],
                    'text' => [
                            'input' => ['class' => 'dreamform-input', 'autocomplete' => 'name'],
                    ],
                    'select' => [
                            'input' => ['class' => 'dreamform-select'],
                    ],
                    'number' => [
                            'input' => ['class' => 'dreamform-input'],
                    ],
                    'file' => [
                            'input' => ['class' => 'dreamform-file-upload'],
                    ],
                    'email' => [
                            'input' => ['class' => 'dreamform-input', 'autocomplete' => 'email'],
                    ],
                    'radio' => [
                            'input' => ['class' => 'dreamform-radio'],
                    ],
                    'checkbox' => [
                            'row' => ['class' => 'dreamform-checkbox'],
                    ],

                    'success' => [],
                    'inactive' => [],
            ]
    ]); ?>
</div>
