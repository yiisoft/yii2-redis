<?php
namespace yiiunit\extensions\redis\support;

use yii\redis\Connection;
use yii\redis\SocketException;

class ConnectionWithErrorEmulator extends Connection {
    public $isTemporaryBroken = false;

    protected function sendRawCommand($command, $params)
    {
        // Emulate read from socket error
        if ($this->isTemporaryBroken) {
            // Unset flag for emulate socket error
            $this->isTemporaryBroken = false;
            throw new SocketException("Failed to read from socket.\nRedis command was: " . $command);
        }
        return parent::sendRawCommand($command, $params);
    }
}
