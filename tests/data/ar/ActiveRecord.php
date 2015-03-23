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
    public static $db;

    public static function getDb()
    {
        return self::$db;
    }
}
