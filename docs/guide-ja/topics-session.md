セッションコンポーネントを使用する
==================================

`Session` コンポーネントを使用するためには、[インストール](installation.md) の節で説明した接続の構成に加えて、`session` コンポーネントを `yii\redis\Session` として構成する必要があります。

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

redis をセッションとしてのみ使用する場合、すなわち、redis のアクティブレコードやキャッシュは使わない場合は、接続のパラメータをセッションコンポーネントの中で構成しても構いません
(この場合、接続のアプリケーションコンポーネントを構成する必要はありません)。

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
