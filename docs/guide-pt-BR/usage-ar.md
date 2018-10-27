Usando o ActiveRecord
======================

Para obter informações gerais sobre como usar o ActiveRecord do yii, consulte o [guia](https://www.yiiframework.com/doc/guide/2.0/pt-br/db-active-record).

Para definir uma classe redis ActiveRecord, sua classe de registro precisa ser estendida de [[yii\redis\ActiveRecord]] e
implementar pelo menos o método `attributes ()` para definir os atributos do registro
Uma chave primária pode ser definida via [[yii\redis\ActiveRecord::primaryKey()]] cujo valor padrão é `id` se não for especificado.
A chave primária precisa fazer parte dos atributos, então tenha certeza que você tem um atributo `id` definido se você não especificar sua própria chave primária.

A seguir um exemplo de modelo chamado `Customer`:


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

O uso geral de redis ActiveRecord é muito semelhante ao banco de dados ActiveRecord, conforme descrito no
[guia](https://www.yiiframework.com/doc/guide/2.0/pt-br/db-active-record).
Ele suporta a mesma interface e recursos, exceto as seguintes limitações:

- Com os redis não suporta SQL, a API de consulta está limitada aos seguintes métodos:
  `where()`, `limit()`, `offset()`, `orderBy()` and `indexBy()`.
- Relações não podem ser definidas através de uma tabela, pois não há tabelas em redis. Você só pode definir relações através de outros registros.

Também é possível definir relações de redis ActiveRecords para classes normais de ActiveRecord e vice-versa.

Exemplo de uso:

```php
$customer = new Customer();
$customer->attributes = ['name' => 'test'];
$customer->save();
echo $customer->id; // id will automatically be incremented if not set explicitly

$customer = Customer::find()->where(['name' => 'test'])->one(); // find by query
$customer = Customer::find()->active()->all(); // find all by query (using the `active` scope)
```
