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
        $this->assertMutexKeyInRedisExistsOrNot(false);

        $this->assertTrue($mutex->acquire(static::$mutexName));
        $this->assertMutexKeyInRedisExistsOrNot(true);
        $this->assertTrue($mutex->release(static::$mutexName));
        $this->assertMutexKeyInRedisExistsOrNot(false);

        $this->assertFalse($mutex->release(static::$mutexName));
        $this->assertMutexKeyInRedisExistsOrNot(false);
    }

    /**
     * @covers \yii\redis\Mutex::acquireLock
     * @covers \yii\redis\Mutex::releaseLock
     */
    public function testThatMutexLockIsWorking()
    {
        $mutexOne = $this->createMutex();
        $mutexTwo = $this->createMutex();

        $this->assertMutexKeyInRedisExistsOrNot(false);
        $this->assertTrue($mutexOne->acquire(static::$mutexName));
        $this->assertMutexKeyInRedisExistsOrNot(true);
        $this->assertFalse($mutexTwo->acquire(static::$mutexName));
        $this->assertMutexKeyInRedisExistsOrNot(true);

        $this->assertTrue($mutexOne->release(static::$mutexName));
        $this->assertMutexKeyInRedisExistsOrNot(false);

        $this->assertTrue($mutexTwo->acquire(static::$mutexName));
        $this->assertMutexKeyInRedisExistsOrNot(true);
        $this->assertLessThanOrEqual(2500, $mutexTwo->redis->executeCommand('PTTL', [$this->getKey(static::$mutexName)]));

        $this->assertFalse($mutexOne->acquire(static::$mutexName, 2));

        $this->assertTrue($mutexOne->acquire(static::$mutexName, 3));
        $this->assertMutexKeyInRedisExistsOrNot(true);

        $this->assertTrue($mutexOne->release(static::$mutexName));
        $this->assertMutexKeyInRedisExistsOrNot(false);
        $this->assertFalse($mutexTwo->release(static::$mutexName));
        $this->assertMutexKeyInRedisExistsOrNot(false);
    }

    public function testExpire()
    {
        $mutex = $this->createMutex();

        $this->assertTrue($mutex->acquire(static::$mutexName));
        $this->assertMutexKeyInRedisExistsOrNot(true);

        sleep(3);

        $this->assertMutexKeyInRedisExistsOrNot(false);
        $this->assertFalse($mutex->release(static::$mutexName));
        $this->assertMutexKeyInRedisExistsOrNot(false);
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

    protected function assertMutexKeyInRedisExistsOrNot($exists)
    {
        $value = Yii::$app->redis->executeCommand('GET', [$this->getKey(static::$mutexName)]);
        if ($exists) {
            $this->assertNotNull($value);
        } else {
            $this->assertNull($value);
        }
    }
}
