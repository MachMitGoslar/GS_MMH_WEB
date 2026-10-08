
<?php
/**
* @var string | null $query
* @uses Kirby\Http\Remote
*/

function sort_by_start($a, $b)
{

    //TODO: Exclude Events that are already gone
    return strnatcmp($a['start'], $b['start']);
}
$query = kirby()->request()->query()->get('data');
$program_id = (int) $query ?: 74;

try {
    $json = Remote::get('https://goslar.feripro.de/api/programs/' . $program_id . '/events/')->json();
} catch (Exception $e) {
    // Handle the error, e.g., log it and return an empty array or a default event
    error_log('Error fetching events: ' . $e->getMessage());
    print json_encode(['error' => 'Error fetching events'], JSON_UNESCAPED_SLASHES);

    return 0;
}

if (!is_array($json) || !isset($json[0]['start'])) {
    // Handle the case where the response is not valid JSON
    error_log('Invalid JSON response from API');
    print json_encode(['error' => 'Invalid response from API'], JSON_UNESCAPED_SLASHES);

    return 0;
}

/* Fetch Events from Caching Server */
//$json = Remote::get('https://crawler.goslar.app/events.json')->json();

$events = $json;
usort($events, 'sort_by_start');
//var_dump($events[1]['start']);

$rand = random_int(0, count($events) - 1);
$event = $events[$rand];
$json_event['title'] = $event['name'];
$json_event['description'] = '<strong>' . $event['name'] . '</strong> - ' . $event['description'];
$json_event['published_at'] = $event['start'];
$json_event['image_url'] = 'https://jugend.goslar.de/fileadmin/user_upload/website/jugendpflege/goslar_app/anriss.png';
$json_event['call_to_action_url'] = 'https://mmh.goslar.de/app/ferienpass_index.json?data=' . $program_id;
print json_encode($json_event, JSON_UNESCAPED_SLASHES);
