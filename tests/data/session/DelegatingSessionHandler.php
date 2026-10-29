<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\extensions\redis\data\session;

use SessionHandlerInterface;
use yii\web\Session;

/**
 * Session handler that forwards the storage callbacks to the session component, like a logging decorator does.
 * It does not implement `validateId()`, so PHP does not validate session IDs through it in strict mode.
 */
class DelegatingSessionHandler implements SessionHandlerInterface
{
    /**
     * @var Session
     */
    private $session;


    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    #[\ReturnTypeWillChange]
    public function open($path, $name)
    {
        return $this->session->openSession($path, $name);
    }

    #[\ReturnTypeWillChange]
    public function close()
    {
        return $this->session->closeSession();
    }

    #[\ReturnTypeWillChange]
    public function read($id)
    {
        return $this->session->readSession($id);
    }

    #[\ReturnTypeWillChange]
    public function write($id, $data)
    {
        return $this->session->writeSession($id, $data);
    }

    #[\ReturnTypeWillChange]
    public function destroy($id)
    {
        return $this->session->destroySession($id);
    }

    #[\ReturnTypeWillChange]
    public function gc($maxLifetime)
    {
        return $this->session->gcSession($maxLifetime);
    }
}
