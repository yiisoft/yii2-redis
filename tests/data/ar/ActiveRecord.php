<?php

namespace yiiunit\extensions\redis\data\ar;

/**
 * ActiveRecord is ...
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveRecord extends \yii\redis\ActiveRecord
{
    /**
     * @return \yii\redis\Connection
     */
    public static $db;

    /**
     * @return \yii\redis\Connection
     */
    public static function getDb()
    {
        return self::$db;
    }
}
