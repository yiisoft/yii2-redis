<?php

namespace yiiunit\extensions\predis\sentinel\data\ar;

use yii\redis\Predis\PredisConnection;

/**
 * ActiveRecord is ...
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveRecord extends \yii\redis\ActiveRecord
{
    /**
     * @return PredisConnection
     */
    public static $db;

    /**
     * @return PredisConnection
     */
    public static function getDb(): PredisConnection
    {
        return self::$db;
    }
}
