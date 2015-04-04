Using the Session component
===========================

To use the `Session` component, in addition to configuring the connection as described in [Installation](installation.md) section,
you also have to configure the `session` component to be `yii\redis\Session`:

```php
return [
    //....
    'components' => [
        // ...
        'session' => [
            'class' => 'yii\redis\Session',
        ],
    ]
];
```

If you use redis only for a session storage, you can also configure the parameters of the connection within the
session component (no connection application component needs to be configured in this case):

```php
return [
    //....
    'components' => [
        // ...
        'session' => [
            'class' => 'yii\redis\Session',
            'redis' => [
                'hostname' => 'localhost',
                'port' => 6379,
                'database' => 0,
            ],
        ],
    ]
];
```
