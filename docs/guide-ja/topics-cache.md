キャッシュコンポーネントを使用する
==================================

`Cache` コンポーネントを使用するためには、[インストール](installation.md) の節で説明した接続の構成に加えて、`cache` コンポーネントを `yii\redis\Cache` として構成する必要があります。

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

redis をキャッシュとしてのみ使用する場合、すなわち、redis のアクティブレコードやセッションを使用しない場合は、接続のパラメータをキャッシュコンポーネントの中で構成しても構いません
(この場合、接続のアプリケーションコンポーネントを構成する必要はありません)。

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
