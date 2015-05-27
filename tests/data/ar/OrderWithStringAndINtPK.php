<?php

namespace yiiunit\extensions\redis\data\ar;

/**
 * Class Order
 *
 * @property integer $id
 * @property string $type
 * @property integer $customer_id
 * @property integer $created_at
 * @property string $total
 */
class OrderWithStringAndIntPk extends ActiveRecord
{
    public static function primaryKey()
    {
        return ['type', 'id'];
    }

    public function attributes()
    {
        return ['id', 'type', 'customer_id', 'created_at', 'total'];
    }
}
