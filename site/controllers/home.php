<?php

require_once __DIR__ . '/events-api.php';

return function () {
    // Fetches events from Oveda for organization and from current date.
    $today = date('Y-m-d');
    $eventPage = mmhOvedaEventDatePage($today, perPage: 6);
    $events = $eventPage['items'];

    return compact(['events']);
};
