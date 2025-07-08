セッション・コンポーネントを使用する Predis
===========================

`Session` コンポーネントを使用するためには、[predis](predis.md) の節で説明した接続の構成に加えて、
`session` コンポーネントを [[yii\redis\Session]] として構成する必要があります。

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
        'session' => [
            'class' => 'yii\redis\Session',
        ],
    ]
];
```

redis をセッションとしてのみ使用する場合、すなわち、redis のアクティブレコードやキャッシュは使わない場合は、接続のパラメータをセッション・コンポーネントの中で構成しても構いません
(この場合、接続のアプリケーション・コンポーネントを構成する必要はありません)。

```php
return [
    //....
    'components' => [
        // ...
        'session' => [
            'class' => 'yii\redis\Session',
            'redis' => [
                'class' => 'yii\redis\predis\PredisConnection',
                'parameters' => 'tcp://redis:6379',
                // ...
            ],
        ],
    ]
];
```
