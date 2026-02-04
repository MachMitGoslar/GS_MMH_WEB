<?php
/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
* @var Content_Data [$days, $data]
*/
?>
<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>

<main class="main">

    <div class="mb-4">
        <?=snippet('sections/hero')?>
    </div>

    <section class="content mb-7">
        <h1 class="font-titleXXL grid-item"> App Performance Daten </h1>
        <div class="grid-item">
        <canvas id="container" style="height:800px"></canvas>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
        const ctx = document.getElementById('container');

        new Chart(ctx, {
            type: 'bar',
            data: {
            labels: ['<?=implode("','", $days)?>'],

            datasets: [
                <?php foreach ($datasets as $url => $values) : ?>
                { label: "<?= $url ?>",
                data: [<?=implode(",", $values)?>],
                borderWidth: 1
                },
                <?php endforeach ?>
            ]
            },
            options: {
            scales: {
                y: {
                beginAtZero: true
                }
            }
            }
        });
        </script>
        </div>
    </section>
</main>
<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>


<?php
