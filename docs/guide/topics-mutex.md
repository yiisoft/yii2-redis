Using the Mutex component
=========================

To use Redis for your application’s mutex locks, you must configure the `mutex` component to be an instance of [[yii\redis\Mutex]], in addition to configuring the connection (as described in the [Installation](installation.md) section):

```php
return [
    // ...
    'components' => [
        // ...base `redis` component definition...
        'mutex' => [
            'class' => yii\redis\Mutex::class,
        ],
    ]
];
```

By default, `yii\redis\Mutex` uses the globally-configured `redis` connection component.

If you plan on using Redis for more than one component, configure your connection directly on the `mutex` component:

```php
return [
    //....
    'components' => [
        // ...
        'mutex' => [
            'class' => yii\redis\Mutex::class,
            'redis' => [
                'class' => yii\redis\Connection::class,
                'hostname' => 'localhost',
                'port' => 6379,
                'database' => 1,
            ],
        ],
    ]
];
```

Note that the connection explicitly designates database `1` for the cache. To avoid inadvertent flushing of the [cache](topics-cache.md) or [sessions](topics-session.md), each component should be configured with a different `database`.
