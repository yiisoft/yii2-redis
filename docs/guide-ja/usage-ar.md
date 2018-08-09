アクティブレコードを使用する
============================

Yii のアクティブレコードの使用方法に関する一般的な情報については、[ガイド](https://www.yiiframework.com/doc/guide/2.0/ja/db-active-record) を参照してください。

redis の アクティブレコード・クラスを定義するためには、[[yii\redis\ActiveRecord]] から拡張して、最低限、
レコードの属性を定義する `attributes()` メソッドを実装する必要があります。
プライマリ・キーは [[yii\redis\ActiveRecord::primaryKey()]] によって定義することが出来ますが、指定されない場合のデフォルト値は `id` となります。
この primaryKey は属性の中に含まれていなければなりませんので、独自のプライマリ・キーを指定しない場合は、
`id` という属性を定義することを忘れないようにしなければなりません。

下記は `Customer` と呼ばれるモデルの例です。

```php
class Customer extends \yii\redis\ActiveRecord
{
    /**
     * @return array このレコードの属性のリスト
     */
    public function attributes()
    {
        return ['id', 'name', 'address', 'registration_date'];
    }

    /**
     * @return ActiveQuery Order レコードに対するリレーションを定義 (Order は、他のデータベース、例えば elasticsearch や sql でも構わない)
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
     * アクティブ (status = 1) である顧客だけを返すように `$query` を修正するスコープを定義
     */
    public function active()
    {
        return $this->andWhere(['status' => 1]);
    }
}
```

redis のアクティブレコードの一般的な使用方法は、[ガイド](https://www.yiiframework.com/doc/guide/2.0/ja/db-active-record)
で説明されているデータベースのアクティブレコードの場合と非常によく似ています。
次の制限を除けば、同じインタフェイスと機能をサポートしています。

- redis は SQL をサポートしていないので、クエリの API は次のメソッドに限定されます。
  すなわち、`where()`、`limit()`、`offset()`、`orderBy()` および `indexBy()`。
- redis にはテーブルがないため、`via` リレーションはテーブルによって定義することはできません。他のレコードを通じてリレーションを定義することだけが出来ます。

redis のアクティブレコードから通常のアクティブレコードへのリレーションを定義することも、また、その逆も、可能です。

使用例:

```php
$customer = new Customer();
$customer->attributes = ['name' => 'test'];
$customer->save();
echo $customer->id; // id は明示的にセットされない場合は自動的にインクリメントされる

$customer = Customer::find()->where(['name' => 'test'])->one(); // クエリによって検索
$customer = Customer::find()->active()->all(); // クエリ (`active` スコープを使用) によって全て検索
```
