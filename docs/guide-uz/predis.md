Redis keshi, sessiya va ActiveRecord uchun Predis
===============================================
## Sozlash

Kengaytmadan foydalanish uchun Yii2 sozlamalaridan `[[yii\redis\predis\PredisConnection]]` sinfini quyidagicha sozlashingiz kerak

> Warning: Yii\redis\predis\PredisConnection klassi redis-klaster ulanishini qo‘llab-quvvatlaydi, lekin *kesh*, *sessiya*, *ActiveRecord*, *mutex* komponent interfeyslarini qo‘llab-quvvatlamaydi.

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

> Ulanish konfiguratsiyasi va opsiyalari haqida batafsil ma’lumotni <a href="https://github.com/predis/predis">predis</a> hujjatlarida topishingiz mumkin.

Quyidagi kodlar orqali redis'ga ma'lumot kiritish va o'qishning eng oddiy holatini ko'rish mumkin

```php
Yii::$app->redis->set('mykey', 'some value');
echo Yii::$app->redis->get('mykey');
```

Qo'shimcha
-----------------

* [Kesh komponentidan predis bilan foydalanish](topics-predis-cache.md)
* [Session komponentidan predis bilan foydalanish](topics-predis-session.md)

