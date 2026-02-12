<?php

use Kirby\Database\Db;

return function ($page): array {
    $results = DB::select("app_requests");
    foreach (DB::select("app_requests") as $request) {
        $data[$request->day()][$request->url()] = $request->requests();
    }

    $start = new DateTime();

    $values = [
        "days" => [],
        "urls" => [],
    ];

    foreach ($results->group("url")->toArray() as $url => $value) {
        $values["urls"][$url] = [];
    }



    for ($i = 0; $i < 5; $i++) {
        $interval = new DateInterval("P1D");
        $day = $start->sub($interval)->format("Y-m-d");
        array_push($values["days"], $day);
        foreach ($values["urls"] as $url => $value) {
            if (isset($data[$day][$url])) {
                array_push($values["urls"][$url], $data[$day][$url]);
            } else {
                array_push($values["urls"][$url], 0);
            }
        }
    }


    return [
        "datasets" => $values["urls"],
        "days" => $values["days"],
    ];
};
