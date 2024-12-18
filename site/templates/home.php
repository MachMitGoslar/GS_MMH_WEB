<?php
/**
 * @var Kirby\Cms\Site $site
 * @var Kirby\Cms\Page $page
 */
?>

<?php snippet('general/head'); ?>
<?php snippet('general/header'); ?>

<main class="main">

    <div class="mb-4">
        <?=snippet('components/hero')?>
    </div>

    <section class="grid content mb-7">
        <?php //Welcome Text ?>
        <div class="grid-item-half-span p-tb-4">
            <h1 class="font-titleXXL mb-3"><?=$page->wellcomeHeadline()?></h1>
            <p class="font-body"><?=$page->wellcomeText()?></p>
        </div>
        <?php //Newsletter Teaser Box ?>
        <?=snippet('components/newsletter/newsletterTeaser')?>
        <?php //Divider ?>
        <div class="divider grid-item-full-span"></div>
        <?php //Events List ?>
        <section class="grid-item-full-span">
            <h2 class="font-title mb-3">Termine</h2>
            <ul class="grid mb-4">
                <?php foreach($events as $event): ?>
                    <?php snippet('components/events/eventsListItem', compact('event')) ?>
                <?php endforeach ?>
            </ul>
            <a class="gs-c-btn" data-type="secondary" data-size="regualr" data-style="pill" href="<?=$site->page('Terminkalender')?>" >Zu den Terminen</a>
        </section>
        <?php //Divider ?>
        <div class="divider grid-item-full-span"></div>
        <?php //Project Updates List ?>
        <section class="grid-item-full-span">
            <h2 class="font-title mb-3">Projektupdates</h2>
            <ul class="grid mb-4">
                <?php foreach(range(0,5) as $id): ?>
                    <?php snippet('components/project/projectUpdateTeaserCard', compact('id')) ?>
                <?php endforeach ?>
            </ul>
            <a class="gs-c-btn" data-type="secondary" data-size="regualr" data-style="pill" href=<?=$site->page('projekte')?> >Zu den Projekten</a>
        </section>

    </section>

    <?php if ($blocks = $page->blocks()?->toBlocks()): ?>
        <section class="grid content mb-7">
            <?php foreach ($blocks as $block): ?>
                <div class="grid-item-full-span block"><?php snippet('blocks/' . $block->type(), compact('block')) ?></div>
            <?php endforeach ?>
        </section>
    <?php endif; ?>
</main>

<?php snippet('general/footer'); ?>
<?php snippet('general/foot'); ?>
