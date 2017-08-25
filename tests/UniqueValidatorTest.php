<?php

namespace yiiunit\extensions\redis;

use yii\validators\UniqueValidator;
use yiiunit\extensions\redis\data\ar\ActiveRecord;
use yiiunit\extensions\redis\data\ar\Customer;
use yiiunit\extensions\redis\data\ar\OrderItem;

/**
 * UniqueValidatorTest tests unique validator with Redis
 */
class UniqueValidatorTest extends TestCase
{
    public function testValidationInsert()
    {
        ActiveRecord::$db = $this->getConnection(true);

        $validator = new UniqueValidator();

        $customer = new Customer();
        $customer->setAttributes(['email' => 'user1@example.com', 'name' => 'user1', 'address' => 'address1', 'status' => 1, 'profile_id' => 1], false);

        $this->assertFalse($customer->hasErrors('email'));
        $validator->validateAttribute($customer, 'email');
        $this->assertFalse($customer->hasErrors('email'));
        $customer->save(false);

        $customer = new Customer();
        $customer->setAttributes(['email' => 'user1@example.com', 'name' => 'user1', 'address' => 'address1', 'status' => 1, 'profile_id' => 1], false);

        $this->assertFalse($customer->hasErrors('email'));
        $validator->validateAttribute($customer, 'email');
        $this->assertTrue($customer->hasErrors('email'));
    }

    public function testValidationUpdate()
    {
        ActiveRecord::$db = $this->getConnection(true);

        $customer = new Customer();
        $customer->setAttributes(['email' => 'user1@example.com', 'name' => 'user1', 'address' => 'address1', 'status' => 1, 'profile_id' => 1], false);
        $customer->save(false);
        $customer = new Customer();
        $customer->setAttributes(['email' => 'user2@example.com', 'name' => 'user2', 'address' => 'address2', 'status' => 1, 'profile_id' => 2], false);
        $customer->save(false);

        $validator = new UniqueValidator();

        $customer1 = Customer::findOne(['email' => 'user1@example.com']);

        $this->assertFalse($customer1->hasErrors('email'));
        $validator->validateAttribute($customer1, 'email');
        $this->assertFalse($customer1->hasErrors('email'));

        $customer1->email = 'user2@example.com';
        $validator->validateAttribute($customer1, 'email');
        $this->assertTrue($customer1->hasErrors('email'));
    }

    public function testValidationInsertCompositePk()
    {
        ActiveRecord::$db = $this->getConnection(true);

        $validator = new UniqueValidator();
        $validator->targetAttribute = ['order_id', 'item_id'];

        $model = new OrderItem();
        $model->setAttributes(['order_id' => 1, 'item_id' => 1, 'quantity' => 5, 'subtotal' => 42], false);

        $this->assertFalse($model->hasErrors('item_id'));
        $validator->validateAttribute($model, 'item_id');
        $this->assertFalse($model->hasErrors('item_id'));
        $model->save(false);

        $model = new OrderItem();
        $model->setAttributes(['order_id' => 1, 'item_id' => 1, 'quantity' => 5, 'subtotal' => 42], false);

        $this->assertFalse($model->hasErrors('item_id'));
        $validator->validateAttribute($model, 'item_id');
        $this->assertTrue($model->hasErrors('item_id'));
    }

    public function testValidationInsertCompositePkUniqueAttribute()
    {
        ActiveRecord::$db = $this->getConnection(true);

        $validator = new UniqueValidator();

        $model = new OrderItem();
        $model->setAttributes(['order_id' => 1, 'item_id' => 1, 'quantity' => 5, 'subtotal' => 42], false);

        $this->assertFalse($model->hasErrors('quantity'));
        $validator->validateAttribute($model, 'quantity');
        $this->assertFalse($model->hasErrors('quantity'));
        $model->save(false);

        $model = new OrderItem();
        $model->setAttributes(['order_id' => 1, 'item_id' => 1, 'quantity' => 5, 'subtotal' => 42], false);

        $this->assertFalse($model->hasErrors('quantity'));
        $validator->validateAttribute($model, 'quantity');
        $this->assertTrue($model->hasErrors('quantity'));
    }

    public function testValidationUpdateCompositePk()
    {
        ActiveRecord::$db = $this->getConnection(true);

        $model = new OrderItem();
        $model->setAttributes(['order_id' => 1, 'item_id' => 1, 'quantity' => 5, 'subtotal' => 42], false);
        $model->save(false);
        $model = new OrderItem();
        $model->setAttributes(['order_id' => 1, 'item_id' => 2, 'quantity' => 5, 'subtotal' => 42], false);
        $model->save(false);

        $validator = new UniqueValidator();
        $validator->targetAttribute = ['order_id', 'item_id'];

        $model1 = OrderItem::findOne(['order_id' => 1, 'item_id' => 1]);

        $this->assertFalse($model1->hasErrors('item_id'));
        $validator->validateAttribute($model1, 'item_id');
        $this->assertFalse($model1->hasErrors('item_id'));

        $model1->item_id = 2;
        $validator->validateAttribute($model1, 'item_id');
        $this->assertTrue($model1->hasErrors('item_id'));
    }

    public function testValidationUpdateCompositePkUniqueAttribute()
    {
        ActiveRecord::$db = $this->getConnection(true);

        $model = new OrderItem();
        $model->setAttributes(['order_id' => 1, 'item_id' => 1, 'quantity' => 5, 'subtotal' => 42], false);
        $model->save(false);
        $model = new OrderItem();
        $model->setAttributes(['order_id' => 1, 'item_id' => 2, 'quantity' => 6, 'subtotal' => 42], false);
        $model->save(false);

        $validator = new UniqueValidator();

        $model1 = OrderItem::findOne(['order_id' => 1, 'item_id' => 1]);

        $this->assertFalse($model1->hasErrors('quantity'));
        $validator->validateAttribute($model1, 'quantity');
        $this->assertFalse($model1->hasErrors('quantity'));

        $model1->quantity = 6;
        $validator->validateAttribute($model1, 'quantity');
        $this->assertTrue($model1->hasErrors('quantity'));
    }

}
