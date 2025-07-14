<?php

namespace yiiunit\extensions\redis;
use Yii;
use yii\helpers\ArrayHelper;
use yii\log\Logger;
use yii\redis\Connection;
use yii\redis\SocketException;
use yiiunit\extensions\redis\support\ConnectionWithErrorEmulator;

/**
 * @group redis
 */
class ConnectionTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->getConnection(false)->configSet('timeout', 0);
        $this->getConnection()->close();
        parent::tearDown();
    }

    /**
     * test connection to redis and selection of db
     */
    public function testConnect(): void
    {
        $db = $this->getConnection(false);
        $database = $db->database;
        $db->open();
        $this->assertTrue($db->ping());
        $db->set('YIITESTKEY', 'YIITESTVALUE');
        $db->close();

        $db = $this->getConnection(false);
        $db->database = $database;
        $db->open();
        $this->assertEquals('YIITESTVALUE', $db->get('YIITESTKEY'));
        $db->close();

        $db = $this->getConnection(false);
        $db->database = 1;
        $db->open();
        $this->assertNull($db->get('YIITESTKEY'));
        $db->close();
    }

    /**
     * tests whether close cleans up correctly so that a new connect works
     */
    public function testReConnect(): void
    {
        $db = $this->getConnection(false);
        $db->open();
        $this->assertTrue($db->ping());
        $db->close();

        $db->open();
        $this->assertTrue($db->ping());
        $db->close();
    }


    /**
     * @return array
     */
    public function keyValueData(): array
    {
        return [
            [123],
            [-123],
            [0],
            ['test'],
            ["test\r\ntest"],
            [''],
        ];
    }

    /**
     * @dataProvider keyValueData
     * @param mixed $data
     */
    public function testStoreGet(mixed $data):void
    {
        $db = $this->getConnection(true);

        $db->set('hi', $data);
        $this->assertEquals($data, $db->get('hi'));
    }

    public function testSerialize(): void
    {
        $db = $this->getConnection(false);
        $db->open();
        $this->assertTrue($db->ping());
        $s = serialize($db);
        $this->assertTrue($db->ping());
        $db2 = unserialize($s);
        $this->assertTrue($db->ping());
        $this->assertTrue($db2->ping());
    }

    /**
     * @skip Flaky Test fixme
     */
    public function testConnectionTimeout(): void
    {
        $db = $this->getConnection(false);
        $db->configSet('timeout', 1);
        $this->assertTrue($db->ping());
        sleep(1);
        $this->assertTrue($db->ping());

        $db->close();
        $db->on(Connection::EVENT_AFTER_OPEN, function() {
            // sleep 2 seconds after connect to make every command time out
            sleep(2);
        });

        $exception = false;
        try {
            sleep(4);
            $db->ping();
        } catch (SocketException $e) {
            $exception = true;
        }
        $this->assertTrue($exception, 'SocketException should have been thrown.');
    }

    public function testConnectionTimeoutRetry(): void
    {
        $logger = new Logger();
        Yii::setLogger($logger);

        $db = $this->getConnection(false);
        $db->retries = 1;
        $db->configSet('timeout', 1);
        $this->assertCount(3, $logger->messages, 'log of connection and init commands.');

        $this->assertTrue($db->ping());
        $this->assertCount(4, $logger->messages, 'log +1 ping command.');
        usleep(500000); // 500ms

        $this->assertTrue($db->ping());
        $this->assertCount(5, $logger->messages, 'log +1 ping command.');
        sleep(2);

        // reconnect should happen here

        $this->assertTrue($db->ping());
        $this->assertCount(11, $logger->messages, 'log +1 ping command, and reconnection.'
            . print_r(array_map(static function($s) { return (string) $s; }, ArrayHelper::getColumn($logger->messages, 0)), true));
    }

    public function testConnectionTimeoutRetryWithFirstFail(): void
    {
        $logger = new Logger();
        Yii::setLogger($logger);

        $databases = TestCase::getParam('databases');
        $redis = $databases['redis'] ?? [];
        $db = new ConnectionWithErrorEmulator($redis);
        $db->retries = 3;

        $db->configSet('timeout', 1);
        $this->assertCount(3, $logger->messages, 'log of connection and init commands.');

        $this->assertTrue($db->ping());
        $this->assertCount(4, $logger->messages, 'log +1 ping command.');

        sleep(2);

        // Set flag for emulate socket error
        $db->isTemporaryBroken = true;

        $this->assertTrue($db->ping());
        $this->assertCount(10, $logger->messages, 'log +1 ping command, and two reconnections.'
            . print_r(array_map(static function($s) { return (string) $s; }, ArrayHelper::getColumn($logger->messages, 0)), true));
    }

    /**
     * Retry connecting 2 times
     * @skip Flaky Test fixme
     */
    public function testConnectionTimeoutRetryCount(): void
    {
        $logger = new Logger();
        Yii::setLogger($logger);

        $db = $this->getConnection(false);
        $db->retries = 2;
        $db->configSet('timeout', 1);
        $db->on(Connection::EVENT_AFTER_OPEN, function() {
            // sleep 2 seconds after connect to make every command time out
            sleep(2);
        });
        $this->assertCount(3, $logger->messages, 'log of connection and init commands.');

        $exception = false;
        try {
            // should try to reconnect 2 times, before finally failing
            // results in 3 times sending the PING command to redis
            sleep(4);
            $db->ping();
        } catch (SocketException $e) {
            $exception = true;
        }
        $this->assertTrue($exception, 'SocketException should have been thrown.');
        $this->assertCount(14, $logger->messages, 'log +1 ping command, and reconnection.'
            . print_r(array_map(function($s) { return (string) $s; }, ArrayHelper::getColumn($logger->messages, 0)), true));
    }

    /**
     * https://github.com/yiisoft/yii2/issues/4745
     */
    public function testReturnType(): void
    {
        $redis = $this->getConnection();
        $redis->executeCommand('SET', ['key1', 'val1']);
        $redis->executeCommand('HMSET', ['hash1', 'hk3', 'hv3', 'hk4', 'hv4']);
        $redis->executeCommand('RPUSH', ['newlist2', 'tgtgt', 'tgtt', '44', 11]);
        $redis->executeCommand('SADD', ['newset2', 'segtggttval', 'sv1', 'sv2', 'sv3']);
        $redis->executeCommand('ZADD', ['newz2', 2, 'ss', 3, 'pfpf']);
        $allKeys = $redis->executeCommand('KEYS', ['*']);
        sort($allKeys);
        $this->assertEquals(['hash1', 'key1', 'newlist2', 'newset2', 'newz2'], $allKeys);
        $expected = [
            'hash1' => 'hash',
            'key1' => 'string',
            'newlist2' => 'list',
            'newset2' => 'set',
            'newz2' => 'zset',
        ];
        foreach ($allKeys as $key) {
            $this->assertEquals($expected[$key], $redis->executeCommand('TYPE', [$key]));
        }
    }

    public function testTwoWordCommands(): void
    {
        $redis = $this->getConnection();
        $this->assertIsArray($redis->executeCommand('CONFIG GET', ['port']));
        $this->assertIsString($redis->clientList());
        $this->assertIsString($redis->executeCommand('CLIENT LIST'));
    }

    /**
     * @return array
     */
    public function zRangeByScoreData(): array
    {
        return [
            [
                'members' => [
                    ['foo', 1],
                    ['bar', 2],
                ],
                'cases' => [
                    // without both scores and limit
                    ['0', '(1', null, null, null, null, []],
                    ['1', '(2', null, null, null, null, ['foo']],
                    ['2', '(3', null, null, null, null, ['bar']],
                    ['(0', '2', null, null, null, null, ['foo', 'bar']],

                    // with scores, but no limit
                    ['0', '(1', 'WITHSCORES', null, null, null, []],
                    ['1', '(2', 'WITHSCORES', null, null, null, ['foo', 1]],
                    ['2', '(3', 'WITHSCORES', null, null, null, ['bar', 2]],
                    ['(0', '2', 'WITHSCORES', null, null, null, ['foo', 1, 'bar', 2]],

                    // with limit, but no scores
                    ['0', '(1', null, 'LIMIT', 0, 1, []],
                    ['1', '(2', null, 'LIMIT', 0, 1, ['foo']],
                    ['2', '(3', null, 'LIMIT', 0, 1, ['bar']],
                    ['(0', '2', null, 'LIMIT', 0, 1, ['foo']],

                    // with both scores and limit
                    ['0', '(1', 'WITHSCORES', 'LIMIT', 0, 1, []],
                    ['1', '(2', 'WITHSCORES', 'LIMIT', 0, 1, ['foo', 1]],
                    ['2', '(3', 'WITHSCORES', 'LIMIT', 0, 1, ['bar', 2]],
                    ['(0', '2', 'WITHSCORES', 'LIMIT', 0, 1, ['foo', 1]],
                ],
            ],
        ];
    }

    /**
     * @dataProvider zRangeByScoreData
     * @param array $members
     * @param array $cases
     */
    public function testZRangeByScore(array $members, array $cases): void
    {
        $redis = $this->getConnection();
        $set = 'zrangebyscore';
        foreach ($members as $member) {
            [$name, $score] = $member;
            $this->assertEquals(1, $redis->zadd($set, $score, $name));
        }

        foreach ($cases as $case) {
            [$min, $max, $withScores, $limit, $offset, $count, $expectedRows] = $case;
            if ($withScores !== null && $limit !== null) {
                $rows = $redis->zrangebyscore($set, $min, $max, $withScores, $limit, $offset, $count);
            } elseif ($withScores !== null) {
                $rows = $redis->zrangebyscore($set, $min, $max, $withScores);
            } elseif ($limit !== null) {
                $rows = $redis->zrangebyscore($set, $min, $max, $limit, $offset, $count);
            } else {
                $rows = $redis->zrangebyscore($set, $min, $max);
            }
            $this->assertTrue(is_array($rows));
            $this->assertEquals(count($expectedRows), count($rows));
            for ($i = 0; $i < count($expectedRows); $i++) {
                $this->assertEquals($expectedRows[$i], $rows[$i]);
            }
        }
    }

    /**
     * @return array
     */
    public function hmSetData(): array
    {
        return [
            [
                ['hmset1', 'one', '1', 'two', '2', 'three', '3'],
                [
                    'one' => '1',
                    'two' => '2',
                    'three' => '3'
                ],
            ],
            [
                ['hmset2', 'one', null, 'two', '2', 'three', '3'],
                [
                    'one' => '',
                    'two' => '2',
                    'three' => '3'
                ],
            ]
        ];
    }

    /**
     * @dataProvider hmSetData
     * @param array $params
     * @param array $pairs
     */
    public function testHMSet(array $params, array $pairs): void
    {
        $redis = $this->getConnection();
        $set = $params[0];
        call_user_func_array([$redis,'hmset'], $params);
        foreach($pairs as $field => $expected) {
            $actual = $redis->hget($set, $field);
            $this->assertEquals($expected, $actual);
        }
    }
}
