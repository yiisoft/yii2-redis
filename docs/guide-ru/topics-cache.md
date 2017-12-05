Использование компонента Cache
=========================

Чтобы использовать компонент `Cache`, в дополнение к настройке соединения, как описано в разделе [Установка](installation.md), вам также нужно настроить компонент `cache` как `yii\redis\Cache`:

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

Если вы используете только кеш redis (т.е. не используете его ActiveRecord или Session), вы также можете настроить параметры соединения в пределах кеш-компонента (в этом случае необходимо настроить конфигурационный компонент подключения):

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

Кэш предоставляет все методы [[yii\caching\CacheInterface]]. Если вы хотите получить доступ к определенным redis методам, которые не присутствуют
в интерфейсе, вы можете использовать их через [[yii\redis\Cache::$redis]], который является экземпляром [[yii\redis\Connection]]:

```php
Yii::$app->cache->redis->hset('mykey', 'somefield', 'somevalue');
Yii::$app->cache->redis->hget('mykey', 'somefield');
...
```
Смотри [[yii\redis\Connection]] для получения полного списка доступных методов.