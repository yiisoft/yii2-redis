活动记录的使用
======================

对于如何使用 yii 的活动记录一般信息请参阅 [指南](https://github.com/yiisoft/yii2/blob/master/docs/guide/active-record.md)。

定义一个 redis 活动记录类，你的记录类需要继承自 [[yii\redis\ActiveRecord]] 并且
至少实现 `attributes()` 方法来定义记录的属性。
一个没有指定默认值，默认为 `id` 的主键可以通过 [[yii\redis\ActiveRecord::primaryKey()]] 定义。
主键是属性中必要的一部分，所以请确保你有一个 `id` 属性定义的，
如果你没有指定自己的主键。

以下是一个 `Customer` 的实例模型：

```php
class Customer extends \yii\redis\ActiveRecord
{
    /**
     * @return array 此记录的属性列表
     */
    public function attributes()
    {
        return ['id', 'name', 'address', 'registration_date'];
    }

    /**
     * @return ActiveQuery 定义一个关联到 Order 的记录（可以在其它数据库中，例如 elasticsearch 或者 sql）
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
    }

    /**
     * 定义一个修改 `$query` 的范围返回有效（status = 1）的客户。
     */
    public static function active($query)
    {
        $query->andWhere(['status' => 1]);
    }
}
```

redis 活动记录的一般用法和数据库活动记录非常相似，正如
[指南](https://github.com/yiisoft/yii2/blob/master/docs/guide/active-record.md) 中所描述的。
它支持相同的界面和功能，除了以下限制：

- redis 不支持 SQL 查询的 API 仅限于以下方法：
  `where()`，`limit()`，`offset()`，`orderBy()` 和 `indexBy()`。
  (orderBy() 尚未实现：[#1305](https://github.com/yiisoft/yii2/issues/1305))
- `via`-关系不能通过在 redis 中没有的表定义。你只能通过其他记录来定义关系。

另外，也可以定义从 redis 的活动记录关系到正常的活动记录类，反之亦然。

使用实例：

```php
$customer = new Customer();
$customer->attributes = ['name' => 'test'];
$customer->save();
echo $customer->id; // 如果没有明确设置 id 会自动递增

$customer = Customer::find()->where(['name' => 'test'])->one(); // 通过 query 查找
$customer = Customer::find()->active()->all(); // 通过 query 查找全部（使用 `active` 范围）
```
