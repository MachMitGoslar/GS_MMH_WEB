<?php

/**** Renderproject Status Header
 * var $project_step: Project Step
 */

$from = $project_step->project_status_from();
$to = $project_step->project_status_to();

?>

<div class="statusheader">
      <div>
      <?php if ($from->isNotEmpty()) : ?>
            <?= snippet("components/project/statusBadge", ["status" => $from]) ?>
      <?php endif ?>

      <?php if ($from->isNotEmpty() && $to->isNotEmpty()) : ?>
        <span class="transitionElement">
          >
        </span>
      <?php endif ?>
      <?php if ($to->isNotEmpty()) : ?>
            <?= snippet("components/project/statusBadge", ["status" => $to]) ?>
      <?php endif ?>
      </div>

      <time class="font-caption"><?=$project_step->project_start_date()->toDate("d.m.Y", $fallback = null) ?: "test"?></time>
</div>