To'g'ridan-to'g'ri buyruqlar orqali foydalanish
=======================

Redis'da to'g'ridan-to'g'ri ulanishdan foydalanishda, juda ko'p buyruqlar mavjud. 
Ilovani [O'rnatish](installation.md)da ko'rsatilganidek sozlaganingizdan so'ng, quyidagi kabi ulanishga erishish mumkin
```php
$redis = Yii::$app->redis;
```

Sozlab bo'lganingizdan so'ng, to'g'ridan to'g'ri buyruq berish uchun executeCommand metodi asosiy hisoblanadi.

```php
$result = $redis->executeCommand('hmset', ['test_collection', 'key1', 'val1', 'key2', 'val2']);
```

Qo'llab quvvatlanadigan barcha buyruqlarni quyidagicha bajarish ham mumkin:

```php
$result = $redis->hmset('test_collection', 'key1', 'val1', 'key2', 'val2');
```

Redis'ning mavjud buyruqlari haqida <http://redis.io/commands> sahifasida o'rganib chiqishingiz mumkin.
