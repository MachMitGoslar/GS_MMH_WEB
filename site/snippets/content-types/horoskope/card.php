<?php

use Kirby\Http\Remote;

/**
 * Horoskop Card (JSON)
 *
 * Fetches the daily Goslarer Horoskope from the n8n webhook and returns
 * them as a standard app-card payload (title, description, published_at,
 * image_url, call_to_action_url) with the full `signs` array attached
 * so the app can render each sign locally.
 *
 * @var Kirby\Cms\Site $site
 */
?>
<?php

$date = date("Y-m-d H:i:s");

$sign_array = [
    "bergknappe",
    "erzgaenger",
    "fernhaendlerin",
    "haendler",
    "kaiser",
    "kloserschuelerin",
    "muellerin",
    "rammelsberg",
    "ratsherr",
    "teichgraefin",
    "weberin",
    "zinngiesser"
];

$random_int = rand(0, 11);
$random_sign = $sign_array[$random_int];

$card = [
    'title' => 'Goslarer Horoskope',
    'description' => 'Deine Sterne über dem Rammelsberg – die tagesaktuellen Horoskope für alle zwölf Goslarer Sternzeichen.',
    'published_at' => $date,
    'image_url' => url('assets/pngs/hk_' . $random_sign . ".png"),
    'call_to_action_url' => url('app/horoskope'),
];

print json_encode($card, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

