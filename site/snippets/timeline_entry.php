<li class="mb-4 ms-8 flex items-center">
<span
        class="-ml-1 absolute flex items-center justify-center w-8 h-8 bg-blue-100 rounded-full -start-3 ring-4 ring-gold dark:ring-gray-900 dark:bg-blue-900">
        <svg class="w-3.5 h-3.5 text-blue-800 dark:text-blue-300" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
            fill="currentColor" viewBox="0 0 20 20">
            <path
                d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
        </svg>
    </span>
    <div>
    <time
        class="block text-sm font-normal leading-none text-gray-400 dark:text-gray-500"><?= $entry->project_start_date() ?>
        <?= $entry->project_start_time()->isNotEmpty() ? " - ".$entry->project_start_time() : "" ?> </time>
    <div class="flex items-center">
    <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white"><?= $entry->title() ?>

    <?php if ($entry->num() == 1): ?>
    <span class="bg-gray-100 text-gray-800 text-sm font-medium ml-4 dark:bg-gray-900 dark:text-gray-300">Aktuell</span>
    <?php endif; ?>

    </h3>
    </div>



    <?php if ($entry->project_status_from()->isNotEmpty() && $entry->project_status_to()->isNotEmpty()): ?>
        <div class="project_change flex justify-start">

            <?= snippet('project_status', ['project_status' => $entry->project_status_from()]) ?>
            <p> -> </p>
            <?= snippet('project_status', ['project_status' => $entry->project_status_to()]) ?>
        </div>
    <?php endif ?>
    <?php if($entry->description()->isNotEmpty()): ?>
    <p class="mb-4 text-base font-normal text-gray-800 dark:text-gray-400"><?= $entry->description() ?></p>
    <?php endif ?>
    </div>
</li>