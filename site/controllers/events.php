<?php

use Kirby\Http\Remote;

return function () {
    //Fetches events from oveda for organization and from current date

    $json = "";
    if ($page = get('page')) {
        $json = Remote::get("https://oveda.de/api/v1/organizations/19/event-dates/search?per_page=12&event_list_id=9&page=".$page)->json();
    } else {
        $json = Remote::get("https://oveda.de/api/v1/organizations/19/event-dates/search?per_page=12&event_list_id=9")->json();
    }
    $events = $json['items'];
    //$events = [];

    return compact(['events', 'json']);
};
