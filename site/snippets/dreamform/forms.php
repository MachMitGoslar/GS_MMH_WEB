<?php

if (!isset($page) || !$page->form()->isNotEmpty()) {
    return;
}

$formPage = $page->form()->toPage();
?>

<div class="grid content">
    <div class="grid-item" data-span="1/1">

        <div class="c-blog c-blog-form">

            <h2><?= $formPage->title() ?></h2>
            <section>
            <?php snippet('dreamform/form', [
                    'form' => $formPage,
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
                                    'input' => ['class' => ''],
                                    'row' => ['class' => 'dreamform-checkbox'],
                            ],

                            'success' => [],
                            'inactive' => [],
                    ]
            ]); ?>
            </section>
        </div>
    </div>
</div>
