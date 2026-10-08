<?php

/**
 * PHPUnit bootstrap.
 *
 * Boots a minimal Kirby instance against isolated fixture roots, so tests
 * need neither the real content nor the site config (no DB, no network).
 * `index` points at /public so helpers that resolve asset paths work.
 */

require __DIR__ . '/../vendor/autoload.php';

$tmp = sys_get_temp_dir() . '/mmh-phpunit';

new Kirby\Cms\App([
    'roots' => [
        'index' => dirname(__DIR__) . '/public',
        'base' => dirname(__DIR__),
        'site' => __DIR__ . '/fixtures/site',
        'content' => __DIR__ . '/fixtures/content',
        'config' => __DIR__ . '/fixtures/config',
        'plugins' => __DIR__ . '/fixtures/plugins',
        'cache' => $tmp . '/cache',
        'sessions' => $tmp . '/sessions',
        'accounts' => $tmp . '/accounts',
        'logs' => $tmp . '/logs',
    ],
    'urls' => [
        'index' => 'https://mmh.test',
    ],
    'options' => [
        'date.timezone' => 'Europe/Berlin',
    ],
]);

require_once dirname(__DIR__) . '/site/helpers.php';
require_once dirname(__DIR__) . '/site/controllers/oveda-event.php';
require_once dirname(__DIR__) . '/site/controllers/api-images.php';
require_once dirname(__DIR__) . '/site/controllers/newsletter-email.php';
require_once dirname(__DIR__) . '/site/models/project.php';
