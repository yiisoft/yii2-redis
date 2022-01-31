Kesh uchun foydalanish
=========================

Redis'dan keshda foydalanish uchun [O'rnatish](installation.md) bo'limida tavsiflanganidek sozlashdan tashqari, 
[[yii\redis\Cache]] sinfi ham sozlashingiz kerak:

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

Agar siz faqat redis keshidan foydalansangiz (ya'ni, ActiveRecord yoki Sessiya uchun foydalanmasangiz), 
kesh komponentini o'zida ulanish sozlamalarini ham kiritishingiz mumkin 
(bu holda [O'rnatish](installation.md) bo'limidagi sozlashni bajarish shart emas):

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

Kesh `[[yii\caching\CacheInterface]]` interfeysidagi barcha metodlardan foydalanish imkonini beradi.
Interfeysga kiritilmagan Redis maxsus metodlaridan foydalanmoqchi bo'lsangiz, [[yii\redis\Cache::$redis]] orqali foydalanishingiz mumkin, 
bu [[yii\redis\Connection]] holatidagi namuna:

```php
Yii::$app->cache->redis->hset('mykey', 'somefield', 'somevalue');
Yii::$app->cache->redis->hget('mykey', 'somefield');
...
```

Boshqa metodlarni ko'rish uchun `[[yii\redis\Cache]]` sinfiga qarang.
