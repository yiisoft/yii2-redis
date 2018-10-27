Instalação
============

## Requisitos

A versão mínima do Redis necessária para todos os componentes funcionarem corretamente é 2.6.12

## Instalando através do composer

A maneira recomendada para instalar esta extensão é através do [composer](http://getcomposer.org/download/).

Então rode

```
php composer.phar require --prefer-dist yiisoft/yii2-redis
```

Ou Adicione

```json
"yiisoft/yii2-redis": "~2.0.0"
```

na sessão de requirimentos do seu arquivo composer.json.

## Configurando a aplicação

Para usar essa extensão, você precisa parametrizar a classe [[yii\redis\Connection|Connection]] na configuração da aplicação:

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

Isto fornece o acesso básico ao armazenamento de redis através do componente de aplicação `redis`:
 
```php
Yii::$app->redis->set('mykey', 'some value');
echo Yii::$app->redis->get('mykey');
```

Veja [[yii\redis\Connection]] para a lista completa de métodos disponíveis.