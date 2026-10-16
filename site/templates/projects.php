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
        <div class="grid-item" data-span="1/1">
            <?php snippet('utilities/page-title', [
                'title' => $page->headline()->or($page->title()),
                'subtitle' => $page->subheadline()->isNotEmpty() ? $page->subheadline() : null,
            ]) ?>
        </div>
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
                    <span class="projects-filter-heading"> Nach Themen </span>

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
                    <span class="projects-filter-heading"> Nach Schlagworten </span>

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
                    <div class="mb-6">
                        <?php snippet('utilities/card-list', ['pages' => $group['projects']]) ?>
                    </div>
                    
                <?php endforeach ?>

                
            </section>
        <?php endif; ?>

        <?php if ($archivePage && !$page->show_archive()->toBool()) : ?>
                        <section class="grid-item project-archive" data-span="1/1">
                    <h2 id="archive" class="font-title2 section-title">Projektarchiv</h2>

                    <ul class="grid mb-4">
                        <?php snippet('utilities/card', [
                            'variant' => 'project',
                            'title' => $archivePage->title(),
                            'subtitle' => $archivePage->subheadline()->isNotEmpty() ? $archivePage->subheadline() : null,
                            'action' => ['label' => 'Zum Projektarchiv', 'href' => $archivePage->url()],
                        ]) ?>
                    </ul>
                        </section>
        <?php endif; ?>

        <?php if ($page->show_archive()->toBool()) : ?>
            <section class="grid-item" data-span="1/1">

                <?php if ($archiveProjects->count()) : ?>
                    <div class="mb-4">
                        <?php snippet('utilities/card-list', ['pages' => $archiveProjects]) ?>
                    </div>
                <?php else : ?>
                    <p>Keine abgeschlossenen Projekte gefunden.</p>
                <?php endif; ?>

            </section>
        <?php endif; ?>

    </section>

</main>

<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
