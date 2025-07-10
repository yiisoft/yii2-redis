Redis Cache、Session 和 ActiveRecord 的“Predis” 
===============================================
## 配置应用程序

使用此扩展时，需要在你的应用程序配置中配置 [[yii\redis\predis\PredisConnection]] 类：

> Warning: yii\redis\predis\PredisConnection 类支持 redis-cluster 连接，但是不提供对 *cache*、*session*、*​​ActiveRecord*、*mutex* 组件接口的支持。

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

> 有关连接配置和选项的更多信息，请参阅 <a href="https://github.com/predis/predis">predis</a> 文档。

这通过“redis”应用程序组件提供了对redis存储的基本访问：

```php
Yii::$app->redis->set('mykey', 'some value');
echo Yii::$app->redis->get('mykey');
```

附加主题
-----------------

* [使用 Predis 的 Cache 组件](topics-predis-cache.md)
* [使用带有 predis 的 Session 组件](topics-predis-session.md)

