<?php
namespace yiiunit\extensions\redis;

use Yii;
use yii\redis\Connection;
use yii\redis\Mutex;

/**
 * Class for testing redis mutex
 * @group redis
 * @group mutex
 */
class RedisMutexTest extends TestCase
{
    protected static $mutexName = 'testmutex';

    protected static $mutexPrefix = 'prefix';

    private static $_keys = [];

    public function testBasicAcquireAndRelease()
    {
        $mutex = $this->createMutex();

        $this->assertFalse($mutex->release(static::$mutexName));
        $this->assertMutexKeyNotInRedis();

        $this->assertTrue($mutex->acquire(static::$mutexName));
        $this->assertMutexKeyInRedis();
        $this->assertTrue($mutex->release(static::$mutexName));
        $this->assertMutexKeyNotInRedis();

        $this->assertFalse($mutex->release(static::$mutexName));
        $this->assertMutexKeyNotInRedis();
    }

    /**
     * @covers \yii\redis\Mutex::acquireLock
     * @covers \yii\redis\Mutex::releaseLock
     */
    public function testThatMutexLockIsWorking()
    {
        $mutexOne = $this->createMutex();
        $mutexTwo = $this->createMutex();

        $this->assertMutexKeyNotInRedis();
        $this->assertTrue($mutexOne->acquire(static::$mutexName));
        $this->assertMutexKeyInRedis();
        $this->assertFalse($mutexTwo->acquire(static::$mutexName));
        $this->assertMutexKeyInRedis();

        $this->assertTrue($mutexOne->release(static::$mutexName));
        $this->assertMutexKeyNotInRedis();

        $this->assertTrue($mutexTwo->acquire(static::$mutexName));
        $this->assertMutexKeyInRedis();
        $this->assertLessThanOrEqual(2500, $mutexTwo->redis->executeCommand('PTTL', [$this->getKey(static::$mutexName)]));

        $this->assertFalse($mutexOne->acquire(static::$mutexName, 2));

        $this->assertTrue($mutexOne->acquire(static::$mutexName, 3));
        $this->assertMutexKeyInRedis();

        $this->assertTrue($mutexOne->release(static::$mutexName));
        $this->assertMutexKeyNotInRedis();
        $this->assertFalse($mutexTwo->release(static::$mutexName));
        $this->assertMutexKeyNotInRedis();
    }

    public function testExpire()
    {
        $mutex = $this->createMutex();

        $this->assertTrue($mutex->acquire(static::$mutexName));
        $this->assertMutexKeyInRedis();

        sleep(3);

        $this->assertMutexKeyNotInRedis();
        $this->assertFalse($mutex->release(static::$mutexName));
        $this->assertMutexKeyNotInRedis();
    }

    protected function setUp() {
        parent::setUp();
        $databases = TestCase::getParam('databases');
        $params = isset($databases['redis']) ? $databases['redis'] : null;
        if ($params === null) {
            $this->markTestSkipped('No redis server connection configured.');
            return;
        }

        $connection = new Connection($params);
        $this->mockApplication(['components' => ['redis' => $connection]]);
    }

    /**
     * @return Mutex
     * @throws \yii\base\InvalidConfigException
     */
    protected function createMutex()
    {
        return Yii::createObject([
            'class' => Mutex::className(),
            'expire' => 2.5,
            'keyPrefix' => static::$mutexPrefix
        ]);
    }

    protected function getKey($name)
    {
        if (!isset(self::$_keys[$name])) {
            $mutex = $this->createMutex();
            $method = new \ReflectionMethod($mutex, 'calculateKey');
            $method->setAccessible(true);
            self::$_keys[$name] = $method->invoke($mutex, $name);
        }
        return self::$_keys[$name];
    }

    protected function assertMutexKeyInRedis()
    {
        $this->assertNotNull(Yii::$app->redis->executeCommand('GET', [$this->getKey(static::$mutexName)]));
    }

    protected function assertMutexKeyNotInRedis()
    {
        $this->assertNull(Yii::$app->redis->executeCommand('GET', [$this->getKey(static::$mutexName)]));
    }
}
