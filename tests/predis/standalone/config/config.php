<?php
/**
 * This is the configuration file for the Yii2 unit tests.
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
