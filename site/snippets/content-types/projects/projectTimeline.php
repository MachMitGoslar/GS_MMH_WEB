<div class="project-timeline project-step-timeline mb-7">
  <h3 class="font-headline mb-3">Projektentwicklung</h3>
  
  <?php if ($project_steps && count($project_steps) > 0) :
      ?>
  <div class="project-step-timeline-container">
      <?php foreach ($project_steps as $project_step) :
          ?>
        <div class="project-step-timeline-item">
          <div class="project-step-timeline-connector"></div>
          <div class="project-step-timeline-card">
            <div class="project-step-timeline-content">
              <div class="project-step-timeline-date"><?= $project_step->project_start_date()->toDate('d.m.Y') ?></div>
              <?php if ($project_step->project_status_from()->isNotEmpty() || $project_step->project_status_to()->isNotEmpty()) :
                  ?>
                <div class="project-step-timeline-status-pills">
                    <?= snippet('content-types/projects/stepStatusBadge', [
                        'from' => $project_step->project_status_from()->value(),
                        'to' => $project_step->project_status_to()->value(),
                    ]) ?>
                </div>
              <?php endif ?>

              <h4 class="project-step-timeline-title"><?= $project_step->title()->html() ?></h4>
              <div class="project-step-timeline-text"><?= $project_step->description() ?></div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else :
      ?>
      <p class="font-body">Noch keine Projektschritte vorhanden.</p>
  <?php endif ?>
</div>
