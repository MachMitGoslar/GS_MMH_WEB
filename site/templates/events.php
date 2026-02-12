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

        <?php //Welcome Text?>

        <div class="grid-item p-tb-4" data-span="1/2">
            <h1 class="font-titleXXL mb-3"><?=$page->headline()?></h1>
            <p class="font-body"><?=$page->description()?></p>
        </div>
        
        <?php //Events List?>
        <section class="grid-item" data-span="1/1">
            <h2 class="font-title mb-3">Termine</h2>
            <ul class="grid mb-4">
                <?php foreach ($events as $event) : ?>
                    <?php snippet('components/events/eventsListItem', compact('event')) ?>
                <?php endforeach ?>
            </ul>
            <?php // Pagination?>
            <?php if ($json['total'] > 1) : ?>
                <div class="pagination">
                    <?php if ($json['has_prev']) :?>
                        <a href="?page=<?=$json['prev_num'] ?>" class="gs-c-btn" data-type="secondary" data-size="regualr" data-style="pill">Vorherige Seite</a>
                    <?php endif ?>
                    <?php if ($json['has_next']) :?>
                        <a href="?page=<?=$json['next_num'] ?>" class="gs-c-btn" data-type="secondary" data-size="regualr" data-style="pill">Nächste Seite</a>
                    <?php endif ?>
                </div>
            <?php endif ?> 
        </section>
    </section>
</main>

<?php snippet('general/footer'); ?>
<?php snippet('general/foot'); ?>
