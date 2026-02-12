<?php
/**
 * Unified Projects + Archive Template
 *
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 */

?>

<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>

<main>

    <div class="mb-4">
        <?= snippet('sections/hero') ?>
    </div>

    <section class="grid content mb-7">
        <h1 class="font-titleXXL grid-item" data-span="1/1">
            <?= $page->headline()->or($page->title()) ?>
        </h1>

        <?php if ($page->subheadline()->isNotEmpty()): ?>
            <p class="grid-item font-copyL" data-span="1/1">
                <?= $page->subheadline() ?>
            </p>
        <?php endif; ?>
    </section>


    <?php if ($page->show_search()->toBool()): ?>
        <section class="content mb-5">
            <?= snippet('blocks/searchbar') ?>
        </section>
    <?php endif; ?>


    <section class="grid content mb-7">

        <?php if ($page->show_active()->toBool()): ?>
            <section class="grid-item" data-span="1/1">
                <ul class="grid mb-4">

                    <?php foreach ($activeProjects as $project): ?>
                        <?php snippet('content-types/projects/projectTeaserCard', compact('project')) ?>
                    <?php endforeach; ?>


                    <?php if ($archivePage && ! $page->show_archive()->toBool()): ?>
                        <li class="c-projectTeaserCard">

                            <div>
                                <img class="hero" src="https://picsum.photos/1600/800?random=2">
                            </div>


                            <div class="content">
                                <h3 class="font-headline"><?= $archivePage->title() ?></h3>

                                <?php if ($archivePage->subheadline()->isNotEmpty()): ?>
                                    <p class="font-footnote mb-3">
                                        <?= $archivePage->subheadline() ?>
                                    </p>
                                <?php endif; ?>

                                <a href="<?= $archivePage->url() ?>"
                                   class="gs-c-btn"
                                   data-type="secondary"
                                   data-size="regular"
                                   data-style="pill">
                                    Zum Projektarchiv
                                </a>
                            </div>

                        </li>
                    <?php endif; ?>

                </ul>
            </section>
        <?php endif; ?>


        <?php if ($page->show_archive()->toBool()): ?>
            <section class="grid-item" data-span="1/1">

                <?php if ($archiveProjects->count()): ?>
                    <ul class="grid mb-4">
                        <?php foreach ($archiveProjects as $project): ?>
                            <?php snippet('content-types/projects/projectTeaserCard', compact('project')) ?>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Keine abgeschlossenen Projekte gefunden.</p>
                <?php endif; ?>

            </section>
        <?php endif; ?>

    </section>

</main>

<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
