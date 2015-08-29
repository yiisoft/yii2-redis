使用会话组件
============

要使用 `会话` 组件，您不仅要按照[安装](installation.md) 一节的描述配置连接，还要
将 `会话` 组件配置为 `yii\redis\Session`：

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

若您仅使用 redis 会话（比如不使用其活动记录或缓存组件），你也可以在会话组件配置中
附加连接参数（这种情况下不需要单独配置连接应用程序组件）：

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
