Using the Cache component
=========================

To use redis for your application’s cache, you must configure the `cache` component to be an instance of [[yii\redis\Cache]], in addition to configuring the connection (as described in the [Installation](installation.md) section):

```php
return [
    // ...
    'components' => [
        // ...base `redis` component definition...
        'cache' => [
            'class' => yii\redis\Cache::class,
        ],
    ]
];
```

By default, `yii\redis\Cache` uses the globally-configured `redis` connection component.

If you plan on using redis for more than one component, configure your connection directly on the `cache` component:

```php
return [
    //....
    'components' => [
        // ...
        'cache' => [
            'class' => yii\redis\Cache::class,
            'redis' => [
                'class' => yii\redis\Connection::class,
                'hostname' => 'localhost',
                'port' => 6379,
                'database' => 0,
            ],
        ],
    ]
];
```

Note that the connection explicitly designates database `0` for the cache. To avoid inadvertent flushing of [sessions](topics-session.md) or [mutex locks](topics-mutex.md), each component should be configured with a different `database`.

As a last resort, the [[yii\redis\Cache::$shareDatabase]] can be set to `true` to replace indiscriminate flushing (via the `FLUSHDB` command) with a combination of `SCAN` and sequential `DEL` commands. For applications with many cache keys, this can cause “flushes” to consume huge amounts of resources; the required time also scales linearly—if a single deletion typically takes 1ms, 100,000 keys would take at least 10 seconds (`SCAN` returns batches of 10, by default, and those are grouped into a single `DEL` command).

The cache provides all methods of the [[yii\caching\CacheInterface]]. If you want to access redis-specific methods that are _not_
included in the interface, you can use them via [[yii\redis\Cache::$redis]], which is an instance of [[yii\redis\ConnectionInterface]]:

```php
Yii::$app->cache->redis->hset('mykey', 'somefield', 'somevalue');
Yii::$app->cache->redis->hget('mykey', 'somefield');
...
```

See [[yii\redis\Connection]] for a full list of available methods.
