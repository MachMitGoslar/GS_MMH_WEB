
<?php

require __DIR__ . '/vendor/autoload.php';

$kirby = new Kirby\Cms\App();
echo $kirby->render();
