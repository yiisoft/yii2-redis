<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

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
class RedisConnectionTest extends TestCase
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
    public function testStoreGet($data): void
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
        $this->markTestSkipped('This test is skipped due to flakiness.');

        $db = $this->getConnection(false);
        $db->configSet('timeout', 1);
        $this->assertTrue($db->ping());
        sleep(1);
        $this->assertTrue($db->ping());

        $db->close();
        $db->on(Connection::EVENT_AFTER_OPEN, function () {
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

        $this->assertTrue($db->ping());
        usleep(500000);
        $this->assertTrue($db->ping());
        sleep(2);

        $logMessages = array_map(static function ($entry) {
            return (string) $entry[0];
        }, $logger->messages);

        $reconnectionFound = false;
        foreach ($logMessages as $message) {
            if (strpos($message, 'Opening redis DB connection') !== false) {
                if ($reconnectionFound) {
                    $this->assertTrue(true, 'Reconnection detected');
                    return;
                }
                $reconnectionFound = true;
            }
        }

        $this->assertTrue($db->ping(), 'Connection works after timeout');
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
            . print_r(
                array_map(
                    static function ($s) {
                        return (string) $s;
                    },
                    ArrayHelper::getColumn($logger->messages, 0)
                ),
                true
            ));
    }

    /**
     * Retry connecting 2 times
     * @skip Flaky Test fixme
     */
    public function testConnectionTimeoutRetryCount(): void
    {
        $this->markTestSkipped('This test is skipped due to flakiness.');

        $logger = new Logger();
        Yii::setLogger($logger);

        $db = $this->getConnection(false);
        $db->retries = 2;
        $db->configSet('timeout', 1);
        $db->on(Connection::EVENT_AFTER_OPEN, function () {
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
            . print_r(
                array_map(
                    function ($s) {
                        return (string) $s;
                    },
                    ArrayHelper::getColumn($logger->messages, 0)
                ),
                true
            ));
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
        self::assertIsArray($allKeys);
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
        foreach ($pairs as $field => $expected) {
            $actual = $redis->hget($set, $field);
            $this->assertEquals($expected, $actual);
        }
    }

    /**
     * Test that parseResponse throws SocketException when fread() returns empty string
     * on a broken socket, instead of looping forever in the bulk reply while($length > 0) loop.
     *
     * This simulates a Redis node dying mid-response during cluster failover/node replacement:
     * the bulk reply header ($200\r\n) is received, but the body never arrives.
     */
    public function testParseResponseBulkReplyBrokenSocket(): void
    {
        $pair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);
        $this->assertNotFalse($pair, 'Failed to create socket pair');

        [$client, $server] = $pair;
        stream_set_timeout($client, 1);

        // Simulate: Redis sends bulk reply header, then the node dies before sending the body.
        // Use stream_socket_shutdown to close only the write side of the server,
        // so fwrite() on the client still succeeds but fread() gets EOF after the header.
        fwrite($server, "\$200\r\n");
        stream_socket_shutdown($server, STREAM_SHUT_WR);

        // Inject the fake socket into a Connection instance
        $db = new Connection();
        $pool = new \ReflectionProperty(Connection::class, '_pool');
        $pool->setAccessible(true);
        $pool->setValue($db, ['tcp://fake:6379' => $client]);

        $db->hostname = 'fake';
        $db->port = 6379;

        $this->expectException(SocketException::class);
        $this->expectExceptionMessageMatches('/Failed to read from socket/');

        $this->invokeMethod($db, 'sendRawCommand', ["*2\r\n\$3\r\nGET\r\n\$3\r\nfoo\r\n", ['GET', 'foo']]);
    }

    public function testPersistentSslSocketIsReusedWithoutReEnablingCrypto(): void
    {
        $this->withTlsServer(function (string $host, int $port): void {
            $sockets = [];
            try {
                $connection = $this->createPersistentSslConnection($host, $port);
                $connection->open();
                $socket = $connection->getSocket();
                $this->assertNotFalse($socket);
                $sockets[] = $socket;
                $this->assertArrayHasKey('crypto', stream_get_meta_data($socket));

                $reused = $this->createPersistentSslConnection($host, $port);
                $reused->open();
                $reusedSocket = $reused->getSocket();
                $this->assertNotFalse($reusedSocket);
                $sockets[] = $reusedSocket;
                $this->assertSame($socket, $reusedSocket);
                $this->assertArrayHasKey('crypto', stream_get_meta_data($reusedSocket));
            } finally {
                $this->closeSockets($sockets);
            }
        });
    }

    public function testPersistentSslSocketIsNotReusedWhenVerificationDiffers(): void
    {
        $this->withTlsServer(function (string $host, int $port): void {
            $sockets = [];
            try {
                $loose = $this->createPersistentSslConnection($host, $port);
                $loose->open();
                $looseSocket = $loose->getSocket();
                $this->assertNotFalse($looseSocket);
                $sockets[] = $looseSocket;

                $strict = $this->createPersistentSslConnection($host, $port, [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => false,
                ]);
                $warning = null;
                set_error_handler(static function (int $severity, string $message) use (&$warning): bool {
                    $warning = $message;

                    return true;
                });
                try {
                    $strict->open();
                } finally {
                    restore_error_handler();
                }
                $this->assertNotNull($warning);
                $this->assertStringContainsString('SSL', $warning);
                $strictSocket = $strict->getSocket();
                if (is_resource($strictSocket)) {
                    $sockets[] = $strictSocket;
                }
                $this->assertNotSame($looseSocket, $strictSocket);
            } finally {
                $this->closeSockets($sockets);
            }
        });
    }

    /**
     * @return string PEM encoded certificate and private key
     */
    private function createSelfSignedCertificate(): string
    {
        $key = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        $this->assertNotFalse($key);
        $csr = openssl_csr_new(['CN' => 'localhost'], $key, ['digest_alg' => 'sha256']);
        $this->assertNotFalse($csr);
        $certificate = openssl_csr_sign($csr, null, $key, 1, ['digest_alg' => 'sha256']);
        $this->assertNotFalse($certificate);
        $this->assertTrue(openssl_x509_export($certificate, $certificateOut));
        $this->assertTrue(openssl_pkey_export($key, $keyOut));

        return $certificateOut . $keyOut;
    }

    /**
     * @param array<string, mixed>|null $sslOptions
     */
    private function createPersistentSslConnection(string $host, int $port, ?array $sslOptions = null): Connection
    {
        if ($sslOptions === null) {
            $sslOptions = [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ];
        }

        return new Connection([
            'hostname' => $host,
            'port' => $port,
            'database' => null,
            'useSSL' => true,
            'socketClientFlags' => STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT,
            'connectionTimeout' => 5,
            'contextOptions' => [
                'ssl' => $sslOptions,
            ],
        ]);
    }

    /**
     * @param callable(string, int): void $callback
     */
    private function withTlsServer(callable $callback): void
    {
        $contexts = new \ReflectionProperty(Connection::class, 'persistentSslContexts');
        $contexts->setAccessible(true);
        $contexts->setValue(null, []);

        $directory = sys_get_temp_dir() . '/yii2-redis-ssl-' . bin2hex(random_bytes(4));
        mkdir($directory);
        $certificate = $directory . '/cert.pem';
        $portFile = $directory . '/port';
        file_put_contents($certificate, $this->createSelfSignedCertificate());

        $pid = pcntl_fork();
        $this->assertNotSame(-1, $pid);
        if ($pid === 0) {
            try {
                $this->serveTlsOnce($certificate, $portFile);
            } catch (\Throwable $e) {
                file_put_contents($portFile . '.err', $e->getMessage());
            }
            exit(1);
        }

        try {
            $address = $this->waitForTlsServer($portFile);
            [$host, $port] = explode(':', $address, 2);
            $callback($host, (int) $port);
        } finally {
            file_put_contents($portFile . '.done', '1');
            $status = 0;
            pcntl_waitpid($pid, $status);
            @unlink($certificate);
            @unlink($portFile);
            @unlink($portFile . '.done');
            @unlink($portFile . '.err');
            @rmdir($directory);
        }
    }

    /**
     * @param resource[] $sockets
     */
    private function closeSockets(array $sockets): void
    {
        foreach ($sockets as $socket) {
            if (is_resource($socket)) {
                @fclose($socket);
            }
        }
    }

    private function serveTlsOnce(string $certificate, string $portFile): void
    {
        $context = stream_context_create([
            'ssl' => [
                'local_cert' => $certificate,
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
                // TLS 1.3 session tickets make PHP treat the idle socket as dead and open a new one.
                'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_SERVER,
            ],
        ]);
        $errorNumber = 0;
        $errorMessage = '';
        $server = stream_socket_server(
            'tcp://127.0.0.1:0',
            $errorNumber,
            $errorMessage,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
            $context
        );
        if ($server === false) {
            exit(1);
        }
        // The forked server inherits the test error handler. Handshake failures must not abort it.
        set_error_handler(static function (): bool {
            return true;
        });
        stream_set_blocking($server, false);
        file_put_contents($portFile, stream_socket_get_name($server, false));
        $deadline = microtime(true) + 8;
        while (!is_file($portFile . '.done') && microtime(true) < $deadline) {
            $read = [$server];
            // PHP 7.4 rejects null for these by-ref array parameters.
            $write = [];
            $except = [];
            if (@stream_select($read, $write, $except, 0, 200000) <= 0) {
                continue;
            }
            $client = @stream_socket_accept($server, 0);
            if ($client === false) {
                continue;
            }
            // A socket accepted from a non-blocking listener stays non-blocking on PHP 7.4,
            // and the TLS handshake then times out.
            stream_set_blocking($client, true);
            // The constant's integer value differs between PHP versions, so PHPStan sees a plain int.
            /** @phpstan-ignore argument.type */
            @stream_socket_enable_crypto($client, true, STREAM_CRYPTO_METHOD_TLSv1_2_SERVER);
        }
        exit(0);
    }

    private function waitForTlsServer(string $portFile): string
    {
        $deadline = microtime(true) + 5;
        do {
            $address = is_file($portFile) ? file_get_contents($portFile) : false;
            if (is_string($address) && $address !== '') {
                return $address;
            }
            usleep(10000);
        } while (microtime(true) < $deadline);

        $this->fail('TLS server did not start.');
    }
}
