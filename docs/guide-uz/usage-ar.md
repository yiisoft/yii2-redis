ActiveRecord orqali foydalanish
======================

Avvalo Yii2'da ActiveRecord'dan qanday foydalanish haqida umumiy ma'lumot olish uchun [qo'llanma](https://github.com/yiisoft/yii2/blob/master/docs/guide/db-active-record.md) ga qarang.

Redis'dan ActiveRecord orqali foydalanish uchun sizning sinfingiz [[yii\redis\ActiveRecord]] vorisi bo'lish va 
sinfingizda kamida `attributes()` metodi bo'lishi kerak.



PrimaryKey'ni `[[yii\redis\ActiveRecord::primaryKey()]]` orqali tanitish mumkin, agar berilmagan bo'lsa, `id` PrimaryKey sifatida tanitiladi.
PrimaryKey'ni ko'rsatmagan bo'lsangiz, PrimaryKey sifatida `id` olinganligini tekshiring.

Quyida `Customer` nomli namunaviy model keltirilgan:

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

    public static function find()
    {
        return new CustomerQuery(get_called_class());
    }
}

class CustomerQuery extends \yii\redis\ActiveQuery
{
    /**
     * Defines a scope that modifies the `$query` to return only active(status = 1) customers
     */
    public function active()
    {
        return $this->andWhere(['status' => 1]);
    }
}
```

Общее использование redis ActiveRecord очень похоже на БД ActiveRecord как описано в [руководстве](https://github.com/yiisoft/yii2/blob/master/docs/guide/db-active-record.md).
Он поддерживает тот же интерфейс и функции, за исключением следующих ограничений:

Redis'dan ActiveRecord'da foydalanish, ma'lumotlar bazasi bilan ishlashga juda o'xshaydi.
Faqat u quyidagi cheklovlardan tashqari bir xil interfeys va xususiyatlarni qo'llab-quvvatlaydi:

- Redis SQL'ni qo'llab-quvvatlamagani uchun quidagi metodlardan foydalanib bo'lmaydi
  `where()`, `limit()`, `offset()`, `orderBy()` va `indexBy()`.
- `via` - jadval via orqali aniqlab (bog'lanib) bo'lmaydi, chunki redisda jadvallar mavjud emas. Siz bog'lanishlarni boshqa ma'lumotlar orqali belgilashingiz mumkin.

Redis ActiveRecords dan oddiy ActiveRecord sinflariga va aksincha bog'lanishlarni aniqlashingiz mumkin.

Masalan:

```php
$customer = new Customer();
$customer->attributes = ['name' => 'test'];
$customer->save();
echo $customer->id; // id will automatically be incremented if not set explicitly

$customer = Customer::find()->where(['name' => 'test'])->one(); // find by query
$customer = Customer::find()->active()->all(); // find all by query (using the `active` scope)
```
