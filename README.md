yii2-redis module with various fixes
====================================

* [fix integer values in buildKey](https://github.com/E96/yii2-redis/commit/da6ed85ed15bd33b4137083403c027a15b9fc03d)
* [getting long string failure](https://github.com/E96/yii2-redis/commit/fdaf09f4d191c7bf29d5e75432385611b90759af)
* [min and max number comparison](https://github.com/E96/yii2-redis/commit/c1e0f6e1c03007a1133ce7916a4683a242e3515f)
* [a bit more info in traces](https://github.com/E96/yii2-redis/commit/fe8df386a0dadbbc192f1be48c2e4dd899c12b98)
* [profiles in debugger DB panel](https://github.com/E96/yii2-redis/commit/05d8d208b25d57b41cb78e04689ccca5be33b473)
* [convert inCondition for one key](https://github.com/E96/yii2-redis/commit/36d6a145acf109d86eb2aa7918f64bc91c935ebb)
* [correct detection modified values](https://github.com/E96/yii2-redis/commit/e4040f0f5c91e291b4b014661d3e85731426f647)

original README below:


Redis Cache, Session and ActiveRecord for Yii 2
===============================================

This extension provides the [redis](http://redis.io/) key-value store support for the Yii2 framework.
It includes a `Cache` and `Session` storage handler and implements the `ActiveRecord` pattern that allows
you to store active records in redis.

This repository is a git submodule of <https://github.com/yiisoft/yii2>.
Please submit issue reports and pull requests to the main repository.
For license information check the [LICENSE](LICENSE.md)-file.

Requirements
------------

At least redis version 2.6.12 is required for all components to work properly.

Installation
------------

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

Using the Cache component
-------------------------

To use the `Cache` component, in addition to configuring the connection as described above,
you also have to configure the `cache` component to be `yii\redis\Cache`:

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

If you only use the redis cache, you can also configure the parameters of the connection within the
cache component (no connection application component needs to be configured in this case):

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

Using the Session component
---------------------------

To use the `Session` component, in addition to configuring the connection as described above,
you also have to configure the `session` component to be `yii\redis\Session`:

```php
return [
    //....
    'components' => [
        // ...
        'session' => [
            'class' => 'yii\redis\Session',
        ],
    ]
];
```

If you only use the redis session, you can also configure the parameters of the connection within the
cache component (no connection application component needs to be configured in this case):

```php
return [
    //....
    'components' => [
        // ...
        'session' => [
            'class' => 'yii\redis\Session',
            'redis' => [
                'hostname' => 'localhost',
                'port' => 6379,
                'database' => 0,
            ],
        ],
    ]
];
```


Using the redis ActiveRecord
----------------------------

For general information on how to use yii's ActiveRecord please refer to the [guide](https://github.com/yiisoft/yii2/blob/master/docs/guide/active-record.md).

For defining a redis ActiveRecord class your record class needs to extend from [[yii\redis\ActiveRecord]] and
implement at least the `attributes()` method to define the attributes of the record.
A primary key can be defined via [[yii\redis\ActiveRecord::primaryKey()]] which defaults to `id` if not specified.
The primaryKey needs to be part of the attributes so make sure you have an `id` attribute defined if you do
not specify your own primary key.

The following is an example model called `Customer`:

```php
class Customer extends \yii\redis\ActiveRecord
{
    /**
     * @return array the list of attributes for this record
     */
    public function attributes()
    {
        return ['id', 'name', 'address', 'registration_date'];
    }

    /**
     * @return ActiveQuery defines a relation to the Order record (can be in other database, e.g. elasticsearch or sql)
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
    }

    /**
     * Defines a scope that modifies the `$query` to return only active(status = 1) customers
     */
    public static function active($query)
    {
        $query->andWhere(['status' => 1]);
    }
}
```

The general usage of redis ActiveRecord is very similar to the database ActiveRecord as described in the
[guide](https://github.com/yiisoft/yii2/blob/master/docs/guide/active-record.md).
It supports the same interface and features except the following limitations:

- As redis does not support SQL the query API is limited to the following methods:
  `where()`, `limit()`, `offset()`, `orderBy()` and `indexBy()`.
  (orderBy() is not yet implemented: [#1305](https://github.com/yiisoft/yii2/issues/1305))
- `via`-relations can not be defined via a table as there are not tables in redis. You can only define relations via other records.

It is also possible to define relations from redis ActiveRecords to normal ActiveRecord classes and vice versa.

Usage example:

```php
$customer = new Customer();
$customer->attributes = ['name' => 'test'];
$customer->save();
echo $customer->id; // id will automatically be incremented if not set explicitly

$customer = Customer::find()->where(['name' => 'test'])->one(); // find by query
$customer = Customer::find()->active()->all(); // find all by query (using the `active` scope)
```
