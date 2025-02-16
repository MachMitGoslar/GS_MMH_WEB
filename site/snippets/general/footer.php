<?php
/**
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 */
?>
<footer class="footer mt-6">
    <section class="grid content">
        <section class="flex flex-column grid-item-span2">
            <h3 class="font-subheadline mb-2">Hauptseiten</h3>
            <ul>
                <?php foreach($site->children()->listed() as $child): ?>
                    <li class="font-footnote">
                        <a href="<?=$child->url()?>"><?=$child->title()?></a>
                    </li>
                <?php endforeach ?>
            </ul>
        </section>
        <section class="flex flex-column grid-item-span2">
            <h3 class="font-subheadline mb-2">Social Media</h3>
            <ul>
                <?php foreach($site->socialmedia()->toStructure() as $child): ?>
                    <li class="font-footnote">
                        <a href="<?=$child->url()?>" target="_blank"><?=$child->name()?></a>
                    </li>
                <?php endforeach ?>
            </ul>
        </section>
        <section class="flex flex-column grid-item-span2">
            <h3 class="font-subheadline mb-2">Newsletter</h3>
            <ul>

            </ul>
        </section>
    </section>
</footer>