<?php
/**
 * @var Kirby\Cms\Site $site
 * @var Kirby\Cms\Page $page
 */
?>

<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>


<main class="main">

    <div class="mb-4">
        <?=snippet('sections/hero')?>
    </div>

    <section class="grid content mb-7">

        <?php //Welcome Text?>

        <div class="grid-item p-tb-4" data-span="1/2">
            <?php snippet('utilities/page-title', [
                'title' => $page->wellcomeHeadline(),
                'subtitle' => $page->wellcomeText()->isNotEmpty() ? $page->wellcomeText() : null,
            ]) ?>
        </div>
        <?php //Newsletter Teaser Box?>
        <?=snippet('content-types/newsletter/newsletterTeaser')?>
        <?php //Divider?>
        <div class="divider grid-item" data-span="1/1"></div>
        <?php //Events List?>
        <section class="grid-item" data-span="1/1">
            <h2 class="font-title mb-3">Termine</h2>
            <ul class="grid mb-4">
                <?php foreach ($events as $event) : ?>
                    <?php snippet('content-types/events/eventsListItem', compact('event')) ?>
                <?php endforeach ?>
            </ul>
            <?php snippet('utilities/button', ['label' => 'Zu den Terminen', 'href' => $site->page('events')->url(), 'variant' => 'secondary']) ?>
        </section>
        <?php //Divider?>
        <div class="divider grid-item" data-span="1/1"></div>
        <?php //Project Updates List?>
        <section class="grid-item" data-span="1/1">
            <h2 class="font-title mb-3">Projektupdates</h2>

            <ul class="grid mb-4">
                <?php foreach ($kirby->collection('project-updates')->paginate(6) as $project_step) : ?>
                    <?php $project = $project_step->parent() ?>
                    <?php snippet('content-types/projects/projectStepCard', compact(['project', 'project_step'])) ?>
                <?php endforeach ?>
            </ul>
            <?php snippet('utilities/button', ['label' => 'Zu den Projekten', 'href' => $site->page('projects')->url(), 'variant' => 'secondary']) ?>
        </section>

    </section>

    <?php if ($blocks = $page->blocks()?->toBlocks()) : ?>
        <section class="grid content mb-7">
            <?php foreach ($blocks as $block) : ?>
                <div class="grid-item" data-span="full"><?php snippet('blocks/' . $block->type(), compact('block')) ?></div>
            <?php endforeach ?>
        </section>
    <?php endif; ?>

    <?php snippet('shared/pageCtaModal') ?>
</main>

<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
