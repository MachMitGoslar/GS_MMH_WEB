<?php


/**
 * The config file is optional. It accepts a return array with config options
 * Note: Never include more than one return statement, all options go within this single return array
 * In this example, we set debugging to true, so that errors are displayed onscreen.
 * This setting must be set to false in production.
 * All config options: https://getkirby.com/docs/reference/system/options
 */
return [
    'debug' => true,
    'panel' => [
        'install' => true,
    ],
    'db' => [
        'host' => 'mmh.goslar.de',
        'database' => 'admin_test_mmh',
        'user' => 'admin_test_mmh',
        'password' => '147bwl4_Q',
    ],
    'url' => 'http://localhost:8001',



];
