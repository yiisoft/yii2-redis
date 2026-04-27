<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\extensions\predis\sentinel;

use yii\base\InvalidConfigException;
use yii\redis\LuaScriptBuilder;
use yii\redis\Predis\PredisConnection;

class PredisConnectionRetryTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->getConnection(false);
        parent::tearDown();
    }

    public function testDefaultRetriesIsZero(): void
    {
        $db = $this->getConnection(false);
        $this->assertSame(0, $db->retries);
    }

    public function testDefaultRetryIntervalIsZero(): void
    {
        $db = $this->getConnection(false);
        $this->assertSame(0, $db->retryInterval);
    }

    public function testExecuteCommandWithoutRetries(): void
    {
        $db = $this->getConnection(true);
        $db->set('sentinel_retry_test_key', 'value1');
        $this->assertEquals('value1', $db->get('sentinel_retry_test_key'));
    }

    public function testExecuteCommandWithRetriesSuccessOnFirstAttempt(): void
    {
        $db = $this->getConnection(true);
        $db->retries = 3;

        $db->set('sentinel_retry_test_key2', 'value2');
        $this->assertEquals('value2', $db->get('sentinel_retry_test_key2'));
    }

    public function testRetryOnClosedConnection(): void
    {
        $db = $this->getConnection(true);
        $db->set('sentinel_retry_persistent_key', 'persistent_value');

        $db->close();
        $this->assertFalse($db->getIsActive());

        $db->retries = 2;
        $db->retryInterval = 1000;
        $result = $db->get('sentinel_retry_persistent_key');
        $this->assertEquals('persistent_value', $result);
    }

    public function testRetryWithWorkingReconnect(): void
    {
        $db = $this->getConnection(true);
        $db->set('sentinel_retry_reconnect_key', 'before');

        $db->retries = 3;
        $db->retryInterval = 1000;

        $db->close();
        $this->assertFalse($db->getIsActive());

        $result = $db->get('sentinel_retry_reconnect_key');
        $this->assertEquals('before', $result);
        $this->assertTrue($db->getIsActive());
        $db->close();
    }

    public function testCloseDeactivatesConnection(): void
    {
        $db = $this->getConnection(true);
        $this->assertNotNull($db->getClient());
        $this->assertTrue($db->getIsActive());

        $db->close();
        $this->assertFalse($db->getIsActive());
    }

    public function testReconnectAfterClose(): void
    {
        $db = $this->getConnection(true);
        $db->set('sentinel_reconnect_test_key', 'before_close');

        $db->close();
        $this->assertFalse($db->getIsActive());

        $db->open();
        $this->assertEquals('before_close', $db->get('sentinel_reconnect_test_key'));
        $db->close();
    }

    public function testCloseIsIdempotent(): void
    {
        $db = $this->getConnection(true);
        $db->close();
        $db->close();
        $this->assertFalse($db->getIsActive());
    }

    public function testGetIsActiveWhenNotConnected(): void
    {
        $db = $this->getConnection(false);
        $this->assertFalse($db->getIsActive());
    }

    public function testGetIsActiveWhenConnected(): void
    {
        $db = $this->getConnection(true);
        $this->assertTrue($db->getIsActive());
        $db->close();
    }

    public function testGetClientOpensConnection(): void
    {
        $db = $this->getConnection(false);
        $this->assertFalse($db->getIsActive());
        $client = $db->getClient();
        $this->assertNotNull($client);
        $db->close();
    }

    public function testGetLuaScriptBuilder(): void
    {
        $db = $this->getConnection(false);
        $builder = $db->getLuaScriptBuilder();
        $this->assertSame(LuaScriptBuilder::class, get_class($builder));
    }

    public function testOpenTriggersAfterOpenEvent(): void
    {
        $db = $this->getConnection(false);
        $triggered = false;
        $db->on(PredisConnection::EVENT_AFTER_OPEN, function () use (&$triggered) {
            $triggered = true;
        });
        $db->open();
        $this->assertTrue($triggered);
        $db->close();
    }

    public function testOpenWithEmptyParametersThrowsException(): void
    {
        $db = new PredisConnection();
        $db->parameters = null;

        $this->expectException(InvalidConfigException::class);
        $db->open();
    }

    public function testMagicCallForRedisCommand(): void
    {
        $db = $this->getConnection(true);
        $db->set('sentinel_magic_test_key', 'magic_value');
        $this->assertEquals('magic_value', $db->get('sentinel_magic_test_key'));
    }

    public function testExecuteCommandReturnsBoolForOkStatus(): void
    {
        $db = $this->getConnection(true);
        $result = $db->set('sentinel_bool_test_key', 'val');
        $this->assertTrue($result);
    }

    public function testExecuteCommandReturnsBoolForPong(): void
    {
        $db = $this->getConnection(true);
        $result = $db->ping();
        $this->assertTrue($result);
    }
}
