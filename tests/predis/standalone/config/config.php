<?php
/**
 * This is the configuration file for the Yii2 unit tests.
 * You can override configuration values by creating a `config.local.php` file
 * and manipulate the `$config` variable.
 */

$config = [
    'databases' => [
        'redis' => [
            'parameters' => 'tcp://redis:6379',
            'options' => [
                'parameters' => [
                    'password' => null,
                    'database' => 0,
                    /** @see \Predis\Connection\StreamConnection */
                    'persistent' => true,
                    'async_connect' => true,
                    'read_write_timeout' => 0.1,
                ],
            ],
        ],
    ],
];

return $config;
