Using the Session component with Predis
===========================

To use the `Session` component, in addition to configuring the connection as described in the [predis](predis.md) section,
you also have to configure the `session` component to be [[yii\redis\Session]]:

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
        'session' => [
            'class' => 'yii\redis\Session',
        ],
    ]
];
```

If you only use Redis session (i.e., not using its ActiveRecord or Cache), you can also configure the parameters of the connection within the
session component (no connection application component needs to be configured in this case):

```php
return [
    //....
    'components' => [
        // ...
        'session' => [
            'class' => 'yii\redis\Session',
            'redis' => [
                'class' => 'yii\redis\predis\PredisConnection',
                'parameters' => 'tcp://redis:6379',
                // ...
            ],
        ],
    ]
];
```
