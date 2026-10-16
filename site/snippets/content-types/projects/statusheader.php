<?php

/**** Renderproject Status Header
 * var $project_step: Project Step
 */

?>

<?php snippet('utilities/meta-line', slots: true) ?>
  <?php slot('start') ?>
    <?php snippet('content-types/projects/stepStatusBadge', [
        'from' => $project_step->project_status_from()->value(),
        'to' => $project_step->project_status_to()->value(),
    ]) ?>
  <?php endslot() ?>
  <?php slot('end') ?>
    <time class="font-footnote"><?=$project_step->project_start_date()->toDate('d.m.Y', $fallback = null) ?: ''?></time>
  <?php endslot() ?>
<?php endsnippet() ?>
