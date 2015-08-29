使用缓存组件
============

要使用 `缓存` 组件，您不仅要按照[安装](installation.md) 一节的描述配置连接，还要
将 `缓存` 组件配置为 `yii\redis\Cache`：

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

若您仅使用 redis 缓存（比如不使用其活动记录或会话组件），你也可以在缓存组件配置中
附加连接参数（这种情况下不需要单独配置连接应用程序组件）：

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
