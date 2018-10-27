Usando o componente Session
===========================

Para usar o componente `Session`, além de configurar a conexão conforme descrito na seção [Instalação](installation.md),
você também tem que configurar o componente `session` para ser [[yii\redis\Session]]:

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

Se você usar somente a sessão de redis (ou seja, não usar seu ActiveRecord ou Cache), você também pode configurar os parâmetros da conexão dentro do 
componente de sessão (nenhum componente de aplicativo de conexão precisa ser configurado neste caso):

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
