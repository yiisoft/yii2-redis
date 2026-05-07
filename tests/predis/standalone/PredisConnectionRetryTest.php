<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\extensions\predis\standalone;

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
        $db->set('retry_test_key', 'value1');
        $this->assertEquals('value1', $db->get('retry_test_key'));
    }

    public function testExecuteCommandWithRetryEqualBackoff(): void
    {
        $databases = self::getParam('databases');
        $params = $databases['redis'] ?? [];
        $params['options']['parameters']['retry'] = new Retry(
            new EqualBackoff(100),
            1
        );

        $db = new PredisConnection($params);
        $db->open();
        $db->flushdb();

        $db->set('retry_test_key2', 'value2');
        $this->assertEquals('value2', $db->get('retry_test_key2'));
        $db->close();
    }

    public function testExecuteCommandWithRetryExponentialBackoff(): void
    {
        $databases = self::getParam('databases');
        $params = $databases['redis'] ?? [];
        $params['options']['parameters']['retry'] = new Retry(
            new ExponentialBackoff(100, 1000),
            1
        );

        $db = new PredisConnection($params);
        $db->open();
        $db->flushdb();

        $db->set('retry_exp_key', 'exp_value');
        $this->assertEquals('exp_value', $db->get('retry_exp_key'));
        $db->close();
    }

    public function testExecuteCommandWithRetryNoBackoff(): void
    {
        $databases = self::getParam('databases');
        $params = $databases['redis'] ?? [];
        $params['options']['parameters']['retry'] = new Retry(
            new NoBackoff(),
            1
        );

        $db = new PredisConnection($params);
        $db->open();
        $db->flushdb();

        $db->set('retry_nobackoff_key', 'nobackoff_value');
        $this->assertEquals('nobackoff_value', $db->get('retry_nobackoff_key'));
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

    public function testRetryOptionIsWiredIntoStandaloneClient(): void
    {
        $retry = new Retry(new EqualBackoff(100), 1);

        $databases = self::getParam('databases');
        $params = $databases['redis'] ?? [];
        $params['options']['parameters']['retry'] = $retry;

        $db = new PredisConnection($params);
        $db->open();
        $db->ping();

        $client = $db->getClient();
        $this->assertNotNull($client);
        $connectionParameters = $client->getConnection()->getParameters();

        $this->assertFalse($connectionParameters->isDisabledRetry());
        $this->assertSame($retry, $connectionParameters->retry);
        $db->close();
    }

    public function testRetryThrowsAfterAllAttemptsExhausted(): void
    {
        $db = new PredisConnection([
            'parameters' => 'tcp://redis:1?timeout=0.001',
            'options' => [
                'parameters' => [
                    'retry' => new Retry(new EqualBackoff(100), 1),
                ],
            ],
        ]);

        $thrown = null;
        try {
            $db->executeCommand('GET', ['nonexistent_key']);
        } catch (PredisConnectionException | StreamInitException $e) {
            $thrown = $e;
        }
        $this->assertNotNull($thrown);
    }

    public function testRetryDelayWithEqualBackoff(): void
    {
        $db = new PredisConnection([
            'parameters' => 'tcp://redis:1?timeout=0.001',
            'options' => [
                'parameters' => [
                    'retry' => new Retry(new EqualBackoff(5000), 1),
                ],
            ],
        ]);

        $start = microtime(true);
        try {
            $db->executeCommand('GET', ['test']);
        } catch (PredisConnectionException | StreamInitException $e) {
        }
        $elapsed = (microtime(true) - $start) * 1000;

        $this->assertGreaterThanOrEqual(4, $elapsed);
    }

    public function testRetryDelayWithExponentialBackoff(): void
    {
        $db = new PredisConnection([
            'parameters' => 'tcp://redis:1?timeout=0.001',
            'options' => [
                'parameters' => [
                    'retry' => new Retry(new ExponentialBackoff(5000, 10000), 1),
                ],
            ],
        ]);

        $start = microtime(true);
        try {
            $db->executeCommand('GET', ['test']);
        } catch (PredisConnectionException | StreamInitException $e) {
        }
        $elapsed = (microtime(true) - $start) * 1000;

        $this->assertGreaterThanOrEqual(1, $elapsed);
    }

    public function testExponentialBackoffComputeStrategy(): void
    {
        $strategy = new ExponentialBackoff(1000, 10000);
        $this->assertSame(1000, $strategy->compute(0));
        $this->assertSame(2000, $strategy->compute(1));
        $this->assertSame(4000, $strategy->compute(2));
        $this->assertSame(8000, $strategy->compute(3));
        $this->assertSame(10000, $strategy->compute(4));
    }

    public function testEqualBackoffComputeStrategy(): void
    {
        $strategy = new EqualBackoff(5000);
        $this->assertSame(5000, $strategy->compute(0));
        $this->assertSame(5000, $strategy->compute(1));
        $this->assertSame(5000, $strategy->compute(5));
    }

    public function testNoBackoffComputeStrategy(): void
    {
        $strategy = new NoBackoff();
        $this->assertSame(0, $strategy->compute(0));
        $this->assertSame(0, $strategy->compute(1));
    }

    public function testExponentialBackoffGetBaseAndGetCap(): void
    {
        $strategy = new ExponentialBackoff(2000, 20000);
        $this->assertSame(2000, $strategy->getBase());
        $this->assertSame(20000, $strategy->getCap());
    }

    public function testRetryOnClosedConnectionWithRetryConfigured(): void
    {
        $databases = self::getParam('databases');
        $params = $databases['redis'] ?? [];
        $params['options']['parameters']['retry'] = new Retry(
            new EqualBackoff(100),
            1
        );

        $db = new PredisConnection($params);
        $db->open();
        $db->flushdb();

        $db->set('retry_persistent_key', 'persistent_value');
        $db->close();
        $this->assertFalse($db->getIsActive());

        $result = $db->get('retry_persistent_key');
        $this->assertEquals('persistent_value', $result);
        $this->assertTrue($db->getIsActive());
        $db->close();
    }

    public function testRetryWithWorkingReconnect(): void
    {
        $databases = self::getParam('databases');
        $params = $databases['redis'] ?? [];
        $params['options']['parameters']['retry'] = new Retry(
            new EqualBackoff(100),
            1
        );

        $db = new PredisConnection($params);
        $db->open();
        $db->flushdb();

        $db->set('retry_reconnect_key', 'before');
        $db->close();
        $this->assertFalse($db->getIsActive());

        $result = $db->get('retry_reconnect_key');
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
        $db->set('reconnect_test_key', 'before_close');

        $db->close();
        $this->assertFalse($db->getIsActive());

        $db->open();
        $this->assertEquals('before_close', $db->get('reconnect_test_key'));
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
        $db->set('magic_test_key', 'magic_value');
        $this->assertEquals('magic_value', $db->get('magic_test_key'));
    }

    public function testExecuteCommandReturnsBoolForOkStatus(): void
    {
        $db = $this->getConnection(true);
        $result = $db->set('bool_test_key', 'val');
        $this->assertTrue($result);
    }

    public function testExecuteCommandReturnsBoolForPong(): void
    {
        $db = $this->getConnection(true);
        $result = $db->ping();
        $this->assertTrue($result);
    }
}
