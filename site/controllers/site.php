<?php
return function () {
  //Fetches events from oveda for organization and from current date
  $json = Remote::get('https://oveda.de/api/v1/event-dates/search?per_page=6&date_from='. date('Y-m-d') .'&organization_id=19')->json();
  $events = $json['items'];

  

  return compact(['events']);
};
?>