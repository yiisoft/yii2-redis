<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\extensions\predis\standalone\data\ar;

/**
 * Class OrderItem
 *
 * @property integer $order_id
 * @property integer $item_id
 * @property integer $quantity
 * @property string $subtotal
 */
class OrderItemWithNullFK extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['order_id', 'item_id'];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return ['order_id', 'item_id', 'quantity', 'subtotal'];
    }
}
