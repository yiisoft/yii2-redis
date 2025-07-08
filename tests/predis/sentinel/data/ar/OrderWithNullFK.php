<?php

namespace yiiunit\extensions\redis\predis\sentinel\data\ar;

/**
 * Class Order
 *
 * @property integer $id
 * @property integer $customer_id
 * @property integer $created_at
 * @property string $total
 */
class OrderWithNullFK extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function primaryKey(): array
    {
        return ['id'];
    }

    /**
     * @inheritdoc
     */
    public function attributes(): array
    {
        return ['id', 'customer_id', 'created_at', 'total'];
    }
}
