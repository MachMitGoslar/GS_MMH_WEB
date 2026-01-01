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
        <?php foreach ($site->children()->listed() as $child) : ?>
          <li class="font-footnote">
            <a href="<?=$child->url()?>"><?=$child->title()?></a>
          </li>
        <?php endforeach ?>
      </ul>
    </section>
    <section class="flex flex-column grid-item-span2">
      <h3 class="font-subheadline mb-2">Social Media</h3>
      <ul>
        <?php foreach ($site->social()->toStructure() as $child) : ?>
          <li class="font-footnote">
            <a href="<?=$child->link() ? $child->link() : "test" ?>" target="_blank"><?=$child->title()?></a>
          </li>
        <?php endforeach ?>
      </ul>
    </section>
    <section class="flex flex-column grid-item-span2">
      <h3 class="font-subheadline mb-2">Newsletter</h3>
      <ul>
        <li class="font-footnote">

        </li>
      </ul>
    </section>
    <section class="flex flex-column grid-item-span2">
      <h3 class="font-subheadline mb-2">General</h3>
      <ul>
        <li class="font-footnote">

        </li>
      </ul>
    </section>
  </section>
</footer>