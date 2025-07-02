Использование компонента Cache в месте с predis
=========================

Чтобы использовать компонент `Cache`, в дополнение к настройке соединения, как описано в разделе [predis](predis.md), вам также нужно настроить компонент `cache` как [[yii\redis\Cache]]:

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

Если вы используете только кеш redis (т.е. не используете его ActiveRecord или Session), вы также можете настроить параметры соединения в пределах кеш-компонента (в этом случае необходимо настроить конфигурационный компонент подключения):

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

Кэш предоставляет все методы [[yii\caching\CacheInterface]]. Если вы хотите получить доступ к определенным redis методам, которые не присутствуют
в интерфейсе, вы можете использовать их через [[yii\redis\Cache::$redis]], который является экземпляром [[yii\redis\Connection]]:

```php
Yii::$app->cache->redis->hset('mykey', 'somefield', 'somevalue');
Yii::$app->cache->redis->hget('mykey', 'somefield');
...
```

Смотри [[yii\redis\Connection]] для получения полного списка доступных методов.
