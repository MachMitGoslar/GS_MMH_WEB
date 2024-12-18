<?php
/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/
?>
<?php snippet('mainLayout', slots: true) ?>
	<?php slot()?>
    <h1 class="mb-1"><?= $page->title() ?></h1>
    <h2 class=" h4 mb-2">Buttons</h2>
    <h3 class="h5 mb-2">Size</h3>
    <div class="flex items-center mb-3 " style="--gap: 1rem" >
        <a class="gs-c-btn" data-type="primary" data-size="large" href="" >Large Button</a>
        <a class="gs-c-btn" data-type="primary" data-size="regualr" href="" >Regular Button</a>
        <a class="gs-c-btn" data-type="primary" data-size="small" href="" >Small Button</a>
    </div>
    <div class="flex items-center mb-3 " style="--gap: 1rem" >
        <a class="gs-c-btn" data-type="primary" data-size="large" data-style="rounded-corners" href="" >Large Button</a>
        <a class="gs-c-btn" data-type="primary" data-size="regualr" data-style="rounded-corners" href="" >Regular Button</a>
        <a class="gs-c-btn" data-type="primary" data-size="small" data-style="rounded-corners" href="" >Small Button</a>
    </div>
    <div class="flex items-center mb-3 " style="--gap: 1rem" >
        <a class="gs-c-btn" data-type="secondary" data-size="large" data-style="rounded-corners" href="" >Large Button</a>
        <a class="gs-c-btn" data-type="secondary" data-size="regualr" data-style="rounded-corners" href="" >Regular Button</a>
        <a class="gs-c-btn" data-type="secondary" data-size="small" data-style="rounded-corners" href="" >Small Button</a>
    </div>
    <div class="flex items-center mb-3 " style="--gap: 1rem" >
        <a class="gs-c-btn" data-type="tertiary" data-size="large" data-style="rounded-corners" href="" >Large Button</a>
        <a class="gs-c-btn" data-type="tertiary" data-size="regualr" data-style="rounded-corners" href="" >Regular Button</a>
        <a class="gs-c-btn" data-type="tertiary" data-size="small" data-style="rounded-corners" href="" >Small Button</a>
    </div>

    <h3 class="h5 mb-2">Type</h3>
    <div class="flex mb-3" style="--gap: 1rem" >
        <a class="gs-c-btn" data-type="primary" data-size="regular" href="" >Primary</a>
        <a class="gs-c-btn" data-type="secondary" data-size="regular" href="" >Secondary</a>
        <a class="gs-c-btn" data-type="tertiary" data-size="regular" href="" >Tertiary</a>
    </div>
    <h3 class="h5 mb-2">Style</h3>
    <div class="flex mb-2" style="--gap: 1rem" >
        <a class="gs-c-btn" data-type="primary" data-size="regular" data-style="pill" href="">Pill</a>
        <a class="gs-c-btn" data-type="primary" data-size="regular" data-style="rounded-corners" href="">Rounded corners</a>
        <a class="gs-c-btn" data-type="primary" data-size="regular" data-style="square" href="" >Square</a>
    </div>
    <div class="flex mb-3" style="--gap: 1rem" >
        <a class="gs-c-btn" data-type="secondary" data-size="regular" data-style="pill" href="" >Pill</a>
        <a class="gs-c-btn" data-type="secondary" data-size="regular" data-style="rounded-corners" href="" >Rounded corners</a>
        <a class="gs-c-btn" data-type="secondary" data-size="regular" data-style="square" href="" >Square</a>
    </div>
    <h3 class="h5 mb-2">Disabled</h3>
    <div class="flex mb-2" style="--gap: 1rem" >
        <a class="gs-c-btn" data-type="primary" data-size="regular" data-style="pill" href="" disabled>Pill</a>
        <a class="gs-c-btn" data-type="primary" data-size="regular" data-style="rounded-corners" href="" disabled>Rounded corners</a>
        <a class="gs-c-btn" data-type="primary" data-size="regular" data-style="square" href="" disabled>Square</a>
    </div>
    <div class="flex mb-3" style="--gap: 1rem" >
        <a class="gs-c-btn" data-type="secondary" data-size="regular" data-style="pill" href="" disabled>Pill</a>
        <a class="gs-c-btn" data-type="secondary" data-size="regular" data-style="rounded-corners" href="" disabled>Rounded corners</a>
        <a class="gs-c-btn" data-type="secondary" data-size="regular" data-style="square" href="" disabled>Square</a>
    </div>
    <h3 class="h5 mb-2">Role</h3>
    <div class="flex mb-2" style="--gap: 1rem" >
        <a class="gs-c-btn" data-type="primary" data-size="regular" data-style="pill" data-role="destructive" href="" >Destructive</a>
        <a class="gs-c-btn" data-type="primary" data-size="regular" data-style="rounded-corners" data-role="destructive" href="" >Destructive</a>
        <a class="gs-c-btn" data-type="primary" data-size="regular" data-style="square" data-role="destructive" href="" >Destructive</a>
    </div>
    <div class="flex mb-2" style="--gap: 1rem" >
        <a class="gs-c-btn" data-type="primary" data-size="regular" data-style="pill" data-role="destructive" href="" disabled>Destructive</a>
        <a class="gs-c-btn" data-type="primary" data-size="regular" data-style="rounded-corners" data-role="destructive" href="" disabled>Destructive</a>
        <a class="gs-c-btn" data-type="primary" data-size="regular" data-style="square" data-role="destructive" href="" disabled>Destructive</a>
    </div>
    <div class="flex mb-2" style="--gap: 1rem" >
        <a class="gs-c-btn" data-type="secondary" data-size="regular" data-style="pill" data-role="destructive" href="" >Destructive</a>
        <a class="gs-c-btn" data-type="secondary" data-size="regular" data-style="rounded-corners" data-role="destructive" href="" >Destructive</a>
        <a class="gs-c-btn" data-type="secondary" data-size="regular" data-style="square" data-role="destructive" href="" >Destructive</a>
    </div>
    <div class="flex mb-2" style="--gap: 1rem" >
        <a class="gs-c-btn" data-type="secondary" data-size="regular" data-style="pill" data-role="destructive" href="" disabled>Destructive</a>
        <a class="gs-c-btn" data-type="secondary" data-size="regular" data-style="rounded-corners" data-role="destructive" href="" disabled>Destructive</a>
        <a class="gs-c-btn" data-type="secondary" data-size="regular" data-style="square" data-role="destructive" href="" disabled>Destructive</a>
    </div>


	<?php endslot() ?>
<?php endsnippet() ?>