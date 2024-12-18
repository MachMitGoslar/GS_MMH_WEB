<?php
/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/
?>
<li class="c-projectUpdateTeaserCard">
  <div >
    <img class="c-projectUpdateTeaserCard-hero" src="https://picsum.photos/1600/800?random=<?=$id?>">
  </div>
  <div class="c-projectUpdateTeaserCard-content">
    <div class="c-projectUpdateTeaserCard-statusheader">
      <div class="c-projectUpdateTeaserCard-badge mb-2">Status</div>
      <time><?=date("d.m.Y")?></time>
    </div>
    <h3 class="font-headline font-line-height-narrow mb-2">Maecenas sed diam eget risus varius blandit sit amet non magna.</h3>
    <p class="font-body">Donec sed odio dui. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
  </div>
</li>