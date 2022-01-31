Sessiya uchun foydalanish
===========================

Redis'dan sessiyada foydalanish uchun [O'rnatish](installation.md) bo'limida tavsiflanganidek sozlashdan tashqari,
[[yii\redis\Session]] sinfi ham sozlashingiz kerak:

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

Agar siz faqat redis sessiyaidan foydalansangiz (ya'ni, ActiveRecord yoki Kesh uchun foydalanmasangiz),
sessiya komponentini o'zida ulanish sozlamalarini ham kiritishingiz mumkin
(bu holda [O'rnatish](installation.md) bo'limidagi sozlashni bajarish shart emas):

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

Boshqa metodlarni ko'rish uchun `[[yii\redis\Session]]` sinfiga qarang.
