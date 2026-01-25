Using the Session component
===========================

To use redis for your application’s session storage, you must configure the `session` component to be an instance of [[yii\redis\Session]], in addition to configuring the connection (as described in the [Installation](installation.md) section):

```php
return [
    // ...
    'components' => [
        // ...base `redis` component definition...
        'session' => [
            'class' => yii\redis\Session::class,
        ],
    ]
];
```

By default, `yii\redis\Session` uses the globally-configured `redis` connection component.

If you plan on using redis for more than one component, configure your connection directly on the `session` component:

```php
return [
    //....
    'components' => [
        // ...
        'session' => [
            'class' => yii\redis\Session::class,
            'redis' => [
                'class' => yii\redis\Connection::class,
                'hostname' => 'localhost',
                'port' => 6379,
                'database' => 2,
            ],
        ],
    ]
];
```

Note that the connection explicitly designates database `2` for session storage. To avoid inadvertent flushing of the [cache](topics-cache.md) or [mutex locks](topics-mutex.md), each component must be configured with a different `database`.
