
<?php
use Kirby\Http\Remote;

/**
* @var Kirby\Cms\Site $site
* @var Kirby\Cms\Page $page
* @var string|null $data
* @uses Kirby\Http\Remote
**/

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

$events = $json;
usort($events, 'sort_by_start');
//var_dump($events[1]['start']);

function sort_by_start($a, $b)
{

    //TODO: Exclude Events that are already gone
    return strnatcmp($a['start'], $b['start']);
}

$new_events = [];
$id = 0;
foreach ($events as $event) {
    $json_event = [

    ];
    $json_event['id'] = $id;
    $json_event['title'] = $event['name'];
    $json_event['description'] = $event['duration_summary'] . ' ' . $event['description'];
    $json_event['published_at'] = $event['start'];
    $json_event['image_url'] = $event['cover_photo'] ? $event['cover_photo']['thumbnail'] : 'https://jugend.goslar.de/fileadmin/_processed_/3/1/csm_Post_a87fefbd79.png';
    $json_event['call_to_action_url'] = 'https://goslar.feripro.de/anmeldung/' . $program_id . '/veranstaltungen/' . $event['event_id'];
    array_push($new_events, $json_event);
    $id++;
}
//var_dump(json_encode($new_events));
print json_encode($new_events, JSON_UNESCAPED_SLASHES);
