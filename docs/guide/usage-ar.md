Using the ActiveRecord
======================

For general information on how to use yii's ActiveRecord please refer to the [guide](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record).

For defining a redis ActiveRecord class your record class needs to extend from [[yii\redis\ActiveRecord]] and
implement at least the `attributes()` method to define the attributes of the record.
A primary key can be defined via [[yii\redis\ActiveRecord::primaryKey()]] which defaults to `id` if not specified.
The primaryKey needs to be part of the attributes so make sure you have an `id` attribute defined if you do
not specify your own primary key.

The following is an example model called `Customer`:

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

The general usage of redis ActiveRecord is very similar to the database ActiveRecord as described in the
[guide](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record).
It supports the same interface and features except the following limitations:

- As redis does not support SQL the query API is limited to the following methods:
  `where()`, `limit()`, `offset()`, `orderBy()` and `indexBy()`.
- `via`-relations can not be defined via a table as there are not tables in redis. You can only define relations via other records.

It is also possible to define relations from redis ActiveRecords to normal ActiveRecord classes and vice versa.

Usage example:

```php
$customer = new Customer();
$customer->attributes = ['name' => 'test'];
$customer->save();
echo $customer->id; // id will automatically be incremented if not set explicitly

$customer = Customer::find()->where(['name' => 'test'])->one(); // find by query
$customer = Customer::find()->active()->all(); // find all by query (using the `active` scope)
```
