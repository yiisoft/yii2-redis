<p align="center">
    <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://www.yiiframework.com/image/yii_logo_dark.svg">
        <source media="(prefers-color-scheme: light)" srcset="https://www.yiiframework.com/image/yii_logo_light.svg">
        <img src="https://www.yiiframework.com/image/yii_logo_light.svg" alt="Yii Framework" height="100px">
    </picture>
    <h1 align="center">Redis Cache, Session and ActiveRecord for Yii 2</h1>
    <br>
</p>

This extension provides the [redis](https://redis.io/) key-value store support for the [Yii framework 2.0](https://www.yiiframework.com).
It includes a `Cache` and `Session` storage handler and implements the `ActiveRecord` pattern that allows
you to store active records in redis.

For license information check the [LICENSE](LICENSE.md)-file.

Documentation is at [docs/guide/README.md](docs/guide/README.md).

[![Latest Stable Version](https://img.shields.io/packagist/v/yiisoft/yii2-redis.svg?style=for-the-badge&label=Stable&logo=packagist)](https://packagist.org/packages/yiisoft/yii2-redis)
[![Total Downloads](https://img.shields.io/packagist/dt/yiisoft/yii2-redis.svg?style=for-the-badge&label=Downloads)](https://packagist.org/packages/yiisoft/yii2-redis)
[![build](https://img.shields.io/github/actions/workflow/status/yiisoft/yii2-redis/build.yml?style=for-the-badge&logo=github&label=Build)](https://github.com/yiisoft/yii2-redis/actions?query=workflow%3Abuild)
[![codecov](https://img.shields.io/codecov/c/github/yiisoft/yii2-redis.svg?style=for-the-badge&logo=codecov&logoColor=white&label=Codecov)](https://codecov.io/gh/yiisoft/yii2-redis)
[![Static Analysis](https://img.shields.io/github/actions/workflow/status/yiisoft/yii2-redis/static.yml?style=for-the-badge&label=Static)](https://github.com/yiisoft/yii2-redis/actions/workflows/static.yml)


Requirements
------------

At least redis version is required for all components to work properly.

Installation
------------

> [!IMPORTANT]
> - The minimum required [PHP](https://www.php.net/) version is PHP `7.4`.
> - It works best with PHP `8`.

The preferred way to install this extension is through [composer](https://getcomposer.org/download/).

Either run

```shell
php composer.phar require --prefer-dist yiisoft/yii2-redis:"~2.1.0"
```

or add

```json
"yiisoft/yii2-redis": "~2.1.0"
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

## Documentation

- [predis support](/docs/guide/predis.md)
- [Internals](docs/internals.md)

## Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?style=for-the-badge&logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

## Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=for-the-badge&logo=yii)](https://www.yiiframework.com/)
[![Follow on X](https://img.shields.io/badge/-Follow%20on%20X-1DA1F2.svg?style=for-the-badge&logo=x&logoColor=white&labelColor=000000)](https://x.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=for-the-badge&logo=telegram)](https://t.me/yii_framework_in_english)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=for-the-badge&logo=slack)](https://yiiframework.com/go/slack)

