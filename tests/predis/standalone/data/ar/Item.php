<?php

namespace yiiunit\extensions\redis\predis\standalone\data\ar;

/**
 * Class Item
 *
 * @property int $id
 * @property string $name
 * @property int $category_id
 */
class Item extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return ['id', 'name', 'category_id'];
    }
}
