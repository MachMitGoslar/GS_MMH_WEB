<?php

use Kirby\Database\Db;

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
?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  const ctx = document.getElementById('container');

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: [<?php
        foreach ($values['days'] as $day) {
            echo($day.",");
        }
?>],
      datasets: [
        <?php foreach ($values["urls"] as $url => $value) : ?>
          { label: "<?= $url ?>",
          data: [ 
            <?php foreach ($values["urls"][$url] as $data_point) : ?>
                <?= $data_point."," ?>
            <?php endforeach ?>
          ],
          borderWidth: 1
          },
        <?php endforeach ?>
    ]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
</script>