安装
====

## 需求

redis 版本至少为 2.6.12，以供所有组件能够正常工作。

## 获取 Composer 包

最合适的安装方法是通过 [composer](http://getcomposer.org/download/) 安装该扩展。

运行

```
php composer.phar require --prefer-dist yiisoft/yii2-redis
```

或在你的 composer.json 文件的“require”一节添加以下代码：

```json
"yiisoft/yii2-redis": "~2.0.0"
```


## 配置应用程序

要使用该组件，你应当在你的应用程序配置中配置 Connection 类：

```php
return [
    //....
    'components' => [
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
    ]
];
```
