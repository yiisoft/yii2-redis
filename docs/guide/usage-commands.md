Using commands directly
=======================

Redis has many useful commands, which can be used directly from a connection object. After configuring the component in your application (as shown in [installation](installation.md)), you can get a reference to it like this:

```php
$redis = Yii::$app->redis;
```

Run arbitrary commands using the `executeCommand` method:

```php
$result = $redis->executeCommand('hmset', ['test_collection', 'key1', 'val1', 'key2', 'val2']);
```

There are shortcuts available for many common commands. Instead of the above, you can explicitly call the `hmset()` method:

```php
$result = $redis->hmset('test_collection', 'key1', 'val1', 'key2', 'val2');
```

For a list of commands and their parameters available in your version of Redis, see <https://redis.io/commands>.
You may need to use the raw `executeCommand()` method for commands added after the minimum required version (2.6.12).
