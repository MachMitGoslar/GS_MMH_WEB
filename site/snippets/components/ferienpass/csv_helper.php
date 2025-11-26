
<?php
/**
* CSV Helper for Ferienpass Events
* Fetches events from API and exports to CSV
*/
?>
<?php 
// Fetch JSON data using native PHP
$url = 'https://goslar.feripro.de/api/programs/69/events/';
$jsonData = file_get_contents($url);
$json = json_decode($jsonData, true);

$events = $json;

$file = fopen("contacts.csv","w");
fputs( $file, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF) );
fputcsv($file, array("Name", "Age", "Group", "Price", "Organizer", "ID", "Dates","Meeting", "@Image"), ";", enclosure: "\"", escape: ",");

function calculate_age($event) {
    if($event["min_age"] != "") {
        if($event["max_age"] != "") {
            return $event["min_age"]." - ".$event["max_age"];
        } else {
            return "ab ".$event["min_age"];
        }
    } else {
        if($event["max_age"] != "") {
            return "bis ".$event["max_age"];
        } else {
            return "offen";
        }
    }
}

    function calculate_group($event) {
        if($event["min_participants"] != "") {
            if($event["max_participants"] != "") {
                return $event["min_participants"]." - ".$event["max_participants"];
            } else {
                return "min. ".$event["min_participants"];
            }
        } else {
            if($event["max_participants"] != "") {
                return "max. ".$event["max_participants"];
            } else {
                return "offen";
            }
        }
    }

    function calculate_price($event) {
        if($event["price"] != 0) {
            return number_format($event["price"], 2);
        } else {
            return "frei";
        }
    }
 
    function download_image($event) {
        $url = $event['cover_photo'] ? $event['cover_photo']['medium'] : "https://jugend.goslar.de/fileadmin/_processed_/9/2/csm_jugendarbeit_3d_3051faadc4.png";
        $img = 'pics/'.$event["relative_id"].".png";
        $data = file_get_contents($url);
        //print($data);
        $file = imagecreatefromstring($data);
        $imageSize_w = imagesx($file);
        $imageSize_h = imagesy($file);
        //$file = imagecrop($file, ['x' => 0, 'y' => ($imageSize_h-$imageSize_w*(9/16))/2, 'width' => $imageSize_w, 'height' =>  $imageSize_w*(9/16)]);
        
        imagepng($file, $img);
        return $img;
    }

    function escape_string($string) {
        $string = str_replace(array("\n"), ', ', $string);
        return str_replace(array("\r"), '', $string);
    }

foreach($events as $event) {
    
    $fields = array(
        $event["name"],
        calculate_age($event),
        calculate_group($event),
        calculate_price($event),
        $event["organizer"]["name"],
        $event["relative_id"],
        escape_string($event["duration_summary"]),
        escape_string($event["meeting_point"]),
        download_image($event)
        );


   
    fputcsv($file, $fields, ";", "\"", "\\");
}
    fclose($file);
    //var_dump(json_encode($new_events));
    //print json_encode($new_events, JSON_UNESCAPED_SLASHES);

    
?>

