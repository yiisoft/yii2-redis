Session komponentidan predis bilan foydalanish
===========================

Redis'dan sessiyada foydalanish uchun [predis](predis.md) bo'limida tavsiflanganidek sozlashdan tashqari,
[[yii\redis\Session]] sinfi ham sozlashingiz kerak:

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
        'session' => [
            'class' => 'yii\redis\Session',
        ],
    ]
];
```

Agar siz faqat redis sessiyaidan foydalansangiz (ya'ni, ActiveRecord yoki Kesh uchun foydalanmasangiz),
sessiya komponentini o'zida ulanish sozlamalarini ham kiritishingiz mumkin
(bu holda [predis](predis.md) bo'limidagi sozlashni bajarish shart emas):

```php
return [
    //....
    'components' => [
        // ...
        'session' => [
            'class' => 'yii\redis\Session',
            'redis' => [
                'class' => 'yii\redis\predis\PredisConnection',
                'parameters' => 'tcp://redis:6379',
                // ...
            ],
        ],
    ]
];
```
