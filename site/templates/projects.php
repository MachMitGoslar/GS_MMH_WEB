<?php
/**
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
        <h1 class="font-titleXXL grid-item" data-span="1/1"><?= $page->title() ?></h1>
        <?php
        // All projects which are not done
        $activeProjects = $page->children()
                ->filter(function ($project) {
                    return $project->project_status()->value() !== 'abgeschlossen';
                });
?>
        <section class="grid-item" data-span="1/1">
            <ul class="grid mb-4">
                <?php foreach ($activeProjects as $project) : ?>
                    <?php snippet('content-types/projects/projectTeaserCard', compact('project')) ?>
                <?php endforeach ?>
                <li class="c-projectTeaserCard">
                    <div>
                        <img class="hero" src="https://picsum.photos/1600/800?random=2">
                    </div>
                    <div class="content">
                        <h3 class="font-headline">Projektarchiv</h3>
                        <p class="font-footnote mb-3">Ein Blick in die Vergangenheit. Finde alle vergangenen und abgeschlossenen Projekte</p>
                        <a href="<?= $site->page('project-archive')->url() ?>" class="gs-c-btn" data-type="secondary" data-size="regular" data-style="pill">Zum Projektarchiv</a>
                    </div>
                </li>
            </ul>
            <a class="gs-c-btn" data-type="primary" data-size="large" data-style="pill" href="<?= $site->page('projects')->url() ?>">Eigenes Projekt starten?</a>
        </section>
    </section>
    <?php snippet('layout/footer'); ?>
    <?php snippet('layout/foot'); ?>
