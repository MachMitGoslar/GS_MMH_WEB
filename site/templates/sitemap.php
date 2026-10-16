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
                <div class="grid-item" data-span="1/1">
                    <?php snippet('utilities/page-title', [
                        'title' => $page->title(),
                        'subtitle' => $page->subtitle()->isNotEmpty() ? $page->subtitle() : null,
                    ]) ?>
                </div>

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
