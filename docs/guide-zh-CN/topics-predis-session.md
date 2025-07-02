Использование компонента Session в месте с predis
===========================

Чтобы использовать компонент `Session`, в дополнение к настройке соединения, как описано в разделе [predis](predis.md), вам также нужно настроить компонент `session` как [[yii\redis\Session]]:

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

Если вы используете только redis сессии (т.е. не используете его ActiveRecord или Cache), вы также можете настроить параметры соединения в компоненте сеанса (в этом случае не нужно настраивать компонент приложения подключения):

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
