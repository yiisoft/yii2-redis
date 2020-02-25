<?php

namespace yiiunit\extensions\redis;
use Yii;
use yii\helpers\ArrayHelper;
use yii\log\Logger;
use yii\redis\Connection;
use yii\redis\SocketException;

/**
 * @group redis
 */
class ConnectionTest extends TestCase
{
    protected function tearDown()
    {
        $this->getConnection(false)->configSet('timeout', 0);
        parent::tearDown();
    }

    /**
     * test connection to redis and selection of db
     */
    public function testConnect()
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
    public function testReConnect()
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
    public function keyValueData()
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
    public function testStoreGet($data)
    {
        $db = $this->getConnection(true);

        $db->set('hi', $data);
        $this->assertEquals($data, $db->get('hi'));
    }

    public function testSerialize()
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

    public function testConnectionTimeout()
    {
        $db = $this->getConnection(false);
        $db->configSet('timeout', 1);
        $this->assertTrue($db->ping());
        sleep(1);
        $this->assertTrue($db->ping());
        sleep(2);
        if (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('\yii\redis\SocketException');
        } else {
            $this->expectException('\yii\redis\SocketException');
        }
        $this->assertTrue($db->ping());
    }

    public function testConnectionTimeoutRetry()
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
            . print_r(array_map(function($s) { return (string) $s; }, ArrayHelper::getColumn($logger->messages, 0)), true));
    }

    /**
     * Retry connecting 2 times
     */
    public function testConnectionTimeoutRetryCount()
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
            sleep(2);
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
    public function testReturnType()
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

    public function testTwoWordCommands()
    {
        $redis = $this->getConnection();
        $this->assertTrue(is_array($redis->executeCommand('CONFIG GET', ['port'])));
        $this->assertTrue(is_string($redis->clientList()));
        $this->assertTrue(is_string($redis->executeCommand('CLIENT LIST')));
    }

    /**
     * @return array
     */
    public function zRangeByScoreData()
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
    public function testZRangeByScore($members, $cases)
    {
        $redis = $this->getConnection();
        $set = 'zrangebyscore';
        foreach ($members as $member) {
            list($name, $score) = $member;
            $this->assertEquals(1, $redis->zadd($set, $score, $name));
        }

        foreach ($cases as $case) {
            list($min, $max, $withScores, $limit, $offset, $count, $expectedRows) = $case;
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
    public function hmSetData()
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
    public function testHMSet($params, $pairs)
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
