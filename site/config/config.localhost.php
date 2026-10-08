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
    // Optional remote DB, credentials come from the environment (never commit them)
    'db' => [
        'host' => getenv('MMH_DB_HOST') ?: 'localhost',
        'database' => getenv('MMH_DB_NAME') ?: '',
        'user' => getenv('MMH_DB_USER') ?: '',
        'password' => getenv('MMH_DB_PASSWORD') ?: '',
    ],
    'url' => 'http://localhost:8001',

];
