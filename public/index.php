

<?php

require __DIR__ . '/../vendor/autoload.php';

$kirby = new Kirby\Cms\App([
    'roots' => [
        'index' => __DIR__,
        'site' => __DIR__ . '/../site',
        'content' => __DIR__ . '/../content',
        'media' => __DIR__ . '/./media',
        'assets' => __DIR__ . './assets',
        'cache' => __DIR__ . '/../storage/cache',
        'sessions' => __DIR__ . '/../storage/sessions',
        'logs' => __DIR__ . '/../storage/logs',
        'accounts' => __DIR__ . '/../storage/accounts',
        'panel' => __DIR__ . '/../kirby/panel',
        'plugins' => __DIR__ . '/../site/plugins',
        'config' => __DIR__ . '/../site/config',

    ],
]);
echo $kirby->render();
