Использование ActiveRecord
======================
Для получения общей информации о том, как использовать Yii ActiveRecord, обратитесь к
[руководству](https://github.com/yiisoft/yii2/blob/master/docs/guide/db-active-record.md).
Для определения redis ActiveRecord класс записи должен расширяться от класса [[yii\redis\ActiveRecord]] и реализовывать хотябы метод `attributes()` для определения атрибутов записи.
Первичный ключ может быть определен с помощью [[yii\redis\ActiveRecord::primaryKey()]] который по умолчанию имеет значение `id` если не указано.
Первичный ключ должен быть частью атрибутов, поэтому убедитесь, что атрибут `id` определен, если вы не указали свой собственный первичный ключ.

Ниже приведен пример модели `Customer`:

```php
class Customer extends \yii\redis\ActiveRecord
{
    /**
     * @return array the list of attributes for this record
     */
    public function attributes()
    {
        return ['id', 'name', 'address', 'registration_date'];
    }

    /**
     * @return ActiveQuery defines a relation to the Order record (can be in other database, e.g. elasticsearch or sql)
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
    }

    /**
     * Defines a scope that modifies the `$query` to return only active(status = 1) customers
     */
    public static function active($query)
    {
        $query->andWhere(['status' => 1]);
    }
}
```

Общее использование redis ActiveRecord очень похоже на БД ActiveRecord как описано в [руководстве](https://github.com/yiisoft/yii2/blob/master/docs/guide/db-active-record.md).
Он поддерживает тот же интерфейс и функции, за исключением следующих ограничений:

- Поскольку redis не поддерживает SQL, API запросов ограничен следующими методами:
  `where()`, `limit()`, `offset()`, `orderBy()` и `indexBy()`.
  (orderBy() еще не реализовано: [#1305](https://github.com/yiisoft/yii2/issues/1305))
- `via`-отношения не могут быть определены через таблицу, поскольку в redis нет таблиц. Вы можете определять отношения только через другие записи.

Также можно определить отношения от redis ActiveRecords до обычных классов ActiveRecord и наоборот.

Например:

```php
$customer = new Customer();
$customer->attributes = ['name' => 'test'];
$customer->save();
echo $customer->id; // id will automatically be incremented if not set explicitly

$customer = Customer::find()->where(['name' => 'test'])->one(); // find by query
$customer = Customer::find()->active()->all(); // find all by query (using the `active` scope)
```
