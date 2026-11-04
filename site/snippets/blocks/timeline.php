<?php
$layout = $block->content()->get('layout')->or('standard')->value();
$entries = $block->content()->get('entries')->toStructure();
?>

<?php if ($layout === 'constrained') : ?>
    <!-- CONSTRAINED VERSION -->
    <div class="timeline-block timeline-block--compact c-project-timeline">

        <?php foreach ($entries as $entry) : ?>
            <?php $image = $entry->image()->toFile(); ?>

            <div class="c-project-timeline-card<?= $image ? ' c-project-timeline-card--has-image' : '' ?>">

                <div class="info">

                    <div class="headline">
                        <h3 class="title">
                            <?= $entry->year()->html() ?>
                        </h3>
                    </div>

                    <div class="text">
                        <?= $entry->summary()->kt() ?>
                    </div>

                    <?php if ($image) : ?>
                        <div class="image">
                            <?php snippet('utilities/image', [
                                'file' => $image,
                                'role' => 'card',
                                'sizes' => '(min-width: 768px) 480px, 100vw',
                                'alt' => $entry->year()->value(),
                            ]) ?>
                        </div>
                    <?php endif ?>

                </div>

            </div>
        <?php endforeach ?>

    </div>

<?php else : ?>
    <!-- STANDARD VERSION (dein bestehendes CSS) -->
    <div class="timeline-block timeline-block--standard c-project-timeline">

        <?php foreach ($entries as $entry) : ?>
            <?php $image = $entry->image()->toFile(); ?>

            <div class="c-project-timeline-card<?= $image ? ' c-project-timeline-card--has-image' : '' ?>">

                <div class="info">

                    <div class="headline">
                        <h3 class="title">
                            <?= $entry->year()->html() ?>
                        </h3>
                    </div>

                    <div class="text">
                        <?= $entry->summary()->kt() ?>
                    </div>

                    <?php if ($image) : ?>
                        <div class="image">
                            <?php snippet('utilities/image', [
                                'file' => $image,
                                'role' => 'card',
                                'sizes' => '(min-width: 768px) 480px, 100vw',
                                'alt' => $entry->year()->value(),
                            ]) ?>
                        </div>
                    <?php endif ?>

                </div>

            </div>

        <?php endforeach ?>

    </div>

<?php endif ?>
