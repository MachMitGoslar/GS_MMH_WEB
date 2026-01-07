
<?php
/**
* @var Kirby\Cms\Site $site
* @var Kirby\Cms\Page $page
*/
?>

<rss version="2.0">
<channel>
 <title>MachMit!Haus Newsletter</title>
 <description>Hier findest du alle Ausgaben des monatlichen MachMit!Haus Newsletter</description>
 <link> <?= $parent->url() ?> </link>
 <image>
    <url><?=$parent->parent()->cover?></url>
    <title>MachMit!Haus Newsletter</title>
    <link><?= $parent->url() ?></link>
 </image>
 <copyright>2025 Stadt Goslar | MachMit!Haus</copyright>
 <lastBuildDate><?= $parent->modified($format = "D, d M Y", $handler = null, $languageCode = null) ?> </lastBuildDate>
 <pubDate><?= $parent->modified($format = "D, d M Y", $handler = null, $languageCode = null) ?> </pubDate>
 <ttl>1800</ttl>

 <?php foreach ($pages as $page) : ?>
    <item>
        <title><?= $page->title() ?></title>
        <description><?= $page->headline() ?> </description>
        <link> <?= $page->url() ?></link>
        <pubDate> <?= $page->modified($format = "D, d M Y", $handler = null, $languageCode = null) ?></pubDate>
    </item>
 <?php endforeach ?>

</channel>
</rss>
