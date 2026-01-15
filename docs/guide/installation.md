Installation
============

## Requirements

At least redis version 2.6.12 is required for all components to work properly.

## Getting Composer package

The preferred way to install this extension is with [Composer](https://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-redis
```

or add

```json
"yiisoft/yii2-redis": "~2.0.0"
```

to the `require` section of your `composer.json`.

## Configuring application

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
