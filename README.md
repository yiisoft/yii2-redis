<p align="center">
    <a href="https://redis.io/" target="_blank" rel="external">
        <img src="https://download.redis.io/redis.png" height="100px">
    </a>
    <h1 align="center">Redis Cache, Session and ActiveRecord for Yii 2</h1>
    <br>
</p>

This extension provides the [redis](https://redis.io/) key-value store support for the [Yii framework 2.0](https://www.yiiframework.com).
It includes a `Cache` and `Session` storage handler and implements the `ActiveRecord` pattern that allows
you to store active records in redis.

For license information check the [LICENSE](LICENSE.md)-file.

Documentation is at [docs/guide/README.md](docs/guide/README.md).

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii2-redis/v/stable.png)](https://packagist.org/packages/yiisoft/yii2-redis)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii2-redis/downloads.png)](https://packagist.org/packages/yiisoft/yii2-redis)
[![Build status](https://github.com/yiisoft/yii2-redis/workflows/build/badge.svg)](https://github.com/yiisoft/yii2-redis/actions?query=workflow%3Abuild)


Requirements
------------

At least redis version is required for all components to work properly.

Installation
------------

The preferred way to install this extension is through [composer](https://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-redis:^22.x-dev
```

or add

```json
"yiisoft/yii2-redis": "^22.x-dev"
```

to the require section of your composer.json.


Configuration
-------------

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

**SSL configuration** example:
```php
return [
    //....
    'components' => [
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6380,
            'database' => 0,
            'useSSL' => true,
            // Use contextOptions for more control over the connection (https://www.php.net/manual/en/context.php), not usually needed
            'contextOptions' => [
                'ssl' => [
                    'local_cert' => '/path/to/local/certificate',
                    'local_pk' => '/path/to/local/private_key',
                ],
            ],
        ],
    ],
];
```

**Configuring The Connection Scheme**

By default, Redis will use the tcp scheme when connecting to your Redis server; however, you may use TLS / SSL encryption by specifying a scheme configuration option in your application configuration:
```php
return [
    //....
    'components' => [
        'redis' => [
            //....
            'scheme' => 'tls'
        ]
    ]
];
```
