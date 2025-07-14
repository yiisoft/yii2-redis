Yii 2 Redis キャッシュ、セッションおよびアクティブレコード Predis
===============================================
## アプリケーションを構成する

このエクステンションを使用するためには、アプリケーション構成情報で [[yii\redis\predis\PredisConnection]] クラスを構成する必要があります。

> Warning: yii\redis\predis\PredisConnection クラスは redis-cluster 接続をサポートしますが、*cache*、*session*、*ActiveRecord*、*mutex* コンポーネント インタフェースのサポートは提供しません。

### standalone
```php
return [
    //....
    'components' => [
        'redis' => [
            'class' => 'yii\redis\predis\PredisConnection',
            'parameters' => 'tcp://redis:6379',
            'options' => [
                'parameters' => [
                    'password' => 'secret', // Or NULL
                    'database' => 0,
                    'persistent' => true,
                    'async_connect' => true,
                    'read_write_timeout' => 0.1,
                ],
            ],
        ],
    ]
];
```
### sentinel
```php
return [
    //....
    'components' => [
        'redis' => [
            'class' => 'yii\redis\predis\PredisConnection',
            'parameters' => [
                'tcp://redis-node-1:26379',
                'tcp://redis-node-2:26379',
                'tcp://redis-node-3:26379',
            ],
            'options' => [
                'parameters' => [
                    'password' => 'secret', // Or NULL
                    'database' => 0,
                    'persistent' => true,
                    'async_connect' => true,
                    'read_write_timeout' => 0.1,
                ],
            ],
        ],
    ]
];
```

> 接続構成とオプションの詳細については、<a href="https://github.com/predis/predis">predis</a> のドキュメントを参照してください。

これで、`redis` アプリケーション・コンポーネントによって、redis ストレージに対する基本的なアクセスが提供されるようになります。

```php
Yii::$app->redis->set('mykey', 'some value');
echo Yii::$app->redis->get('mykey');
```

追加のトピック
-----------------

* [predisでキャッシュコンポーネントを使用する](topics-predis-cache.md)
* [Predisでセッションコンポーネントを使用する](topics-predis-session.md)

