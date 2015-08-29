使用活动记录
============

要了解如何使用 Yii 的活动记录，请参考 [导览](https://github.com/yiisoft/yii2/blob/master/docs/guide/active-record.md).

要定义一个 redis 活动记录类，则您的记录类需要继承自 [[yii\redis\ActiveRecord]] 并且
至少要实现 `attributes()` 方法，以定义记录的属性。
可以通过覆盖 [[yii\redis\ActiveRecord::primaryKey()]] 定义主键，若不定义，则默认为 `id`。
主键必须是属性之一，所以，若您没有定义主键时，则须确保属性列表中定义了 `id` 属性。

以下为示例模型 `Customer`：

```php
class Customer extends \yii\redis\ActiveRecord
{
    /**
     * @return array 该记录的属性列表
     */
    public function attributes()
    {
        return ['id', 'name', 'address', 'registration_date'];
    }

    /**
     * @return ActiveQuery 定义一个关联 Order 记录的关系（可以是其它数据库，如 ElasticSearch 或 关系型数据库）
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

活动记录的一般用法与
[导览](https://github.com/yiisoft/yii2/blob/master/docs/guide/active-record.md)
中描述的数据库的活动记录用法类似。
它们支持相同的接口和特性，但有以下限制：

- 由于 redis 不支持 SQL，查询 API 限于以下若干方法：
  `where()`、`limit()`、`offset()`、`orderBy()` 和 `indexBy()`。
  (orderBy() 还未实现：[#1305](https://github.com/yiisoft/yii2/issues/1305))
- `via`- 关系不能定义为不在 redis 中的表。您只能定义其它记录。

您也可以定义从 redis 活动记录到常规数据库活动记录类的关系，反之亦然。

用法举例：

```php
$customer = new Customer();
$customer->attributes = ['name' => 'test'];
$customer->save();
echo $customer->id; // 如果不严格设置 id 值，则该值会自动递增。

$customer = Customer::find()->where(['name' => 'test'])->one(); // find by query
$customer = Customer::find()->active()->all(); // find all by query (using the `active` scope)
```