<?php
/**
* Member Detail Page Template
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/
?>
<?php snippet('general/head', slots: true); ?>

<?php slot('head') ?>
<link href="https://api.mapbox.com/mapbox-gl-js/v3.17.0/mapbox-gl.css" rel="stylesheet">
<script src="https://api.mapbox.com/mapbox-gl-js/v3.17.0/mapbox-gl.js"></script>
<?php endslot() ?>

<?php endsnippet() ?>
<?php snippet('general/header'); ?>

<main>
  <section class="member-detail">
    <div class="grid content">
      
      <!-- Hero Section -->
      <div class="grid-item member-hero" data-span="1/1">
        <div class="member-hero-content">
          <div class="profile-image-large">
            <?php if ($page->cover() && $page->cover()->toFile()) : ?>
              <img src="<?= $page->cover()->crop(300, 300)->url() ?>" 
                   alt="<?= $page->name()->html() ?>" 
                   loading="eager">
            <?php else : ?>
              <div class="placeholder-avatar-large">
                <span><?= strtoupper(substr($page->name()->value(), 0, 1)) ?></span>
              </div>
            <?php endif ?>
          </div>
          
          <div class="member-info">
            <h1 class="font-titleXXL"><?= $page->name()->html() ?></h1>
            
            <?php if ($page->role()->isNotEmpty()) : ?>
              <div class="member-role"><?= $page->role()->html() ?></div>
            <?php endif ?>
            
            <?php if ($page->teams()->isNotEmpty()) : ?>
              <div class="team-badges">
                <?php foreach ($page->teams()->split() as $teamTag) : ?>
                  <span class="team-badge" data-team="<?= $teamTag ?>">
                    <?php
                    $teamLabels = [
                      'staff' => 'Hauptamtliches Team',
                      'volunteer' => 'Ehrenamtliches Team',
                      'partner' => 'Partner',
                      'issuer' => 'Auftraggeber',
                    ];
                    echo $teamLabels[$teamTag] ?? ucfirst($teamTag);
                    ?>
                  </span>
                <?php endforeach ?>
              </div>
            <?php endif ?>
            
            <!-- Contact Information -->
            <?php if ($page->hasAnyContactInfo() || $page->address()->isNotEmpty()) : ?>
              <div class="contact-section-unified">
                <div class="unified-contact-card">
                  <div class="contact-card-header">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.89 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-9 3h2v2h-2zm0 3h2v7h-2z"/>
                    </svg>
                    <h3>Kontakt & Standort</h3>
                  </div>
                  
                  <div class="contact-content-grid">
                    <!-- Contact Information (1/2) -->
                    <div class="contact-info-section">
                      <div class="contact-items">
                        <?php if ($page->email()->isNotEmpty()) : ?>
                          <a href="mailto:<?= $page->email()->value() ?>" class="contact-item-inline">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                              <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.89 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                            </svg>
                            <span><?= $page->email()->value() ?></span>
                          </a>
                        <?php endif ?>
                        
                        <?php if ($page->phone()->isNotEmpty()) : ?>
                          <a href="tel:<?= $page->phone()->value() ?>" class="contact-item-inline">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                              <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                            </svg>
                            <span><?= $page->phone()->value() ?></span>
                          </a>
                        <?php endif ?>
                        
                        <?php if ($page->address()->isNotEmpty()) : ?>
                          <div class="contact-item-inline">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                              <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                            </svg>
                            <span><?= $page->address()->value() ?></span>
                          </div>
                        <?php endif ?>
                      </div>
                    </div>
                    
                    <!-- Map Section (1/2) -->
                    <?php if ($page->address()->isNotEmpty()) : ?>
                    <div class="map-info-section">
                      <div class="map-container">
                        <div id="member-map" class="member-map"></div>
                      </div>
                    </div>
                    <?php endif ?>
                  </div>
                </div>
              </div>
            <?php endif ?>
          </div>
        </div>
      </div>
      
      <!-- Description Section -->
      <?php if ($page->description()->isNotEmpty()) : ?>
        <div class="grid-item member-description" data-span="1/1">
          <h2>Über <?= $page->name()->html() ?></h2>
          <div class="description-content">
            <?= $page->description()->kirbytext() ?>
          </div>
        </div>
      <?php endif ?>
      
      <!-- Projects Section -->
      <?php if ($memberProjects->count() > 0) : ?>
        <div class="grid-item member-projects" data-span="1/1">
          <h2>Aktuelle Projekte</h2>
          <div class="projects-grid">
            <?php foreach ($memberProjects as $project) : ?>
                <?= snippet('components/project/projectTeaserCard', ['project' => $project]) ?>
            <?php endforeach ?>
          </div>
        </div>
      <?php endif ?>
      
      <!-- Newsletter Section -->
      <?php if ($memberNewsletters->count() > 0) : ?>
        <div class="grid-item member-newsletters" data-span="1/1">
          <h2>Newsletter-Ausgaben</h2>
          <div class="newsletters-grid">
            <?php foreach ($memberNewsletters as $newsletter) : ?>
              <article class="newsletter-card">
                <div class="newsletter-meta">
                  <time datetime="<?= $newsletter->publishDate()->toDate('c') ?>">
                    <?= $newsletter->publishDate()->toDate('F Y') ?>
                  </time>
                </div>
                <h3><a href="<?= $newsletter->url() ?>"><?= $newsletter->title()->html() ?></a></h3>
                <?php if ($newsletter->greetingText()->isNotEmpty()) : ?>
                  <p class="newsletter-excerpt"><?= $newsletter->greetingText()->excerpt(150) ?></p>
                <?php endif ?>
                <a href="<?= $newsletter->url() ?>" class="newsletter-link">Newsletter lesen →</a>
              </article>
            <?php endforeach ?>
          </div>
        </div>
      <?php endif ?>
      
      <!-- Notes/Blog Articles Section -->
      <?php if ($memberNotes->count() > 0) : ?>
        <div class="grid-item member-notes" data-span="1/1">
          <h2>Blog-Artikel</h2>
          <div class="notes-grid">
            <?php foreach ($memberNotes as $note) : ?>
              <article class="note-card">
                <div class="note-meta">
                  <time datetime="<?= $note->published() ?>">
                    <?= $note->published() ?>
                  </time>
                  <?php if ($note->tags()->isNotEmpty()) : ?>
                    <div class="note-tags">
                        <?php foreach ($note->tags()->split() as $tag) : ?>
                        <span class="tag">#<?= $tag ?></span>
                        <?php endforeach ?>
                    </div>
                  <?php endif ?>
                </div>
                <h3><a href="<?= $note->url() ?>"><?= $note->title()->html() ?></a></h3>
                <?php if ($note->text()->isNotEmpty()) : ?>
                    <p class="note-excerpt"><?= $note->string_content()->body()->excerpt(150) ?></p>
                <?php endif ?>
                <a href="<?= $note->url() ?>" class="note-link">Artikel lesen →</a>
              </article>
            <?php endforeach ?>
          </div>
        </div>
      <?php endif ?>
      
      <!-- Social Media Links -->
      <?php if ($page->hasSocialMedia()) : ?>
        <div class="grid-item member-social" data-span="1/1">
          <h2>Social Media</h2>
          <div class="social-links">
            <?php if ($page->facebook()->isNotEmpty()) : ?>
              <a href="https://facebook.com/<?= $page->facebook()->value() ?>" target="_blank" rel="noopener" class="social-link facebook">
                <span>Facebook</span>
              </a>
            <?php endif ?>
            
            <?php if ($page->instagram()->isNotEmpty()) : ?>
              <a href="https://instagram.com/<?= $page->instagram()->value() ?>" target="_blank" rel="noopener" class="social-link instagram">
                <span>Instagram</span>
              </a>
            <?php endif ?>
            
            <?php if ($page->linkedin()->isNotEmpty()) : ?>
              <a href="https://linkedin.com/in/<?= $page->linkedin()->value() ?>" target="_blank" rel="noopener" class="social-link linkedin">
                <span>LinkedIn</span>
              </a>
            <?php endif ?>
            
            <?php if ($page->github()->isNotEmpty()) : ?>
              <a href="https://github.com/<?= $page->github()->value() ?>" target="_blank" rel="noopener" class="social-link github">
                <span>GitHub</span>
              </a>
            <?php endif ?>
            
            <?php if ($page->youtube()->isNotEmpty()) : ?>
              <a href="https://youtube.com/<?= $page->youtube()->value() ?>" target="_blank" rel="noopener" class="social-link youtube">
                <span>YouTube</span>
              </a>
            <?php endif ?>
          </div>
        </div>
      <?php endif ?>
      
    </div>
  </section>
</main>

<?php if ($page->address()->isNotEmpty()) : ?>
<script>
  // Initialize map when page loads
  document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if map container exists
    if (document.getElementById('member-map')) {
      mapboxgl.accessToken = 'pk.eyJ1IjoicmFuZ2FyaWFuIiwiYSI6ImNrZGVxNzNhODI5MTcyenM4dGR5bnZhb3UifQ.7WvcNEBQJn9iV42IiyG8rQ';
      
      // Geocode the address (simple fallback to MachMit!Haus coordinates)
      let coordinates = [10.429327, 51.906169]; // Default to MachMit!Haus
      let address = '<?= addslashes($page->address()->value()) ?>';
      
      const map = new mapboxgl.Map({
        container: 'member-map',
        style: 'mapbox://styles/mapbox/standard',
        projection: 'globe',
        attributionControl: false,
        zoomControl: false,
        zoom: 15,
        center: coordinates
      });
      
      map.scrollZoom.disable();
      
      const popup = new mapboxgl.Popup({
        anchor: "top",
        closeButton: false
      })
      .setHTML('<h4><?= addslashes($page->name()->value()) ?></h4><p>' + address + '</p>');
      
      const el = document.createElement('div');
            el.className = 'custom-marker';
            el.innerHTML = `
              <img src="/assets/svg/map_pin.svg" alt="Marker" style="width:32px;height:32px;">
            `;

      const marker = new mapboxgl.Marker({
        element: el
      })
      .setLngLat(coordinates)
      .addTo(map);
      
      marker.setPopup(popup);
      
      map.on('style.load', () => {
        map.setFog({});
      });
      
      // Simple geocoding fallback for Goslar addresses
      if (address.toLowerCase().includes('goslar')) {
        // You could integrate with a geocoding service here
        // For now, we'll keep the default coordinates
      }
    }
  });
</script>
<?php endif ?>

<?php snippet('general/footer'); ?>
<?php snippet('general/foot'); ?>