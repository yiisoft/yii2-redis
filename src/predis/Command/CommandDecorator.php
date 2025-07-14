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
     * Yii components expect response without changes
     * @inheritdoc
     */
    public function parseResponse($data)
    {
        return $data;
    }

    // Calling methods of the original class

    public function __call($method, $args) { return call_user_func_array([$this->originalCommand, $method], $args); }

    public function getId():string { return $this->originalCommand->getId(); }

    public function setArguments(array $arguments): void { $this->originalCommand->setArguments($arguments); }

    public function getArguments(): array { return $this->originalCommand->getArguments(); }

    public function setSlot($slot): void { $this->originalCommand->setSlot($slot); }

    public function getSlot(): ?int { return $this->originalCommand->getSlot(); }

    public function setRawArguments(array $arguments): void { $this->originalCommand->setRawArguments($arguments); }

    public function getArgument($index) { return $this->originalCommand->getArgument($index); }

    public function parseResp3Response($data) { return $this->originalCommand->parseResp3Response($data); }

    public function serializeCommand(): string { return $this->originalCommand->serializeCommand(); }
}
