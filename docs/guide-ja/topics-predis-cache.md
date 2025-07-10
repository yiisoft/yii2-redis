キャッシュ・コンポーネントを使用する Predis
=========================

`Cache` コンポーネントを使用するためには、[predis](predis.md) の節で説明した接続の構成に加えて、
`cache` コンポーネントを [[yii\redis\Cache]] として構成する必要があります。

```php
return [
    //....
    'components' => [
        // ...
        'redis' => [
            'class' => 'yii\redis\predis\PredisConnection',
            'parameters' => 'tcp://redis:6379',
            // ...
        ],
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
                'class' => 'yii\redis\predis\PredisConnection',
                'parameters' => 'tcp://redis:6379',
                // ...
            ],
        ],
    ]
];
```

このキャッシュは [[yii\caching\CacheInterface]] の全てのメソッドを提供します。インタフェイスに含まれていない redis 固有のメソッドにアクセスしたい場合は、
[[yii\redis\ConnectionInterface]] のインスタンスである [[yii\redis\Cache::$redis]] を通じてアクセスすることが出来ます。

```php
Yii::$app->cache->redis->hset('mykey', 'somefield', 'somevalue');
Yii::$app->cache->redis->hget('mykey', 'somefield');
...
```

利用可能なメソッドの一覧は [[yii\redis\predis\PredisConnection]] を参照して下さい。
