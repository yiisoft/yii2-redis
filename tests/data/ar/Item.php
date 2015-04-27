<?php

namespace yiiunit\extensions\redis\data\ar;

class Item extends ActiveRecord
{
    public function attributes()
    {
        return ['id', 'name', 'category_id'];
    }
}
