<?php
/**
 * This is the configuration file for the Yii2 unit tests.
 * You can override configuration values by creating a `config.local.php` file
 * and manipulate the `$config` variable.
 */

//$singleNode = new Predis\Client('tcp://redis-node-0:6379');
//$res = $singleNode->ping();


$config = [
    'databases' => [
        'redis' => [
//            'parameters' => ['tcp://redis-cluster:7000', 'tcp://redis-cluster:7001', 'tcp://redis-cluster:7002'],
            'parameters' => [
                'tcp://redis-node-0:6379', 'tcp://redis-node-1:6379', 'tcp://redis-node-2:6379',
            ],
            'options' => [
                'cluster' => 'redis',
                'parameters' => [
                    'persistent' => true,
                    'conn_uid' => 'id_cluster',
                ],
            ],
        ],
    ],
];

return $config;
