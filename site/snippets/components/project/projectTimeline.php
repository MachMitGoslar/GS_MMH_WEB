<h3 class="font-headline">Projektentwicklung</h3>
<div class="c-project-timeline">
  <div class="outer">
    <?php foreach($project_steps as $project_step): ?>
        <?php snippet("components/project/projectTimelineEntry", compact('project_step')) ?>
    <?php endforeach ?>
  </div>
</div>