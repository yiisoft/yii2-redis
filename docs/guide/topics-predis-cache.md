Using the Cache component with predis
=========================

To use the `Cache` component, in addition to configuring the connection as described in the [predis](predis.md) section,
you also have to configure the `cache` component to be [[yii\redis\Cache]]:

```php
return [
    //....
    'components' => [
        // ...
        'redis' => [
            'class' => 'yii\redis\predis\PredisConnection',
            'parameters' => 'tcp://redis:6379',
            // ...
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
        ],
    ]
];
```

If you only use the redis cache (i.e., not using its ActiveRecord or Session), you can also configure the parameters of the connection within the
cache component (no connection application component needs to be configured in this case):

```php
return [
    //....
    'components' => [
        // ...
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => [
                'class' => 'yii\redis\predis\PredisConnection',
                'parameters' => 'tcp://redis:6379',
                // ...
            ],
        ],
    ]
];
```

The cache provides all methods of the [[yii\caching\CacheInterface]]. If you want to access redis specific methods that are not
included in the interface, you can use them via [[yii\redis\Cache::$redis]], which is an instance of [[yii\redis\ConnectionInterface]]:

```php
Yii::$app->cache->redis->hset('mykey', 'somefield', 'somevalue');
Yii::$app->cache->redis->hget('mykey', 'somefield');
...
```

See [[yii\redis\predis\PredisConnection]] for a full list of available methods.
