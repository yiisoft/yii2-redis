<?php

namespace yiiunit\extensions\redis\data\ar;

use yiiunit\extensions\redis\ActiveRecordTest;

/**
 * CustomerBinary
 *
 * @property string $guid
 * @property string $user_guid
 * @property string $email
 * @property string $name
 * @property string $address
 * @property int $status
 *
 * @method CustomerBinaryQuery findBySql($sql, $params = []) static
 */
class CustomerBinary extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    public $status2;

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return ['guid', 'user_guid', 'email', 'name', 'address', 'status'];
    }

    public static function primaryKey()
    {
        return ['guid'];
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        ActiveRecordTest::$afterSaveInsert = $insert;
        ActiveRecordTest::$afterSaveNewRecord = $this->isNewRecord;
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     * @return CustomerBinaryQuery
     */
    public static function find()
    {
        return new CustomerBinaryQuery(get_called_class());
    }
}
