<?php

use Mmh\Site\Oveda\Events;

return function () {
    // Fetches events from Oveda for organization and from current date.
    $today = date('Y-m-d');
    $eventPage = Events::datePage($today, perPage: 6);
    $events = Events::normalizeAll($eventPage['items'], $today);

    return compact(['events']);
};
