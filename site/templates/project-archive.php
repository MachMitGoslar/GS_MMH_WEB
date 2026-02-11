<?php
/**
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 */

$query = get('q');
$archiveProjects = getArchivedProjects($site);

if ($query) {
    $archiveProjects = $archiveProjects->filter(function ($project) use ($query) {
        return stristr($project->title()->value(), $query) !== false
                || stristr($project->text()->value(), $query) !== false;
    });
}
?>
<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>
<main>
    <div class="mb-4">
        <?= snippet('sections/hero') ?>
    </div>
    <section class="grid content mb-7">
        <h1 class="font-titleXXL grid-item" data-span="1/1"><?= $page->title() ?></h1>
    </section>
        <section class="content"><?= snippet('blocks/searchbar') ?></section>
    <section class="grid content mb-7">
    <?php if ($archiveProjects && $archiveProjects->count()): ?>
            <section class="grid-item" data-span="1/1">
                <ul class="grid mb-4">
                    <?php foreach ($archiveProjects as $project): ?>
                        <?php snippet('content-types/projects/projectTeaserCard', compact('project')) ?>
                    <?php endforeach ?>
                </ul>
            </section>
        <?php else: ?>
            <p>Keine abgeschlossenen Projekte gefunden.</p>
        <?php endif ?>

    </section>
    <?php snippet('layout/footer'); ?>
    <?php snippet('layout/foot'); ?>
