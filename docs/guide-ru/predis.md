Predis для Redis Cache, Session и ActiveRecord 
===============================================
## Конфигурирование приложения

Чтобы использовать это расширение, вам необходимо настроить класс [[yii\redis\predis\PredisConnection]] в конфигурации вашего приложения:

> Warning: Класс `yii\redis\predis\PredisConnection` поддерживает подключение redis-cluster, но не даёт поддержки интерфейсов компонентов *cache*, *session*, *ActiveRecord*, *mutex*

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

>  Больше информации можно о конфигурации подключения и опциях можно получить в документации <a href="https://github.com/predis/predis">predis</a>.

Это обеспечивает базовый доступ к redis-хранилищу через компонент приложения `redis`:

```php
Yii::$app->redis->set('mykey', 'some value');
echo Yii::$app->redis->get('mykey');
```

Дополнительно
-----------------

* [Использование компонента Cache с predis](topics-predis-cache.md)
* [Использование компонента Session с predis](topics-predis-session.md)

