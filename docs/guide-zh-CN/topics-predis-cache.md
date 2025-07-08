使用 Predis 的 Cache 组件
=========================

为了使用 `Cache` 组件，如 [predis](predis.md) 章节中所描述的，除了配置连接，
你也需要配置 [[yii\redis\Cache]] 中的 `cache` 组件：

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

如果你只使用 redis 缓存（即，不使用它的活动记录或者会话），您还可以配置缓存组件内的
连接参数（在这种情况下，不需要配置连接应用程序的组件）：

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
