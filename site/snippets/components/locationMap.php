<?php

/**
 * Reusable Mapbox location map
 *
 * Props:
 * - id
 * - class
 * - lat
 * - lng
 * - mapboxToken
 * - popupTitle
 * - popupText
 * - popupAnchor
 */

$mapId = $id ?? 'map';
$mapClass = $class ?? '';
$popupAnchor = $popupAnchor ?? 'right';
$popupTitle = $popupTitle ?? 'MachMit!Haus';
$popupText = $popupText ?? '';
$mapboxToken = is_object($mapboxToken) && method_exists($mapboxToken, 'value')
    ? $mapboxToken->value()
    : ($mapboxToken ?? '');

?>
<div id="<?= esc($mapId) ?>"<?= $mapClass !== '' ? ' class="' . esc($mapClass) . '"' : '' ?>></div>

<script>
    (() => {
        const mapElement = document.getElementById(<?= json_encode($mapId) ?>);

        if (!mapElement || typeof mapboxgl === 'undefined') {
            return;
        }

        mapboxgl.accessToken = <?= json_encode($mapboxToken) ?>;

        const locationMap = new mapboxgl.Map({
            container: <?= json_encode($mapId) ?>,
            style: 'mapbox://styles/mapbox/standard',
            projection: 'globe',
            center: [<?= json_encode((float) $lng) ?>, <?= json_encode((float) $lat) ?>],
            zoom: 15,
            attributionControl: false,
            zoomControl: false
        });

        locationMap.scrollZoom.disable();

        const popup = new mapboxgl.Popup({
            anchor: <?= json_encode($popupAnchor) ?>,
            closeButton: false
        }).setHTML(
            '<h3>' + <?= json_encode($popupTitle) ?> + '</h3><p>' + <?= json_encode($popupText) ?> + '</p>'
        );

        const markerElement = document.createElement('div');
        markerElement.className = 'custom-marker';
        markerElement.innerHTML = '<img src="/assets/svg/map_pin.svg" alt="Marker" style="width:32px;height:32px;">';

        const marker = new mapboxgl.Marker({
            element: markerElement
        })
            .setLngLat([<?= json_encode((float) $lng) ?>, <?= json_encode((float) $lat) ?>])
            .addTo(locationMap);

        marker.setPopup(popup);

        locationMap.on('style.load', () => {
            locationMap.setFog({});
        });
    })();
</script>
