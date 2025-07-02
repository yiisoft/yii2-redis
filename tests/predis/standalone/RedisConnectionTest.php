<?php

namespace yiiunit\extensions\redis\predis\standalone;

use yii\base\InvalidConfigException;

/**
 * @group redis
 */
class ConnectionTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->getConnection(false);
        parent::tearDown();
    }

    /**
     * test connection to redis and selection of db
     */
    public function testConnect(): void
    {
        $db = $this->getConnection(false);
        $db->open();
        $this->assertTrue($db->ping());
        $db->set('YIITESTKEY', 'YIITESTVALUE');
        $db->close();

        $db = $this->getConnection(false);
        $db->open();
        $this->assertEquals('YIITESTVALUE', $db->get('YIITESTKEY'));
        $db->close();

        $db = $this->getConnection(false);
        $db->getClient()->select(1);
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
     * @throws InvalidConfigException
     */
    public function testStoreGet(mixed $data): void
    {
        $db = $this->getConnection(true);

        $db->set('hi', $data);
        $this->assertEquals($data, $db->get('hi'));
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
     * @throws InvalidConfigException
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
            } else if ($withScores !== null) {
                $rows = $redis->zrangebyscore($set, $min, $max, $withScores);
            } else if ($limit !== null) {
                $rows = $redis->zrangebyscore($set, $min, $max, $limit, $offset, $count);
            } else {
                $rows = $redis->zrangebyscore($set, $min, $max);
            }
            $this->assertIsArray($rows);
            $this->assertSameSize($expectedRows, $rows);
            for ($i = 0, $iMax = count($expectedRows); $i < $iMax; $i++) {
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
                    'three' => '3',
                ],
            ],
            [
                ['hmset2', 'one', null, 'two', '2', 'three', '3'],
                [
                    'one' => '',
                    'two' => '2',
                    'three' => '3',
                ],
            ],
        ];
    }

    /**
     * @dataProvider hmSetData
     * @param array $params
     * @param array $pairs
     * @throws InvalidConfigException
     */
    public function testHMSet(array $params, array $pairs): void
    {
        $redis = $this->getConnection();
        $set = $params[0];
        call_user_func_array([$redis, 'hmset'], $params);
        foreach ($pairs as $field => $expected) {
            $actual = $redis->hget($set, $field);
            $this->assertEquals($expected, $actual);
        }
    }
}
