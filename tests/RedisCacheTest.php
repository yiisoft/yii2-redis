<?php
namespace yiiunit\extensions\redis;

use Yii;
use yii\redis\Cache;
use yii\redis\Connection;
use yiiunit\framework\caching\CacheTestCase;

/**
 * Class for testing redis cache backend
 * @group redis
 * @group caching
 */
class RedisCacheTest extends CacheTestCase
{
    private $_cacheInstance;

    /**
     * @return Cache
     */
    protected function getCacheInstance()
    {
        $databases = TestCase::getParam('databases');
        $params = isset($databases['redis']) ? $databases['redis'] : null;
        if ($params === null) {
            $this->markTestSkipped('No redis server connection configured.');
        }
        $connection = new Connection($params);
//        if (!@stream_socket_client($connection->hostname . ':' . $connection->port, $errorNumber, $errorDescription, 0.5)) {
//            $this->markTestSkipped('No redis server running at ' . $connection->hostname . ':' . $connection->port . ' : ' . $errorNumber . ' - ' . $errorDescription);
//        }

        $this->mockApplication(['components' => ['redis' => $connection]]);

        if ($this->_cacheInstance === null) {
            $this->_cacheInstance = new Cache();
        }

        return $this->_cacheInstance;
    }

    public function testExpireMilliseconds()
    {
        $cache = $this->getCacheInstance();

        $this->assertTrue($cache->set('expire_test_ms', 'expire_test_ms', 0.2));
        usleep(100000);
        $this->assertEquals('expire_test_ms', $cache->get('expire_test_ms'));
        usleep(300000);
        $this->assertFalse($cache->get('expire_test_ms'));
    }

    public function testExpireAddMilliseconds()
    {
        $cache = $this->getCacheInstance();

        $this->assertTrue($cache->add('expire_testa_ms', 'expire_testa_ms', 0.2));
        usleep(100000);
        $this->assertEquals('expire_testa_ms', $cache->get('expire_testa_ms'));
        usleep(300000);
        $this->assertFalse($cache->get('expire_testa_ms'));
    }

    public function testExpireIntegerSeconds()
    {
        $cache = $this->getCacheInstance();

        $this->assertTrue($cache->set('expire_test_is', 'expire_test_is', 2));
        sleep(1);
        $this->assertEquals('expire_test_is', $cache->get('expire_test_is'));
        sleep(3);
        $this->assertFalse($cache->get('expire_test_is'));
    }

    public function testExpireLongSeconds()
    {
        $cache = $this->getCacheInstance();

        // on 32-bit: PHP_INT_MAX === 2147483647
        //      max ttl (in SETEX, sec) is about PHP_INT_MAX * 1e6
        //      max ttl (in PSETEX, ms) is about PHP_INT_MAX * 1e9
        //
        // on 64-bit: PHP_INT_MAX === 9223372036854775807
        //      max ttl (in SETEX, sec) is about PHP_INT_MAX * 1e-4
        //      max ttl (in PSETEX, ms) is about PHP_INT_MAX * 1e-1
        $ttl = 2147483647;
        $this->assertTrue($cache->set('expire_test_ls', 'expire_test_ls', $ttl));
        sleep(2);
        $this->assertEquals('expire_test_ls', $cache->get('expire_test_ls'));
        $this->assertLessThan($ttl - 1, Yii::$app->redis->ttl('expire_test_ls'));
        $this->assertGreaterThan($ttl - 5, Yii::$app->redis->ttl('expire_test_ls'));
    }

    /**
     * Store a value that is 2 times buffer size big
     * https://github.com/yiisoft/yii2/issues/743
     */
    public function testLargeData()
    {
        $cache = $this->getCacheInstance();

        $data = str_repeat('XX', 8192); // http://www.php.net/manual/en/function.fread.php
        $key = 'bigdata1';

        $this->assertFalse($cache->get($key));
        $cache->set($key, $data);
        $this->assertSame($cache->get($key), $data);

        // try with multibyte string
        $data = str_repeat('ЖЫ', 8192); // http://www.php.net/manual/en/function.fread.php
        $key = 'bigdata2';

        $this->assertFalse($cache->get($key));
        $cache->set($key, $data);
        $this->assertSame($cache->get($key), $data);
    }

    /**
     * Store a megabyte and see how it goes
     * https://github.com/yiisoft/yii2/issues/6547
     */
    public function testReallyLargeData()
    {
        $cache = $this->getCacheInstance();

        $keys = [];
        for($i = 1; $i < 16; $i++) {
            $key = 'realbigdata' . $i;
            $data = str_repeat('X', 100 * 1024); // 100 KB
            $keys[$key] = $data;

//            $this->assertTrue($cache->get($key) === false); // do not display 100KB in terminal if this fails :)
            $cache->set($key, $data);
        }
        $values = $cache->multiGet(array_keys($keys));
        foreach($keys as $key => $value) {
            $this->assertArrayHasKey($key, $values);
            $this->assertSame($values[$key], $value);
        }
    }

    public function testMultiByteGetAndSet()
    {
        $cache = $this->getCacheInstance();

        $data = ['abc' => 'ежик', 2 => 'def'];
        $key = 'data1';

        $this->assertFalse($cache->get($key));
        $cache->set($key, $data);
        $this->assertSame($cache->get($key), $data);
    }
}
