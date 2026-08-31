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
                    <?php if ($project_step->project_status_from()->isNotEmpty()) :
                        ?>
                        <?= snippet('content-types/projects/statusBadge', ['status' => $project_step->project_status_from()]) ?>
                        <?php
                    endif ?>
                    <?php if ($project_step->project_status_from()->isNotEmpty() && $project_step->project_status_to()->isNotEmpty()) :
                        ?>
                    <svg class="project-step-timeline-status-arrow" width="16" height="12" viewBox="0 0 16 12" fill="none">
                      <path d="M10 2L14 6L10 10M14 6H2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <?php endif ?>
                    <?php if ($project_step->project_status_to()->isNotEmpty()) :
                        ?>
                        <?= snippet('content-types/projects/statusBadge', ['status' => $project_step->project_status_to()]) ?>
                        <?php
                    endif ?>
                </div>
              <?php endif ?>
              <?php if ($stepImage = $project_step->content()->get('image')->toFile()) :
                  ?>
                <figure class="project-step-timeline-image">
                  <img src="<?= $stepImage->crop(640, 360)->url() ?>" alt="<?= $stepImage->alt()->or($project_step->title())->esc() ?>" loading="lazy" style="object-position: <?= $stepImage->focus()->isNotEmpty() ? $stepImage->focus() : '50% 50%' ?>">
                </figure>
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
