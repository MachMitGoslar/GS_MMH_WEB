
<?php
/**
* @var Kirby\Cms\Site $site
* @var Kirby\Cms\Page $page
*/
?>
<?php

$programm_id = 67;

//$json = Remote::get('https://goslar.feripro.de/api/programs/'.$programm_id.'/events/')->json();

/* Fetch Events from Caching Server */
$json = Remote::get('https://crawler.goslar.app/events.json')->json();

$events = $json;

usort($events, 'sort_by_start');
//var_dump($events[1]['start']);

function sort_by_start($a, $b)
{

    //TODO: Exclude Events that are already gone
    return strnatcmp($a['start'], $b['start']);
}


$rand = random_int(0, count($events) - 1);
$event = $events[$rand];


$json_event['title'] = $event['name'];
$json_event['description'] = "<strong>".$event['name'] . "</strong> - " . $event['description'];
$json_event['published_at'] = $event['start'];
$json_event['image_url'] = "https://jugend.goslar.de/fileadmin/_processed_/3/1/csm_Post_a87fefbd79.png";
$json_event['call_to_action_url'] = "https://goslar.feripro.de/anmeldung/".$programm_id."/veranstaltungen/".$event["event_id"];


print json_encode($json_event, JSON_UNESCAPED_SLASHES);
