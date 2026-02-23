<?php

use Kirby\Http\Remote;

return function () {
    //Fetches events from oveda for organization and from current date
    $today = date('Y-m-d');
    $json = Remote::get("https://oveda.de/api/v1/organizations/19/event-dates/search?per_page=6&date_from=" . $today)->json();
    $events = $json['items'];

    //$events = [];

    return compact(['events']);
};
