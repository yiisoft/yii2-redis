<?php

namespace yiiunit\extensions\redis\predis\standalone\data\ar;

use yii\redis\predis\PredisConnection;

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
