Predis para Redis Cache, Sessão e ActiveRecord para Yii 2
===============================================
## Configurando a aplicação

Para usar essa extensão, você precisa parametrizar a classe [[yii\redis\predis\PredisConnection]] na configuração da aplicação:

> Warning: A classe yii\redis\predis\PredisConnection suporta conexão redis-cluster, mas não fornece suporte para as interfaces de componentes *cache*, *session*, *ActiveRecord*, *mutex*.

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

> Mais informações sobre configuração e opções de conexão podem ser encontradas na documentação do <a href="https://github.com/predis/predis">predis</a>.

Isto fornece o acesso básico ao armazenamento de redis através do componente de aplicação `redis`:

```php
Yii::$app->redis->set('mykey', 'some value');
echo Yii::$app->redis->get('mykey');
```

Additional topics
-----------------

* [Usando o componente Cache com predis](topics-predis-cache.md)
* [Usando o componente Session com predis](topics-predis-session.md)

