# Upgrading Instructions for Yii 2.0 Redis Extension

This file contains the upgrade notes for Yii 2.0 Redis Extension. These notes highlight changes that
could break your application when you upgrade extension from one version to another.

Upgrading in general is as simple as updating your dependency in your composer.json and
running `composer update`. In a big application however there may be more things to consider,
which are explained in the following.

> Note: The following upgrading instructions are cumulative. That is,
if you want to upgrade from version A to version C and there is
version B between A and C, you need to follow the instructions
for both A and B.

Upgrade from 2.0.10
------------------
yii2-redis now provides a better support for Redis in cluster mode.

If you're using redis in cluster mode and want to use `MGET` and `MSET` effectively, you will need to supply a
[hash tag](https://redis.io/topics/cluster-spec#keys-hash-tags) to allocate cache keys to the same hash slot.
```
\Yii::$app->cache->multiSet([
    'posts{user1}' => 123,
    'settings{user1}' => [
        'showNickname' => false,
        'sortBy' => 'created_at',
    ],
    'unreadMessages{user1}' => 5,
]);
```

You might also want to force cluster mode for every request by setting `'forceClusterMode' => true` in your Redis component config. Otherwise an implicit check that executes Redis command `'CLUSTER INFO'` will be made on each request.

If you do not intend on using Redis in cluster mode, it is advisable to set `'forceClusterMode' => false` to avoid additional overhead and possible troubles when extending `yii\redis\Connection::executeCommand`, because executing `'CLUSTER INFO'` will return an error on a single-node Redis.

Upgrade from 2.0.11
-------------------
`zrangebyscore` was changed to:

```php
zrangebyscore($key, $min, $max, ...$options)
```

Usage:

- `zrangebyscore($key, $min, $max)`
- `zrangebyscore($key, $min, $max, 'WITHSCORES')`
- `zrangebyscore($key, $min, $max, 'LIMIT', $offset, $count)`
- `zrangebyscore($key, $min, $max, 'WITHSCORES', 'LIMIT', $offset, $count)`
