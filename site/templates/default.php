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
  <section class="">
    <div class="grid content">
    <h1 class="font-titleXXL grid-item" data-span="1/1"><?=$page->title()?></h1>

    </div>
    <?php foreach ($page->layout()->toLayouts() as $layout) : ?>
      <div class="grid content">

        <?php foreach ($layout->columns() as $column) : ?>
            <?php foreach ($column->blocks() as $block) : ?>
        <div class="grid-item" data-span="<?=$column->width()?>">
            <div id="<?= $block->id() ?>" class="c-blog c-blog-<?= $block->type() ?>">
                <?= $block ?>
            </div>
        </div>
            <?php endforeach ?>

        <?php endforeach ?>
      </div>

    <?php endforeach ?>
  </section>
  <!-- GALLERY -->
   <?php if ($page->files()->isNotEmpty()): ?>
    <section>
        <div class="grid content">
            <h2 class="font-titleL grid-item" data-span="1/1">Galerie</h2>
            <div class="grid-item" data-span="1/1">
                <div class="c-gallery">
                    <ul class="grid">
                        <?php foreach ($page->files() as $image_file) : ?>
                          <?php $image = $image_file->toFile() ?>
                        <li class="grid-item grid-item-span4">
                        <a href="<?= $image_file->url() ?>" data-fslightbox="gallery">
                          <img src="<?= $image_file->url() ?>" alt="<?= $image_file->alt()->esc() ?>" class="c-gallery-image" />

                          <!-- <?= $image_file?> -->
                          </a>
                        </li>
                        <?php endforeach ?>
                    </ul>
                </div>
        </div>      
    </section>
  <?php endif ?>

  </main>
<?php snippet('general/footer'); ?>
<?php snippet('general/foot'); ?>
