<?php

use Kirby\Cms\Pages;

/**
 * Error page template
 * @var Pages $navigation
 */

?>



<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>

    <main>
        <div class="mb-4">
            <?= snippet('sections/hero') ?>
        </div>

        <section>
            <div class="grid content">
                <div class="grid-item" data-span="1/1">
                    <?php snippet('utilities/page-title', [
                        'title' => 'Da ist etwas schiefgelaufen.',
                    ]) ?>
                </div>

                <p class="font-body grid-item" data-span="1/1">
                    Leider können wir die, von dir gesuchte Seite nicht finden.
                    Vielleicht findest du die gesuchte Seite hier:
                </p>
            </div>
            <?= snippet('sections/page-section', [
                'pages' => $navigation,
            ]) ?>

        </section>
    </main>

<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
