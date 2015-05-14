<?php

namespace yiiunit\extensions\redis\data\ar;

/**
 * Class TestModel
 * @package yiiunit\extensions\redis\data\ar
 */
class TestModel extends ActiveRecord
{
    /**
     * @var
     */
    public $changedAttributes;

    /**
     * @return array
     */
    public function attributes()
    {
        return ['id', 'name', 'isActive', 'items'];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['id', 'integer'],
            ['name', 'string'],
            ['isActive', 'boolean'],
            ['items', 'safe'],
        ];
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->changedAttributes = $changedAttributes;
    }
}