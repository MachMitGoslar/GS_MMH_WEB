
<?php
/**
* @var Kirby\Cms\Site $site
* @var Kirby\Cms\Page $page
*/
?>
<?php 

$programm_id = 67;

$json = Remote::get('https://goslar.feripro.de/api/programs/'.$programm_id.'/events/')->json();
$events = $json;

usort($events, 'sort_by_start');
//var_dump($events[1]['start']);

function sort_by_start($a, $b) {
 
    //TODO: Exclude Events that are already gone
    return strnatcmp($a['start'], $b['start']);
}


	 $rand = random_int(0, count($events)-1);
	 $event = $events[$rand];

    $json_event['title'] = $event['name']; 
    $json_event['description'] = $event['duration_summary'] . " " . $event['description'];
    $json_event['published_at']  = $event['first_registration_date'];
    $json_event['image_url'] = $event['cover_photo'] ? $event['cover_photo']['thumbnail'] : "https://jugend.goslar.de/fileadmin/_processed_/6/e/csm_Post_e4848fd3c5.png";
    $json_event['call_to_action_url'] = "https://goslar.feripro.de/anmeldung/".$programm_id."/veranstaltungen/".$event["event_id"];

print json_encode($json_event, JSON_UNESCAPED_SLASHES);

?>


