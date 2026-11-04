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
                        <a href="<?=$child->link()?>" target="_blank"><?=$child->title()?></a>
                    </li>
                <?php endforeach ?>
            </ul>
        </section>
        <section class="flex flex-column grid-item-span2">
            <h3 class="font-subheadline mb-2"><a href="<?=$site->page('newsletter')->url()?>">Newsletter</a>  </h3>
            <ul>
            <?php foreach ($site->page('newsletter')->children() as $child) : ?>
                    <li class="font-footnote"> <a href="<?=$child->url()?>"> <?=$child->title()?> </a> </li>
            <?php endforeach ?>
            </ul>
        </section>
        <?php if ($site->general()->isNotEmpty()) : ?>
        <section class="flex flex-column grid-item-span2">
            <h3 class="font-subheadline mb-2">General</h3>
            <ul>
                <?php foreach ($site->general()->toPages() as $child) : ?>
                    <li class="font-footnote"> <a href="<?=$child->url()?>"> <?=$child->title()?> </a> </li>
                <?php endforeach ?>
            </ul>
        </section>
        <?php endif ?>
        <section class="flex flex-column grid-item-span2 footer-credit">
            <h3 class="font-subheadline mb-2">Powered by:</h3>
            <a href="https://getkirby.com" target="_blank" rel="noopener" class="footer-credit__link" aria-label="Kirby CMS – getkirby.com">
                <img src="<?= url('assets/svg/kirby-logo.svg') ?>" alt="Kirby CMS" class="footer-credit__logo" width="36" height="42" loading="lazy">
            </a>
            <p class="font-footnote mt-2"><a href="https://getkirby.com" target="_blank" rel="noopener">getkirby.com</a></p>
        </section>
    </section>
    <?= js('assets/js/lightbox/fslightbox.js') ?>

</footer>