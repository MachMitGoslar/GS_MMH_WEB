<div class="project-timeline project-step-timeline mb-7">
  <h3 class="font-headline mb-3">Projektentwicklung</h3>
  
  <?php if ($project_steps && count($project_steps) > 0) :
        ?>
  <div class="project-step-timeline-container">
      <?php foreach ($project_steps as $project_step) :
            ?>
        <div class="project-step-timeline-item">
          <div class="project-step-timeline-connector"></div>
            <?php snippet('content-types/projects/projectStepCard', ['project_step' => $project_step, 'project' => $project_step->parent(), 'context' => 'timeline', 'tag' => 'div']) ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else :
        ?>
      <p class="font-body">Noch keine Projektschritte vorhanden.</p>
  <?php endif ?>
</div>
