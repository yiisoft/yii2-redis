yii2-redis module with various fixes
====================================

* [getting long string failure](https://github.com/E96/yii2-redis/commit/fdaf09f4d191c7bf29d5e75432385611b90759af)
* [min and max number comparison](https://github.com/E96/yii2-redis/commit/c1e0f6e1c03007a1133ce7916a4683a242e3515f)
* [a bit more info in traces](https://github.com/E96/yii2-redis/commit/fe8df386a0dadbbc192f1be48c2e4dd899c12b98)
* [profiles in debugger DB panel](https://github.com/E96/yii2-redis/commit/05d8d208b25d57b41cb78e04689ccca5be33b473)
* [convert inCondition for one key](https://github.com/E96/yii2-redis/commit/36d6a145acf109d86eb2aa7918f64bc91c935ebb)
* [correct detection modified values](https://github.com/E96/yii2-redis/commit/e4040f0f5c91e291b4b014661d3e85731426f647)
* [correct detection modified values +](https://github.com/E96/yii2-redis/commit/2d2bd7fb72a5702c66f41bce5aee8d1f163e094b)
* [correct detection modified values +](https://github.com/E96/yii2-redis/commit/0238c137ce7e1e76cc832d57538e675b2e462563)
* [fix populate null attributes](https://github.com/E96/yii2-redis/commit/02c879b865937b39f47fb0664329310dcaf3ff94)
* [fix integer values in buildKey](https://github.com/E96/yii2-redis/commit/da6ed85ed15bd33b4137083403c027a15b9fc03d)
* [Make Connection::parseResponse public](https://github.com/E96/yii2-redis/commit/f5ce8325303cda6bd1bc1ecb62d310a5b01661e4)

original README below:


Redis Cache, Session and ActiveRecord for Yii 2
===============================================

This extension provides the [redis](http://redis.io/) key-value store support for the [Yii framework 2.0](http://www.yiiframework.com).
It includes a `Cache` and `Session` storage handler and implements the `ActiveRecord` pattern that allows
you to store active records in redis.

For license information check the [LICENSE](LICENSE.md)-file.

Requirements
------------

At least redis version 2.6.12 is required for all components to work properly.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist e96/yii2-redis
```

or add

```json
"e96/yii2-redis": "~2.0.0"
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
