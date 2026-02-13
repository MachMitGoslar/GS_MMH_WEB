<?php
use Kirby\Toolkit\Str;

/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
* @var array $event
*/
?>
<li class="eventsListItem">
  <?php
    $date = strtotime($event['start'] ?? '');
?>
  <a href="<?= "https://oveda.de/eventdate/" . ($event['id'] ?? '') ?>" target="_blank" rel="noopener noreferrer">
  <time class="font-footnote mb-2"><?=date('d.m.Y, H:i', $date)?> Uhr</time>
  <h3 class="font-subheadline mb-2"><?=Str::short($event['event']['name'], 50, '…') ?></h3>
  <p class="font-body mb-2"><?=Str::short($event['event']['description'], 120, '…')?></p>
    </a>

</li>