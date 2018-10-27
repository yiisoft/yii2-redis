Usando o componente Cache
=========================

Para usar o componente `Cache`, além de configurar a conexão conforme descrito na seção [Instalação](installation.md),
você também tem que configurar o componente `cache` para ser [[yii\redis\Cache]]:

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

Se você usa apenas o cache de redis (ou seja, não está usando seu ActiveRecord ou Session), você também pode configurar os parâmetros da conexão dentro do componente de cache (nenhum componente de aplicativo de conexão precisa ser configurado neste caso):

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

O cache fornece todos os métodos do [[yii\caching\CacheInterface]]. Se você quiser acessar os métodos específicos do redis que não são incluído na interface, você pode usá-los via [[yii\redis\Cache::$redis]], que é uma instância de [[yii\redis\Connection]]:

```php
Yii::$app->cache->redis->hset('mykey', 'somefield', 'somevalue');
Yii::$app->cache->redis->hget('mykey', 'somefield');
...
```

Veja [[yii\redis\Connection]] para a lista completa de métodos disponíveis.
