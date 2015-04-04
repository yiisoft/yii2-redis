Using the Cache component
=========================

To use the `Cache` component, in addition to configuring the connection as described in the [Installation](installation.md) section,
you also have to configure the `cache` component to be `yii\redis\Cache`:

```php
return [
    //....
    'components' => [
        // ...
        'cache' => [
            'class' => 'yii\redis\Cache',
        ],
    ]
];
```

If you only use the redis cache (i.e., not usig its ActiveRecord or Session), you can also configure the parameters of the connection within the
cache component (no connection application component needs to be configured in this case):

```php
return [
    //....
    'components' => [
        // ...
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => [
                'hostname' => 'localhost',
                'port' => 6379,
                'database' => 0,
            ],
        ],
    ]
];
```
