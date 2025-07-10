Kesh komponentidan predis bilan foydalanish
=========================

Redis'dan keshda foydalanish uchun [predis](predis.md) bo'limida tavsiflanganidek sozlashdan tashqari,
[[yii\redis\Cache]] sinfi ham sozlashingiz kerak:

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

Agar siz faqat redis keshidan foydalansangiz (ya'ni, ActiveRecord yoki Sessiya uchun foydalanmasangiz),
kesh komponentini o'zida ulanish sozlamalarini ham kiritishingiz mumkin
(bu holda [predis](predis.md) bo'limidagi sozlashni bajarish shart emas):

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

Kesh `[[yii\caching\CacheInterface]]` interfeysidagi barcha metodlardan foydalanish imkonini beradi.
Interfeysga kiritilmagan Redis maxsus metodlaridan foydalanmoqchi bo'lsangiz, [[yii\redis\Cache::$redis]] orqali foydalanishingiz mumkin,
bu [[yii\redis\ConnectionInterface]] holatidagi namuna:

```php
Yii::$app->cache->redis->hset('mykey', 'somefield', 'somevalue');
Yii::$app->cache->redis->hget('mykey', 'somefield');
...
```

Boshqa metodlarni ko'rish uchun `[[yii\redis\predis\PredisConnection]]` sinfiga qarang.
