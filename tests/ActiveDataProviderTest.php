<?php

namespace yiiunit\extensions\redis;

use yii\data\ActiveDataProvider;
use yii\redis\ActiveQuery;
use yiiunit\extensions\redis\data\ar\ActiveRecord;
use yiiunit\extensions\redis\data\ar\Item;
use yiiunit\framework\ar\ActiveRecordTestTrait;

/**
 * @group redis
 */
class ActiveDataProviderTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();

        $item = new Item();
        $item->setAttributes(['name' => 'abc', 'category_id' => 1], false);
        $item->save(false);
        $item = new Item();
        $item->setAttributes(['name' => 'def', 'category_id' => 2], false);
        $item->save(false);
    }

    public function testQuery()
    {
        $query = Item::find();
        $provider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->assertCount(2, $provider->getModels());

        $query = Item::find()->where(['category_id' => 1]);
        $provider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->assertCount(1, $provider->getModels());
    }
}
