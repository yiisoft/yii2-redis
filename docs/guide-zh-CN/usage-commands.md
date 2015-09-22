直接使用命令行
=======================

Redis 有很多可以直接从连接中使用的有用的命令。在配置应用程序后，
如 [安装](installation.md) 所示，连接可以像下面这样获取：

```php
$redis = Yii::$app->redis;
```

完成之后可以执行如下命令。最通用的方法是使用 `executeCommand` 方法：

```php
$result = $redis->executeCommand('hmset', ['test_collection', 'key1', 'val1', 'key2', 'val2']);
```

每个命令都有相应的快捷方式支持，所以可以像下面这样代替以上的命令：

```php
$result = $redis->hmset(['test_collection', 'key1', 'val1', 'key2', 'val2']);
```

可用命令列表和他们的参数可参阅 [http://redis.io/commands](http://redis.io/commands)。