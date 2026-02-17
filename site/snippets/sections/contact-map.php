<?php
/**
 * Reusable Contact + Map section
 *
 * Props:
 * - title
 * - email
 * - website
 * - phone
 * - social
 * - addressLabel
 * - lat
 * - lng
 * - mapboxToken
 */

$email = $email?->value();
$website = $website?->value();
$phone = $phone?->value();
$mapboxToken = $mapboxToken?->value();


?>

<section class="grid content mb-7">
    <!-- Contact Text -->
    <div class="grid-item" data-span="1/3">
        <div class="contact-info mb-4">
            <?php if ($title): ?>
                <h3 class="font-headline mb-3"><?= $title ?></h3>
            <?php endif ?>

            <?php if ($email): ?>
                <div class="contact-item font-body mb-2">
                    📧 <a href="mailto:<?= $email ?>"><?= $email ?></a>
                </div>
            <?php endif ?>

            <?php if ($website): ?>
                <div class="contact-item font-body mb-2">
                    🌐 <a href="<?= $website ?>"><?= preg_replace('#^https?://#', '', $website) ?></a>
                </div>
            <?php endif ?>

            <?php if ($phone): ?>
                <div class="contact-item font-body mb-2">
                    📞 <a href="tel:<?= $phone ?>"><?= $phone ?></a>
                </div>
            <?php endif ?>
            <?php if ($social): ?>
                <div class="contact-item font-body mb-2"">
                                                      Folgen Sie uns: <strong><?= $social ?></strong>
                </div>
            <?php endif ?>
        </div>


    </div>

    <!-- Map -->
    <div class="grid-item" data-span="2/3">
        <div id="map" class="mb-2"></div>

        <?php if ($addressLabel): ?>
            <p class="font-footnote"><?= $addressLabel ?></p>
        <?php endif ?>

    </div>
</section>

<script>
    mapboxgl.accessToken = '<?= esc($mapboxToken) ?>';

    const map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/standard',
        center: [<?= $lng ?>, <?= $lat ?>],
        zoom: 15,
        attributionControl: false
    });

    map.scrollZoom.disable();

    new mapboxgl.Marker()
        .setLngLat([<?= $lng ?>, <?= $lat ?>])
        .addTo(map);
</script>
