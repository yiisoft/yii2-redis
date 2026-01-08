<?php

namespace yiiunit\extensions\predis\sentinel\data\ar;

use yii\redis\ActiveQuery;

/**
 * CustomerQuery
 */
class CustomerQuery extends ActiveQuery
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
