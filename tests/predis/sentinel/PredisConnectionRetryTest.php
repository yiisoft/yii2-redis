<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\extensions\predis\sentinel;

use Predis\Connection\ConnectionException as PredisConnectionException;
use Predis\Connection\Resource\Exception\StreamInitException;
use Predis\Retry\Retry;
use Predis\Retry\Strategy\EqualBackoff;
use Predis\Retry\Strategy\ExponentialBackoff;
use Predis\Retry\Strategy\NoBackoff;
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

    public function testExecuteCommandWithoutRetry(): void
    {
        $db = $this->getConnection(true);
        $db->set('sentinel_retry_test_key', 'value1');
        $this->assertEquals('value1', $db->get('sentinel_retry_test_key'));
    }

    public function testExecuteCommandWithRetryEqualBackoff(): void
    {
        $databases = self::getParam('databases');
        $params = $databases['redis'] ?? [];
        $params['options']['parameters']['retry'] = new Retry(
            new EqualBackoff(1000),
            3
        );

        $db = new PredisConnection($params);
        $db->open();
        $db->flushdb();

        $db->set('sentinel_retry_test_key2', 'value2');
        $this->assertEquals('value2', $db->get('sentinel_retry_test_key2'));
        $db->close();
    }

    public function testExecuteCommandWithRetryExponentialBackoff(): void
    {
        $databases = self::getParam('databases');
        $params = $databases['redis'] ?? [];
        $params['options']['parameters']['retry'] = new Retry(
            new ExponentialBackoff(1000, 10000),
            3
        );

        $db = new PredisConnection($params);
        $db->open();
        $db->flushdb();

        $db->set('sentinel_retry_exp_key', 'exp_value');
        $this->assertEquals('exp_value', $db->get('sentinel_retry_exp_key'));
        $db->close();
    }

    public function testExecuteCommandWithRetryNoBackoff(): void
    {
        $databases = self::getParam('databases');
        $params = $databases['redis'] ?? [];
        $params['options']['parameters']['retry'] = new Retry(
            new NoBackoff(),
            3
        );

        $db = new PredisConnection($params);
        $db->open();
        $db->flushdb();

        $db->set('sentinel_retry_nobackoff_key', 'nobackoff_value');
        $this->assertEquals('nobackoff_value', $db->get('sentinel_retry_nobackoff_key'));
        $db->close();
    }

    public function testRetryWithDefaultCatchableExceptions(): void
    {
        $retry = new Retry(new NoBackoff(), 3);
        $this->assertSame(3, $retry->getRetries());
    }

    public function testRetryWithCustomCatchableExceptions(): void
    {
        $retry = new Retry(
            new NoBackoff(),
            3,
            [PredisConnectionException::class, StreamInitException::class]
        );
        $this->assertSame(3, $retry->getRetries());
    }

    public function testRetryUpdateCatchableExceptions(): void
    {
        $retry = new Retry(new NoBackoff(), 3);
        $retry->updateCatchableExceptions([\Predis\Response\ServerException::class]);

        $this->assertSame(3, $retry->getRetries());
    }

    public function testRetryUpdateRetriesCount(): void
    {
        $retry = new Retry(new NoBackoff(), 3);
        $this->assertSame(3, $retry->getRetries());

        $retry->updateRetriesCount(5);
        $this->assertSame(5, $retry->getRetries());
    }

    public function testRetryGetStrategy(): void
    {
        $strategy = new ExponentialBackoff(1000, 10000);
        $retry = new Retry($strategy, 3);

        $this->assertSame($strategy, $retry->getStrategy());
    }

    public function testRetryOnClosedConnectionWithRetryConfigured(): void
    {
        $databases = self::getParam('databases');
        $params = $databases['redis'] ?? [];
        $params['options']['parameters']['retry'] = new Retry(
            new EqualBackoff(1000),
            3
        );

        $db = new PredisConnection($params);
        $db->open();
        $db->flushdb();

        $db->set('sentinel_retry_persistent_key', 'persistent_value');
        $db->close();
        $this->assertFalse($db->getIsActive());

        $db->open();
        $result = $db->get('sentinel_retry_persistent_key');
        $this->assertEquals('persistent_value', $result);
        $db->close();
    }

    public function testRetryWithWorkingReconnect(): void
    {
        $databases = self::getParam('databases');
        $params = $databases['redis'] ?? [];
        $params['options']['parameters']['retry'] = new Retry(
            new EqualBackoff(1000),
            3
        );

        $db = new PredisConnection($params);
        $db->open();
        $db->flushdb();

        $db->set('sentinel_retry_reconnect_key', 'before');
        $db->close();
        $this->assertFalse($db->getIsActive());

        $db->open();
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
