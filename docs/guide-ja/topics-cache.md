キャッシュ・コンポーネントを使用する
====================================

`Cache` コンポーネントを使用するためには、[インストール](installation.md) の節で説明した接続の構成に加えて、
`cache` コンポーネントを [[yii\redis\Cache]] として構成する必要があります。

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

redis をキャッシュとしてのみ使用する場合、すなわち、redis のアクティブレコードやセッションを使用しない場合は、接続のパラメータをキャッシュ・コンポーネントの中で構成しても構いません
(この場合、接続のアプリケーション・コンポーネントを構成する必要はありません)。

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

このキャッシュは [[yii\caching\CacheInterface]] の全てのメソッドを提供します。インタフェイスに含まれていない redis 固有のメソッドにアクセスしたい場合は、
[[yii\redis\Connection]] のインスタンスである [[yii\redis\Cache::$redis]] を通じてアクセスすることが出来ます。

```php
Yii::$app->cache->redis->hset('mykey', 'somefield', 'somevalue');
Yii::$app->cache->redis->hget('mykey', 'somefield');
...
```

利用可能なメソッドの一覧は [[yii\redis\Connection]] を参照して下さい。
