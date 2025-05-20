
<?php
/**
* @var Kirby\Cms\Site $site
* @var Kirby\Cms\Page $page
*/
?>
<?php 




    $index_object['title'] = "Deine Ferien in Goslar"; 
    $index_object['description'] = "Hier findest du alle Events für deine Ferien in Goslar";
    $index_object['published_at']  = "2025-05-20 10:00:00";
    $index_object['image_url'] = "https://jugend.goslar.de/fileadmin/_processed_/6/e/csm_Post_e4848fd3c5.png";
    $index_object['call_to_action_url'] = "https://mmh.goslar.de/ferienpass.json";
    

    //var_dump(json_encode($new_events));
    print json_encode($index_object, JSON_UNESCAPED_SLASHES);
?>

