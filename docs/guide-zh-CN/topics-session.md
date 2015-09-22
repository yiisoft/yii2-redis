会话组件的使用
===========================

为了使用 `Session` 组件，如 [安装](installation.md) 章节中所描述的，除了配置连接，
你也需要配置 `yii\redis\Session` 中的 `session` 组件：

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

如果你只使用 redis 会话（即，不使用它的活动记录或者缓存），您还可以配置会话组件内的
连接参数（在这种情况下，不需要配置连接应用程序的组件）：

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
