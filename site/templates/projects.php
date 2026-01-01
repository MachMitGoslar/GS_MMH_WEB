<?php
/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/
?>
<?php snippet('general/head'); ?>
<?php snippet('general/header'); ?>
  <main>
  <div class="mb-4">
    <?=snippet('components/hero')?>
  </div>
  <section class="grid content mb-7">
    <h1 class="font-titleXXL grid-item" data-span="1/1"><?=$page->title()?></h1>
    <?php //Project Updates List?>
        <section class="grid-item" data-span="1/1">
            <ul class="grid mb-4">
                <?php foreach ($page->children() as $project) : ?>
                    <?php snippet('components/project/projectTeaserCard', compact('project')) ?>
                <?php endforeach ?>
                <li class="c-projectTeaserCard">
                  <div >
                    <img class="hero" src="https://picsum.photos/1600/800?random=2">
                  </div>
                  <div class="content">
                    <h3 class="font-headline">Projektarchiv</h3>
                    <p></p>
                    <p class="font-footnote mb-3">Ein Blick in die Vergangenheit. Finde alle vergangenen und abgeschlossenen Projekte</p>
                    <!-- <p class="font-body"><?=$project->text()->excerpt()?></p> -->
                    <a href="<?=$project?>" class="gs-c-btn" data-type="secondary" data-size="regular" data-style="pill">Zum Projektarchiv</a>
                  </div>
                </li>
            </ul>
            <a class="gs-c-btn" data-type="primary" data-size="large" data-style="pill" href=<?=$site->page('projekte')?> >Eigenes Projekt starten?</a>
        </section>
  </section>
  <?php snippet('general/footer'); ?>
<?php snippet('general/foot'); ?>
