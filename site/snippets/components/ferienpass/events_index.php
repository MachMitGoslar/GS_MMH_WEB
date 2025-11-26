
<?php
/**
* @var Kirby\Cms\Site $site
* @var Kirby\Cms\Page $page
*/
?>
<?php 
$program_id = 69;
$json = Remote::get('https://goslar.feripro.de/api/programs/'.$program_id.'/events/')->json();
//$json = Remote::get('https://crawler.goslar.app/events.json')->json();

$events = $json;

usort($events, 'sort_by_start');
//var_dump($events[1]['start']);

function sort_by_start($a, $b) {
 
    //TODO: Exclude Events that are already gone
    return strnatcmp($a['start'], $b['start']);
}

$new_events = [];
    $id = 0;

foreach($events as $event) {
    $json_event = [
        
    ];
    $json_event['id'] = $id;
    $json_event['title'] = $event['name']; 
    $json_event['description'] = $event['duration_summary'] . " " . $event['description'];
    $json_event['published_at']  = $event['start'];
    $json_event['image_url'] = $event['cover_photo'] ? $event['cover_photo']['thumbnail'] : "https://jugend.goslar.de/fileadmin/_processed_/3/1/csm_Post_a87fefbd79.png";
    $json_event['call_to_action_url'] = "https://goslar.feripro.de/anmeldung/".$program_id."/veranstaltungen/".$event["event_id"];
    array_push($new_events, $json_event);
    $id++;

}   
    //var_dump(json_encode($new_events));
    print json_encode($new_events, JSON_UNESCAPED_SLASHES);
?>

