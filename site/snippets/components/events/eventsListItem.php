<?php
/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/
?>
<li class="eventsListItem">
  <?php
  $date = strtotime($event['end']??$event['start']??'');
  ?>
  <time class="font-footnote mb-2"><?=date('d.m.Y, H:m', $date)?> Uhr</time>
  <h3 class="font-subheadline mb-2"><?=$event['event']['name']?></h3>
  <p class="font-body mb-2"><?=Str::short($event['event']['description'], 120, '…')?></p>
</li>