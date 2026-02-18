<?php

use Kirby\Http\Remote;

return function () {
    //Fetches events from oveda for organization and from current date
    $today = date('Y-m-d');
    $keyword = get('keyword', '');

    $url = "https://oveda.de/api/v1/organizations/19/event-dates/search?per_page=12&date_from=" . $today;

    if ($keyword) {
        $url .= "&keyword=" . urlencode($keyword);
    }

    if ($page = get('page')) {
        $url .= "&page=" . $page;
    }

    $json = Remote::get($url)->json();
    $events = $json['items'];

    return compact(['events', 'json', 'keyword']);
};
