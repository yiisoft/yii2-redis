直接使用命令
============

Redis 拥有诸多非常有用的命令，而且这些命令可以直接从连接组件中执行。按照 [安装](installation.md) 中的
描述配置好应用程序后，可以按以下方法获得`连接`：

```php
$redis = Yii::$app->redis;
```

获得后便可以执行命令。最常用的方法是使用其 `executeCommand` 方法：

```php
$result = $redis->executeCommand('hmset', ['test_collection', 'key1', 'val1', 'key2', 'val2']);
```

某些命令有其快捷方式，而不必使用以上方式，示例如下：

```php
$result = $redis->hmset(['test_collection', 'key1', 'val1', 'key2', 'val2']);
```

快捷方式命令列表和它们的参数详见 [http://redis.io/commands](http://redis.io/commands)。