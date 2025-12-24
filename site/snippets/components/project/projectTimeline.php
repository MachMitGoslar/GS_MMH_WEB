<div class="project-timeline mb-7">
  <h3 class="font-headline mb-3">Projektentwicklung</h3>
  
  <?php if ($project_steps && count($project_steps) > 0): ?>
  <div class="timeline-container timeline-container--constrained">
      <?php foreach ($project_steps as $project_step): ?>
        <div class="timeline-item">
          <div class="timeline-connector"></div>
          <div class="timeline-item__container">
            <div class="timeline-content">
              <div class="timeline-date"><?= $project_step->project_start_date("d.m.Y") ?></div>
              <?php if ($project_step->project_status_from()->isNotEmpty() || $project_step->project_status_to()->isNotEmpty()): ?>
                <div class="timeline-status-pills">
                  <?php if ($project_step->project_status_from()->isNotEmpty()): ?>
                    <?= snippet("components/project/statusBadge", ["status" => $project_step->project_status_from()]) ?>
                  <?php endif ?>
                  <?php if ($project_step->project_status_from()->isNotEmpty() && $project_step->project_status_to()->isNotEmpty()): ?>
                    <svg class="timeline-status-arrow" width="16" height="12" viewBox="0 0 16 12" fill="none">
                      <path d="M10 2L14 6L10 10M14 6H2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  <?php endif ?>
                  <?php if ($project_step->project_status_to()->isNotEmpty()): ?>
                    <?= snippet("components/project/statusBadge", ["status" => $project_step->project_status_to()]) ?>
                  <?php endif ?>
                </div>
              <?php endif ?>
              <h4 class="timeline-title"><?= $project_step->title()->html() ?></h4>
              <div class="timeline-text"><?= $project_step->description()->html() ?></div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <p class="font-body">Noch keine Projektschritte vorhanden.</p>
    <?php endif ?>
</div>