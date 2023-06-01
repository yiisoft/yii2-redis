<?php

namespace yiiunit\extensions\redis\data\ar;

use yii\redis\ActiveQuery;

/**
 * CustomerBinaryQuery
 */
class CustomerBinaryQuery extends ActiveQuery
{
    /**
     * @return $this
     */
    public function active()
    {
        $this->andWhere(['status' => 1]);

        return $this;
    }
}
