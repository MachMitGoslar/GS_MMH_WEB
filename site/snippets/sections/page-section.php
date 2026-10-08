<?php
/**
 * Page section snippet
 * @var \Kirby\Cms\Pages $pages
 */
?>


<div class="grid content">
    <?php foreach ($pages as $page) : ?>
        <?= snippet('utilities/page-card', [
            'page' => $page,
            'class' => 'grid-item',
            'dataSpan' => '1/1',
            'withChildren' => true
        ]) ?>
    <?php endforeach ?>
</div>