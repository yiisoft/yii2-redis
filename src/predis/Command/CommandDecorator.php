<?php
declare(strict_types=1);

namespace yii\redis\predis\Command;

use Predis\Command\CommandInterface;
use Predis\Command\Redis\COMMAND;

class CommandDecorator extends COMMAND
{
    private $originalCommand;

    public function __construct(CommandInterface $command)
    {
        $this->originalCommand = $command;
    }

    /**
     * @inheritdoc
     */
    public function parseResponse($data)
    {
        return $data; // Ваша реализация
    }

    // Делегируем все остальные вызовы оригинальной команде
    public function __call($method, $args)
    {
        return call_user_func_array([$this->originalCommand, $method], $args);
    }

    public function getId():string { return $this->originalCommand->getId(); }
    public function setArguments(array $arguments):void { $this->originalCommand->setArguments($arguments); }
    public function getArguments():array { return $this->originalCommand->getArguments(); }
}
