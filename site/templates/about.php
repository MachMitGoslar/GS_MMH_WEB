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
        <div class="grid-item" data-span="<?=$column->width()?>">

            <?php foreach ($column->blocks() as $block) : ?>
            <div id="<?= $block->id() ?>" class="c-blog c-blog-<?= $block->type() ?>">
                <?= $block ?>
            </div>
            <?php endforeach ?>
        </div>

        <?php endforeach ?>
      </div>

    <?php endforeach ?>
  </section>

  <!-- Team Galleries Section -->
  <section class="team-section">
    <div class="grid content">
      <div class="grid-item" data-span="1/1">
        
        <!-- Main Team -->
        <?= snippet('components/teamGallery', [
          'teamMembers' => $staff,
          'title' => 'Hauptamtliches Team',
          'subtitle' => 'Unsere hauptamtlichen Mitarbeiterinnen und Mitarbeiter',
        ]) ?>
        
        <!-- Volunteers -->
        <?= snippet('components/teamGallery', [
          'teamMembers' => $volunteers,
          'title' => 'Ehrenamtliches Team',
          'subtitle' => 'Engagierte Menschen, die das MachMit!Haus ehrenamtlich unterstützen',
        ]) ?>
        
        <!-- Partners -->
        <?= snippet('components/teamGallery', [
          'teamMembers' => $partners,
          'title' => 'Partner',
          'subtitle' => 'Unsere wertvollen Partner und Kooperationspartner',
        ]) ?>
        
        <!-- Issuers -->
        <?= snippet('components/teamGallery', [
          'teamMembers' => $issuers,
          'title' => 'Auftraggeber',
          'subtitle' => 'Institutionen und Organisationen, die uns beauftragen',
        ]) ?>
        
      </div>
    </div>
  </section>

  </main>
<?php snippet('general/footer'); ?>
<?php snippet('general/foot'); ?>
