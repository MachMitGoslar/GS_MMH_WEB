<div class="team_images flex mt-2">
    <?php if (isset($showTitle) && $showTitle): ?>
        <span> <small> Team: </small> </span>
    <?php endif; ?>
    <?php foreach ($team as $member): ?>
        <a href="<?= $member->url() ?>">

            <span
                class="inline-flex items-center justify-center w-16 h-16 -mr-4 text-sm font-semibold text-gray-800 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-300">
                <?php if ($member->cover()->inNotEmpty()): ?>
                    <img src="<?= $member->cover()->toFile()->crop(50, 50)->url() ?>" class="w-max rounded-full" />
                <?php else: ?>
                    <p> Test </p>
                <?php endif ?>
                <span class="sr-only"><?= $member->name() ?></span>
            </span>
        </a>

    <?php endforeach ?>

</div>