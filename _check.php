<?php
require "/var/www/html/kirby/bootstrap.php";
$kirby = new Kirby\Cms\App(['roots'=>['index'=>'/var/www/html','base'=>'/var/www/html']]);
foreach (['projects','project-archive'] as $pid) {
  echo "== $pid\n";
  $p = $kirby->site()->find($pid);
  foreach ($p->childrenAndDrafts() as $c) {
    $cover = $c->cover();
    $steps = method_exists($c,'project_steps') ? $c->project_steps()->count() : 0;
    $layout = $c->text()->toLayouts()->count();
    printf("%-50s status=%-14s cover=%-3s layouts=%-2d steps=%d\n", $c->id(), $c->project_status(), $cover?'ok':'--', $layout, $steps);
  }
}
