<?php

use Kirby\Cms\Structure;
use Kirby\Cms\StructureObject;
/**
 * Blueprint template for HTML Sitemap
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 * Delivered by the Controller
 * @var \Kirby\Cms\Pages $pages
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
                <h1 class="font-titleXXL grid-item" data-span="1/1">
                    <?= $page->title() ?>
                </h1>
                <?php if ($page->subtitle()->isNotEmpty()) : ?>
                <h2 class="font-titleL grid-item" data-span="1/1">
                    <?= $page->subtitle() ?>
                </h2>
                <?php endif ?>

                <?php if ($page->intro_text()->isNotEmpty()) : ?>
                <div class="font-body grid-item" data-span="1/1">
                    <?= $page->intro_text() ?>
                </div>
                <?php endif ?>
            </div>

            <?= snippet('sections/page-section', [
                'pages' => $page->pages()->toPages(),
            ]) ?>

        </section>
    </main>

<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
