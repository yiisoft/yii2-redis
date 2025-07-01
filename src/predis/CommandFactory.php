<?php
declare(strict_types=1);

namespace yii\redis\predis;

use Predis\Command\RedisFactory;
use yii\redis\predis\Command\HashGetAllCommand;
use yii\redis\predis\Command\ZrangeCommand;

class CommandFactory extends RedisFactory
{
    public function __construct()
    {
        parent::__construct();

        // Переопределяем команду
        $this->commands['HGETALL'] = HashGetAllCommand::class;
//        $this->commands['ZRANGE'] = ZrangeCommand::class;
    }

}
