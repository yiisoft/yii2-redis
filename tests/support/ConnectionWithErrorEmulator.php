<?php
namespace yiiunit\extensions\redis\support;

use yii\redis\Connection;
use yii\redis\SocketException;

class ConnectionWithErrorEmulator extends Connection {
    public $isTemporaryBroken = false;

    protected function sendCommandInternal($command, $params)
    {
        // Emulate read from socket error
        if ($this->isTemporaryBroken) {
            // Unset flag for emulate socket error
            $this->isTemporaryBroken = false;
            throw new SocketException("Failed to read from socket.\nRedis command was: " . $command);
        }
        return parent::sendCommandInternal($command, $params);
    }
}
