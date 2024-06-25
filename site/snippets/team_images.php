<div class="flex justify-center mr-2 pb-1">
    <?php foreach ($team as $member): ?>
        <a href="<?= $member->url() ?>" class="border-4 border-gold dark:border-gold-800 rounded-full h-12 w-12 -mr-2">
            <?php if ($member->cover()->inNotEmpty()): ?>
                <img src="<?= $member->cover()->crop(50, 50)->url() ?>" class="w-max rounded-full" alt="<?= $member->cover()->alt()->esc() ?>"/>
            <?php else: ?>
                <p> Test </p>
            <?php endif ?>
            <span class="sr-only"><?= $member->name() ?></span>
            </span>
        </a>

    <?php endforeach ?>

</div>