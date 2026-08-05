<?php

/**
 * This is the configuration file for the Yii2 unit tests.
 */

$config = [
    'databases' => [
        'redis' => [
            'parameters' => ['tcp://redis-sentinel-1:26379', 'tcp://redis-sentinel-2:26379', 'tcp://redis-sentinel-3:26379'],
            'options' => [
                'replication' => 'sentinel',
                'service' => 'mymaster',
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
