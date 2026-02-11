<?php
/**
 * Contact page
 *
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 */
?>
<?php snippet('layout/head', slots: true); ?>

<?php slot('head') ?>
<link href="https://api.mapbox.com/mapbox-gl-js/v3.17.0/mapbox-gl.css" rel="stylesheet">
<script src="https://api.mapbox.com/mapbox-gl-js/v3.17.0/mapbox-gl.js"></script>
<?php endslot() ?>

<?php endsnippet() ?>
<?php snippet('layout/header'); ?>

<main>
    <div class="mb-4">
        <?= snippet('sections/hero') ?>
    </div>

    <!-- Title & Intro -->
    <section class="content mb-7">
        <h1 class="font-titleXXL mb-4">
            <?= $page->title() ?>
        </h1>

        <?php if ($page->intro()->isNotEmpty()): ?>
            <div class="mb-6">
                <?= $page->intro()->kt() ?>
            </div>
        <?php endif ?>
    </section>

    <!-- Contact + Map -->
    <section class="grid content mb-7">
        <div class="grid-item" data-span="1/3">
            <div class="contact-info mb-4">
                <h3 class="font-headline mb-3">Ihr Kontakt zu uns</h3>

                <div class="contact-item font-body mb-2">
                    📧 <a href="mailto:machmit@goslar.de">machmit@goslar.de</a>
                </div>
                <div class="contact-item font-body mb-2">
                    🌐 <a href="https://machmit.goslar.de">machmit.goslar.de</a>
                </div>
                <div class="contact-item font-body mb-2">
                    📞 <a href="tel:05321704525">05321 704 525</a>
                </div>
            </div>

            <p class="social-info">
                Folgen Sie uns: <strong>@machmitgoslar</strong>
            </p>
        </div>

        <div class="grid-item" data-span="1/2">
            <div id="map" class="mb-2"></div>
            <p class="font-footnote">Markt 7, 38640 Goslar</p>
        </div>
    </section>

    <!-- 🔽 Flexible Blocks (wie Project) -->
    <div class="designer">
        <?php foreach ($page->text()->toLayouts() as $layout) : ?>
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
    </div>
</main>

<script>
    mapboxgl.accessToken = '<?= esc($page->mapbox_token()) ?>';

    const map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/standard',
        center: [<?= $page->lng() ?>, <?= $page->lat() ?>],
        zoom: 15,
        attributionControl: false
    });

    map.scrollZoom.disable();

    new mapboxgl.Marker()
        .setLngLat([<?= $page->lng() ?>, <?= $page->lat() ?>])
        .addTo(map);
</script>

<?php snippet('layout/footer'); ?>
