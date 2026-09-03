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

        <?php if ($page->subheadline()->isNotEmpty()) : ?>
            <p class="grid-item font-body" data-span="1/1">
                <?= $page->subheadline() ?>
            </p>
        <?php endif; ?>
    </section>


    <?php if ($page->show_search()->toBool()) : ?>
        <section class="content mb-5">
            <?= snippet('blocks/searchbar') ?>
        </section>
    <?php endif; ?>


    <section class="grid content mb-7">

        <?php if ($page->show_active()->toBool()) : ?>
            <section class="grid-item projects-listing" data-span="1/1">

                <?php if (count($topicFilters) > 1) : ?>
                                            <span class="projects-filter-heading"> Nach Themen </h6>

                    <div class="projects-filter-row mb-3">
                        <a
                            class="projects-filter-pill gs-c-btn"
                            data-active="<?= $activeTopic === null ? 'true' : 'false' ?>"
                            data-type="<?= $activeTopic === null ? 'primary' : 'secondary' ?>"
                            data-size="small"
                            data-style="pill"
                            href="<?= $topicResetUrl ?>"
                        >
                            <span>Alle Themen</span>
                            <span class="projects-filter-pill__count"><?= $activeProjects->count() ?></span>
                        </a>
                        <?php foreach ($topicFilters as $slug => $filter) : ?>
                            <a
                                class="projects-filter-pill gs-c-btn"
                                data-active="<?= $filter['isActive'] ? 'true' : 'false' ?>"
                                data-topic-slug="<?= esc($slug) ?>"
                                data-type="<?= $filter['isActive'] ? 'primary' : 'secondary' ?>"
                                data-size="small"
                                data-style="pill"
                                href="<?= $filter['url'] ?>"
                            >
                                <span><?= esc($filter['label']) ?></span>
                                <span class="projects-filter-pill__count"><?= $filter['count'] ?></span>
                            </a>
                        <?php endforeach ?>
                    </div>
                <?php endif ?>

                <?php if (!empty($tagFilters)) : ?>
                                                                <span class="projects-filter-heading"> Nach Schlagworten </h6>

                    <div class="projects-filter-row mb-5" aria-label="Nach Tag filtern">

                        <?php foreach ($tagFilters as $tag => $filter) : ?>
                            <a
                                class="projects-filter-pill projects-filter-pill--tag gs-c-btn"
                                data-active="<?= $filter['isActive'] ? 'true' : 'false' ?>"
                                data-type="<?= $filter['isActive'] ? 'primary' : 'secondary' ?>"
                                data-style="pill"
                                href="<?= $filter['url'] ?>"
                            ><?= esc($filter['label']) ?></a>
                        <?php endforeach ?>
                    </div>
                <?php endif ?>

                <?php if (empty($groupedProjects) && $tagQuery !== '') : ?>
                    <p class="font-body mb-6">Keine Projekte mit diesem Tag gefunden.</p>
                <?php endif ?>

                <?php $themeRoot = $site->find('themen'); ?>
                <?php foreach ($groupedProjects as $slug => $group) : ?>
                    <?php $themePage = $themeRoot
                        ? $themeRoot->children()->listed()->findBy('topic', $slug)
                        : null; ?>
                    <h2 id="<?= esc($slug) ?>" class="font-title2 section-title">
                        <?php if ($themePage) : ?>
                            <a href="<?= $themePage->url() ?>"><?= esc($group['label']) ?></a>
                        <?php else : ?>
                            <?= esc($group['label']) ?>
                        <?php endif ?>
                    </h2>
                    <ul class="grid mb-6">
                        <?php foreach ($group['projects'] as $project) : ?>
                            <?php snippet('content-types/projects/projectTeaserCard', compact('project')) ?>
                        <?php endforeach ?>
                        <?php if ($archivePage && !$page->show_archive()->toBool()) : ?>
                        <li class="c-projectTeaserCard">

                            <div class="hero-wrapper">
                                <?php snippet('utilities/imagePlaceholder') ?>
                            </div>


                            <div class="content">
                                <h3 class="font-headline font-line-height-narrow"><?= $archivePage->title() ?></h3>

                                <?php if ($archivePage->subheadline()->isNotEmpty()) : ?>
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
                    
                <?php endforeach ?>

                
            </section>
        <?php endif; ?>


        <?php if ($page->show_archive()->toBool()) : ?>
            <section class="grid-item" data-span="1/1">

                <?php if ($archiveProjects->count()) : ?>
                    <ul class="grid mb-4">
                        <?php foreach ($archiveProjects as $project) : ?>
                            <?php snippet('content-types/projects/projectTeaserCard', compact('project')) ?>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p>Keine abgeschlossenen Projekte gefunden.</p>
                <?php endif; ?>

            </section>
        <?php endif; ?>

    </section>

</main>

<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
