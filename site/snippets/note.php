<article class="note-excerpt grid grid-cols-4 items-center space-x-4 rtl:space-x-reverse mb-5">
<div class="relative">
  <a href="<?= $note->url() ?>" class=" ">
    <figure class="img rounded-full">
      <?php if ($cover = $note->cover()): ?>
        <img class="" src="<?= $cover->resize(220, 220)->url() ?>" alt="<?= $cover->alt()->esc() ?>" />
      <?php endif ?>
    </figure>

  </a>
  <div class="team_images h-10 absolute bottom-0 right-10 z-10">
      <?php
      if ($note->author()->inNotEmpty()) {
        $members = $note->author()->toPages();
        snippet('team_images', ['team' => $members]);
      }
      ?>
  </div>
  </div>

    <div class="exerpt col-span-3">
      <div class="by_date float-end">
        <span class="  font-thin text-gray-600 text-sm"> Veröffentlicht: </span>
        <time class="note-excerpt-date  text-gray-600 text-sm"
          datetime="<?= $note->published('c') ?>"><?= $note->published() ?></time>
      </div>

      <h2 class="font-bold text-lg "><?= $note->title()->esc() ?></h2>

      <?php if (($excerpt ?? true) !== false): ?>
        <div class="note-excerpt-text">
          <?= $note->text()->toBlocks()->excerpt(280) ?>
        </div>
      <?php endif ?>



    </div>
</article>