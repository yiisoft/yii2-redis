Predis for Redis Cache, Session и ActiveRecord 
===============================================
## Configuring application

To use this extension, you have to configure the [[yii\redis\predis\PredisConnection]] class in your application configuration:

> Warning: The yii\redis\predis\PredisConnection class supports redis-cluster connection, but does not provide support for the *cache*, *session*, *ActiveRecord*, *mutex* component interfaces.

### standalone
```php
return [
    //....
    'components' => [
        'redis' => [
            'class' => 'yii\redis\predis\PredisConnection',
            'parameters' => 'tcp://redis:6379',
            'options' => [
                'parameters' => [
                    'password' => 'secret', // Or NULL
                    'database' => 0,
                    'persistent' => true,
                    'async_connect' => true,
                    'read_write_timeout' => 0.1,
                ],
            ],
        ],
    ]
];
```
### sentinel
```php
return [
    //....
    'components' => [
        'redis' => [
            'class' => 'yii\redis\predis\PredisConnection',
            'parameters' => [
                'tcp://redis-node-1:26379',
                'tcp://redis-node-2:26379',
                'tcp://redis-node-3:26379',
            ],
            'options' => [
                'parameters' => [
                    'password' => 'secret', // Or NULL
                    'database' => 0,
                    'persistent' => true,
                    'async_connect' => true,
                    'read_write_timeout' => 0.1,
                ],
            ],
        ],
    ]
];
```

> More detailed information about the configuration and connection parameters can be found in the <a href="https://github.com/predis/predis">predis</a> documentation.

This provides the basic access to redis storage via the `redis` application component:

```php
Yii::$app->redis->set('mykey', 'some value');
echo Yii::$app->redis->get('mykey');
```

Additional topics
-----------------

* [Using the Cache Component with Predis](topics-predis-cache.md)
* [Using the Session Component with Predis](topics-predis-session.md)
