<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\extensions\predis\sentinel\data\ar;

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
