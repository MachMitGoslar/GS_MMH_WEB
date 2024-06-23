<div class="px-4 py-16 mx-auto sm:max-w-xl md:max-w-full lg:max-w-screen-xl md:px-24 lg:px-8 lg:py-20">
    <div class="grid grid-cols-2 gap-8 md:grid-cols-2">
        <?php if ($project_status = $slots->project_status()): ?>
            <div class="text-center md:border-r">
                <h6 class="text-4xl font-bold lg:text-5xl xl:text-6xl"><?= $project_status ?></h6>
                <p class="text-sm font-medium tracking-widest text-gray-800 uppercase lg:text-base">
                    Projektstatus
                </p>
            </div>
        <?php endif; ?>
        <?php if ($team = $slots->team()): ?>
        <div class="text-center">
            <h6 class="text-4xl font-bold lg:text-5xl xl:text-6xl"><?= $team ?></h6>
            <p class="text-sm font-medium tracking-widest text-gray-800 uppercase lg:text-base">
                Beteiligte
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>