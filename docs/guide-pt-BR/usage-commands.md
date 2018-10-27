Usando comandos diretamente
=======================

O Redis tem muitos comandos úteis que podem ser usados diretamente da conexão. Após configurar o aplicativo como
mostrado em [instalação](installation.md), a conexão pode ser obtida da seguinte forma:

```php
$redis = Yii::$app->redis;
```

Depois de feito, pode-se executar comandos. The most generic way to do it is using `executeCommand` method:

```php
$result = $redis->executeCommand('hmset', ['test_collection', 'key1', 'val1', 'key2', 'val2']);
```

Existem atalhos disponíveis para cada comando suportado, portanto, em vez do acima, ele pode ser usado da seguinte maneira:

```php
$result = $redis->hmset('test_collection', 'key1', 'val1', 'key2', 'val2');
```

Para uma lista de comandos disponíveis e seus parâmetros, consulte <http://redis.io/commands>.