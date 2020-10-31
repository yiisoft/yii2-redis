<?php

namespace yiiunit\extensions\redis;

use yii\redis\Session;
use yii\web\DbSession;

/**
 * Class for testing redis session backend
 * @group redis
 * @group session
 */
class RedisSessionTest extends TestCase
{
    public function testReadWrite()
    {
        $session = new Session();

        $session->writeSession('test', 'session data');
        $this->assertEquals('session data', $session->readSession('test'));
        $session->destroySession('test');
        $this->assertEquals('', $session->readSession('test'));
    }

    /**
     * Test set name. Also check set name twice and after open
     * @runInSeparateProcess
     */
    public function testSetName()
    {
        $session = new Session();
        $session->setName('oldName');

        $this->assertEquals('oldName', $session->getName());

        $session->open();
        $session->setName('newName');

        $this->assertEquals('newName', $session->getName());

        $session->destroy();
    }

    /**
     * @depends testReadWrite
     * @runInSeparateProcess
     */
    public function testStrictMode()
    {
        //non-strict-mode test
        $nonStrictSession = new Session([
            'useStrictMode' => false,
        ]);
        $nonStrictSession->close();
        $nonStrictSession->destroySession('non-existing-non-strict');
        $nonStrictSession->setId('non-existing-non-strict');
        $nonStrictSession->open();
        $this->assertEquals('non-existing-non-strict', $nonStrictSession->getId());
        $nonStrictSession->close();

        //strict-mode test
        $strictSession = new Session([
            'useStrictMode' => true,
        ]);
        $strictSession->close();
        $strictSession->destroySession('non-existing-strict');
        $strictSession->setId('non-existing-strict');
        $strictSession->open();
        $id = $strictSession->getId();
        $this->assertNotEquals('non-existing-strict', $id);
        $strictSession->set('strict_mode_test', 'session data');
        $strictSession->close();
        //Ensure session was not stored under forced id
        $strictSession->setId('non-existing-strict');
        $strictSession->open();
        $this->assertNotEquals('session data', $strictSession->get('strict_mode_test'));
        $strictSession->close();
        //Ensure session can be accessed with the new (and thus existing) id.
        $strictSession->setId($id);
        $strictSession->open();
        $this->assertNotEmpty($id);
        $this->assertEquals($id, $strictSession->getId());
        $this->assertEquals('session data', $strictSession->get('strict_mode_test'));
        $strictSession->close();
    }
}
