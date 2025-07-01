<?php
declare(strict_types=1);

namespace yii\redis\predis\Command;

//use Predis\Command\PrefixableCommand as RedisCommand;
use Predis\Command\Redis\HGETALL;

class HashGetAllCommand extends HGETALL
{
    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return $data;
    }
}
