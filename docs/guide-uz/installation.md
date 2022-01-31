O'rnatish
============

## Talablar

Barcha komponentlar to'g'ri ishlashi uchun kamida [Redis](http://redis.io/) 2.6.12 versiyasi talab qilinadi.

## Composer orqali o'rnatish

[Composer](http://getcomposer.org/download/) orqali o'rnatish eng yaxshi usuldir.

Quyidagi buyruq orqali o'rnatish mumkin:

```
php composer.phar require --prefer-dist yiisoft/yii2-redis
```

yoki `composer.json` fayliga `require` bo'limiga quyidagilarni qo'shish orqali

```json
"yiisoft/yii2-redis": "~2.0.0"
```

o'rnatish mumkin.

## Sozlash

Kengaytmadan foydalanish uchun Yii2 sozlamalaridan `[[yii\redis\Connection|Connection]]` sinfini quyidagicha sozlashingiz kerak

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

Quyidagi kodlar orqali redis'ga ma'lumot kiritish va o'qishning eng oddiy holatini ko'rish mumkin
 
```php
Yii::$app->redis->set('mykey', 'some value');
echo Yii::$app->redis->get('mykey');
```

Boshqa metodlarni ko'rish uchun `[[yii\redis\Connection]]` sinfiga qarang.
