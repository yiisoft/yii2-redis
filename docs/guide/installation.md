Installation
============

## Requirements

This extension works on PHP 7.4 and later.
Redis version 2.6.12 or later is required for all components to work properly.

## Composer

The preferred way to install this extension is with [Composer](https://getcomposer.org/download/):

```
php composer.phar require --prefer-dist yiisoft/yii2-redis
```

Alternatively, you may directly add this package to the `require` section of your `composer.json`…

```json
"yiisoft/yii2-redis": "~2.1.0"
```

…and then run `php composer.phar update`.

## Configuration

The most basic usage involves defining a [[yii\redis\Connection|Connection]] component in your application configuration:

```php
return [
    // ...
    'components' => [
        'redis' => [
            'class' => yii\redis\Connection::class,
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
    ]
];
```

You can then [interact with the redis store](usage-commands.md) via that `redis` application component:

```php
Yii::$app->redis->set('mykey', 'some value');
echo Yii::$app->redis->get('mykey');
```

See [[yii\redis\Connection]] for a full list of available methods.

The included [cache](topics-cache.md), [mutex](topics-mutex.md), and [session](topics-session.md) drivers require
additional configuration, and rely on the existence of this `redis` connection component, by default.
