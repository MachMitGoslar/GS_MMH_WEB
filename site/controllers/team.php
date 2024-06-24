
<?php 
return function ($page, $site, $kirby) {

/**
 * We use the collection helper to fetch the tags collection defined in `/site/bluebrint/members.php`
 * 
 * More about collections:
 * https://getkirby.com/docs/guide/templates/collections
 */
$shared = $kirby->controller('site' , compact('site'));

$tags = $page->children()->listed()->pluck('teams', ',', true);



return A::merge($shared, ['tags' => $tags]);

};
?>