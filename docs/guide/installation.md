Installation
============

## Requirements

At least redis version 2.6.12 is required for all components to work properly.

## Getting Composer package

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-redis
```

or add

```json
"yiisoft/yii2-redis": "~2.0.0"
```

to the require section of your composer.json.

## Configuring application

To use this extension, you have to configure the Connection class in your application configuration:

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
