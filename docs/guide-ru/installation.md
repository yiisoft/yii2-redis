Установка
============

## Требования

Для того, чтобы все компоненты работали должным образом, требуется хотя бы версия redis 2.6.12.

## Getting Composer package

Предпочтительным способом установки этого расширения является [composer](http://getcomposer.org/download/).

Либо запустите

```
php composer.phar require --prefer-dist yiisoft/yii2-redis
```

или добавьте

```json
"yiisoft/yii2-redis": "~2.0.0"
```

в секцию `require` вашего composer.json.

## Конфигурирование приложения

Чтобы использовать это расширение, вам необходимо настроить класс Connection в конфигурации вашего приложения:

```php
return [
    //....
    'components' => [
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
    ]
];
```

Это обеспечивает базовый доступ к redis-хранилищу через компонент приложения `redis`:
 
```php
Yii::$app->redis->set('mykey', 'some value');
echo Yii::$app->redis->get('mykey');
```

Смотри [[yii\redis\Connection]] для получения полного списка доступных методов.