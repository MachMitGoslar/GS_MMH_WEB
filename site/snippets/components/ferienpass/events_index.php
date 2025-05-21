
<?php
/**
* @var Kirby\Cms\Site $site
* @var Kirby\Cms\Page $page
*/
?>
<?php 
$json = Remote::get('https://goslar.feripro.de/api/programs/67/events/')->json();
$events = $json;

usort($events, 'sort_by_start');
//var_dump($events[1]['start']);

function sort_by_start($a, $b) {
 
    //TODO: Exclude Events that are already gone
    return strnatcmp($a['start'], $b['start']);
}

$new_events = [];
foreach($events as $event) {
    $id = 0;
    $json_event = [
        
    ];
    $json_event['title'] = $event['name']; 
    $json_event['description'] = $event['duration_summary'] . " " . $event['description'];
    $json_event['published_at']  = $event['first_registration_date'];
    $json_event['image_url'] = $event['cover_photo'] ? $event['cover_photo']['thumbnail'] : "https://jugend.goslar.de/fileadmin/_processed_/6/e/csm_Post_e4848fd3c5.png";
    $json_event['call_to_action_url'] = "https://goslar.feripro.de/programm/".$id."/anmeldung/veranstaltungen/".$event["relative_id"];
    array_push($new_events, $json_event);
}   
    //var_dump(json_encode($new_events));
    print json_encode($new_events, JSON_UNESCAPED_SLASHES);
?>

