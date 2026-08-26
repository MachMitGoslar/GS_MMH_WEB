/**
 * Mapbox map with a single marker on the event detail view.
 *
 * All parameters come from data attributes on the container, so the template
 * stays free of inline scripts.
 */
(() => {
  const container = document.querySelector('[data-event-map]');

  if (!container || typeof mapboxgl === 'undefined') return;

  const lat = Number.parseFloat(container.dataset.lat);
  const lng = Number.parseFloat(container.dataset.lng);
  const token = container.dataset.token;

  if (!token || Number.isNaN(lat) || Number.isNaN(lng)) return;

  mapboxgl.accessToken = token;

  const map = new mapboxgl.Map({
    container,
    style: 'mapbox://styles/mapbox/standard',
    center: [lng, lat],
    zoom: 15,
    attributionControl: false,
    cooperativeGestures: true,
  });

  // cooperativeGestures already keeps the map from hijacking page scroll:
  // ctrl + wheel to zoom on desktop, two fingers to pan on touch
  map.addControl(
    new mapboxgl.NavigationControl({ showCompass: false }),
    'top-right'
  );

  const marker = new mapboxgl.Marker({
    color:
      window
        .getComputedStyle(document.documentElement)
        .getPropertyValue('--color-fg-brand-primary')
        .trim() || '#866811',
  }).setLngLat([lng, lat]);

  if (container.dataset.label) {
    marker.setPopup(
      new mapboxgl.Popup({ offset: 24 }).setText(container.dataset.label)
    );
  }

  marker.addTo(map);

  // The map is created inside a grid cell that may still be resizing
  const observer = new window.ResizeObserver(() => map.resize());
  observer.observe(container);
})();
