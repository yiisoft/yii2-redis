<?php

namespace yiiunit\extensions\redis\data\ar;

/**
 * Order
 *
 * @property int $id
 * @property int $customer_id
 * @property int $created_at
 * @property string $total
 *
 * @property Customer $customer
 * @property Item[] $itemsIndexed
 * @property OrderItem[] $orderItems
 * @property Item[] $items
 * @property Item[] $itemsInOrder1
 * @property Item[] $itemsInOrder2
 * @property Item[] $booksWithNullFK
 * @property Item[] $itemsWithNullFK
 * @property OrderItemWithNullFK[] $orderItemsWithNullFK
 * @property Item[] $books
 */
class Order extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return ['id', 'customer_id', 'created_at', 'total'];
    }

    /**
     * @return \yii\redis\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }

    /**
     * @return \yii\redis\ActiveQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\redis\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->via('orderItems', function ($q) {
                // additional query configuration
            });
    }

    /**
     * @return \yii\redis\ActiveQuery
     */
    public function getItemsIndexed()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->via('orderItems')->indexBy('id');
    }

    /**
     * @return \yii\redis\ActiveQuery
     */
    public function getItemsWithNullFK()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->via('orderItemsWithNullFK');
    }

    /**
     * @return \yii\redis\ActiveQuery
     */
    public function getOrderItemsWithNullFK()
    {
        return $this->hasMany(OrderItemWithNullFK::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\redis\ActiveQuery
     */
    public function getItemsInOrder1()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->via('orderItems', function ($q) {
                $q->orderBy(['subtotal' => SORT_ASC]);
            })->orderBy('name');
    }

    /**
     * @return \yii\redis\ActiveQuery
     */
    public function getItemsInOrder2()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->via('orderItems', function ($q) {
                $q->orderBy(['subtotal' => SORT_DESC]);
            })->orderBy('name');
    }

    /**
     * @return \yii\redis\ActiveQuery
     */
    public function getBooks()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->via('orderItems')
            ->where(['category_id' => 1]);
    }

    /**
     * @return \yii\redis\ActiveQuery
     */
    public function getBooksWithNullFK()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->via('orderItemsWithNullFK')
            ->where(['category_id' => 1]);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->created_at = time();

            return true;
        } else {
            return false;
        }
    }
}
