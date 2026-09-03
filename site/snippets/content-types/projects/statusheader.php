<?php

/**** Renderproject Status Header
 * var $project_step: Project Step
 */

?>

<div class="statusheader">
      <div>
      <?= snippet('content-types/projects/stepStatusBadge', [
          'from' => $project_step->project_status_from()->value(),
          'to' => $project_step->project_status_to()->value(),
      ]) ?>
      </div>

      <time class="font-footnote"><?=$project_step->project_start_date()->toDate('d.m.Y', $fallback = null) ?: 'test'?></time>
</div>
