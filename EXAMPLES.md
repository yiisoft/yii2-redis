Внимаение!!!
===============================================
Этот форк несовместим с существующим пулом ключей ActiveRedis и будет вызывать ошибку при простом замещении yii\redis\ActiveRecord
Разработчики yii использовали RPUSH для сохранения пула ключей, в этой ветке используется SADD который не совместим на уровне движка redis.
Поэтому для использования этой ветки требуется пересоздать пул ключей или конвертировать его с помощью команды
```bash
redis-cli -n 0 LRANGE 'rd_user 0 -1|awk '{print $1}'| xargs redis-cli -n 0 SADD rd_user_temp
```
то же для сокета
```bash
redis-cli -s /var/run/redis/redis.sock -n 0 LRANGE 'rd_user 0 -1|awk '{print $1}'| xargs redis-cli -s /var/run/redis/redis.sock -n 0 SADD rd_user_temp
```
и затем переименовать получившийся ключ, удалив старый

Примеры использования
---------------------

Класс ActiveRecord
```php
use yii\redis\ActiveRecord;

class rdUser extends ActiveRecord {
    public function attributes() {
        return ['id', 'name', ];
    }
}
```

Сохранение записи
```php
$user = new rdUser;
$user->id   = 1;
$user->name = 'user';
$user->save();
```

Причем если сделать еще раз
```php
$user = new rdUser;
$user->id   = 1;
$user->name = 'change user';
$user->save();
```
то в redis сохранится только один ключ rd_user:a:1 и count() будет правильно показывать значение 1


Сохранение истекающих записей
```php
$user = new rdUser;
$user->id   = 1;
$user->name = 'change user';
$user->expire(10);
$user->save();
```
Создаст ключ истекающий через 10 секунд
Поиск таких записей отличается от обычного find()
```php
$records = rdUser::find()->expire(true)->all();
```
вернет ТОЛЬКО истекающие записи

```php
$records = rdUser::find()->withExpiring(true)->all();
```
вернет и истекающие и обычные записи
Методы expire и withExpiring конфликтующие, одновременное использование обоих методов приведет к использованию только withExpiring

Сохранение геоданных
```php
$user = new rdUser;
$user->id   = 1;
$user->name = 'change user';
$user->geo(['lat'=>50,'lon'=>40]);
$user->save();
```
Поиск таких записей так же отличается от обычного find()
```php
$records = rdUser::find()->georadius(['lat'=>50,'lon'=>40, 'radius'=>'100 m'])->all();
```

georecords можно комбинировать с expire
```php
$user = new rdUser;
$user->id   = 1;
$user->name = 'change user';
$user->expire(10);
$user->geo(['lat'=>50,'lon'=>40]);
$user->save();
```

в поиске нужно указать и georadius и expire
```php
$records = rdUser::find()->georadius(['lat'=>50,'lon'=>40, 'radius'=>'100 m'])->expire(true)->all();
```

разумеется можно использовать withExpiring для поиска истекающих georecords и обычных
```php
$records = rdUser::find()->georadius(['lat'=>50,'lon'=>40, 'radius'=>'100 m'])->withExpiring(true)->all();
```

Для истекающих записей можно получить оставшееся время жизни
```php
$record = rdUser::find()->expire(true)->one();
echo $record->ttl();
```
