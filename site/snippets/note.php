<article class="rounded overflow-hidden shadow-lg flex flex-col">
  <a href="<?= $note->url() ?>" class=" "></a>
  <div class="relative">
    <a href="<?= $note->url() ?>" class=" ">
      <?php if ($cover = $note->cover()): ?>
        <img class="w-full" src="<?= $cover->crop(420, 280)->url() ?>" alt="<?= $cover->alt()->esc() ?>" />
      <?php endif ?>
      <div
        class="hover:bg-transparent transition duration-300 absolute bottom-0 top-0 right-0 left-0 bg-gray-900 dark:bg-gray-200 opacity-25">
      </div>
    </a>
  </div>

  <div class="px-6 py-4 mb-auto dark:bg-gray-300 dark:text-gray-900">
    <a href="<?= $note->url() ?>"
      class="font-medium text-lg text-gray-900 inline-block hover:text-gold-600 transition duration-500 ease-in-out inline-block mb-2">
      <?= $note->title()->esc() ?></a>
    <?php if (isset($excerpt) && $excerpt): ?>
      <p class="text-gray-500 text-sm">
        <?= $note->text()->toBlocks()->excerpt(280) ?>
      </p>
    <?php endif ?>
  </div>
  <div class="px-6 py-3 flex flex-row items-center justify-between bg-gray-100 dark:bg-gray-500">
    <span href="#" class="py-1 text-xs font-regular text-gray-900 dark:text-gray-100 mr-1 flex flex-row items-center">
      <svg class="dark:text-gray-100" height="13px" width="13px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"
        xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512"
         xml:space="preserve">
        <g>
          <g>
            <path
              d="M256,0C114.837,0,0,114.837,0,256s114.837,256,256,256s256-114.837,256-256S397.163,0,256,0z M277.333,256 c0,11.797-9.536,21.333-21.333,21.333h-85.333c-11.797,0-21.333-9.536-21.333-21.333s9.536-21.333,21.333-21.333h64v-128 c0-11.797,9.536-21.333,21.333-21.333s21.333,9.536,21.333,21.333V256z">
            </path>
          </g>
        </g>
      </svg>
      <span class="ml-1"><?= $note->published() ?></span>
    </span>

    <span href="#" class="py-1 text-xs font-regular text-gray-900 dark:text-gray-100 mr-1 flex flex-row items-center">
      <svg class="h-5 dark:text-gray-100" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <g>
          <path
            d="M9.33 11.5h2.17A4.5 4.5 0 0 1 16 16H8.999L9 17h8v-1a5.578 5.578 0 0 0-.886-3H19a5 5 0 0 1 4.516 2.851C21.151 18.972 17.322 21 13 21c-2.761 0-5.1-.59-7-1.625L6 10.071A6.967 6.967 0 0 1 9.33 11.5zM4 9a1 1 0 0 1 .993.883L5 10V19a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-9a1 1 0 0 1 1-1h2zm9.646-5.425L14 3.93l.354-.354a2.5 2.5 0 1 1 3.535 3.536L14 11l-3.89-3.89a2.5 2.5 0 1 1 3.536-3.535z" />
        </g>
      </svg>
      <span class="ml-1"><?= snippet('team_images', ['team' => $note->author()->toPages()]) ?></span>
    </span>
  </div>
</article>

