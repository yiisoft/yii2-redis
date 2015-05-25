コマンドを直接に使用する
========================

Redis は、接続から直接に使用することが出来る数多くの有用なコマンドを持っています。
[インストール](installation.md) で示されたようにアプリケーションを構成した後は、redis の接続を次のようにして取得することが出来ます。

```php
$redis = Yii::$app->redis;
```

このようにした後、コマンドを実行することが出来ます。
最も汎用的な方法は、`executeCommand` メソッドを使用することです。

```php
$result = $redis->executeCommand('hmset', ['test_collection', 'key1', 'val1', 'key2', 'val2']);
```

サポートされているコマンドのそれぞれに対してショートカットが利用できますので、上記の代りに次のようにすることも出来ます。

```php
$result = $redis->hmset(['test_collection', 'key1', 'val1', 'key2', 'val2']);
```

利用できるコマンドとそのパラメータについては、[http://redis.io/commands](http://redis.io/commands) のリストを参照してください。
