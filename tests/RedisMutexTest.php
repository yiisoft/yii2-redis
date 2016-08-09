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

        $this->assertFalse($mutex->release(self::$mutexName));
        $this->assertTrue($mutex->acquire(self::$mutexName));
        $this->assertTrue($mutex->release(self::$mutexName));
        $this->assertFalse($mutex->release(self::$mutexName));
    }

    /**
     * @covers yii\redis\Mutex::acquireLock
     * @covers yii\redis\Mutex::releaseLock
     */
    public function testThatMutexLockIsWorking()
    {
        $mutexOne = $this->createMutex();
        $mutexTwo = $this->createMutex();

        $this->assertTrue($mutexOne->acquire(self::$mutexName));
        $this->assertFalse($mutexTwo->acquire(self::$mutexName));

        $this->assertTrue($mutexOne->release(self::$mutexName));

        $this->assertTrue($mutexTwo->acquire(self::$mutexName));
        $this->assertEquals(2200, $mutexTwo->redis->executeCommand('PTTL', [$this->getKey(self::$mutexName)]));
        $this->assertTrue($mutexOne->acquire(self::$mutexName, 3));

        $this->assertTrue($mutexOne->release(self::$mutexName));
        $this->assertFalse($mutexTwo->release(self::$mutexName));
    }

    /**
     * @covers yii\redis\Mutex::touch
     */
    public function testMutexTouch()
    {
        $mutex = $this->createMutex();

        $this->assertFalse($mutex->release(self::$mutexName));
        $this->assertTrue($mutex->acquire(self::$mutexName));
        $this->assertEquals(2200, $mutex->redis->executeCommand('PTTL', [$this->getKey(self::$mutexName)]));

        $mutex->expire = 1.4;
        $this->assertTrue($mutex->touch(self::$mutexName));
        $this->assertEquals(1400, $mutex->redis->executeCommand('PTTL', [$this->getKey(self::$mutexName)]));
        $this->assertTrue($mutex->release(self::$mutexName));

    }

    protected function setUp() {
        parent::setUp();
        $databases = TestCase::getParam('databases');
        $params = isset($databases['redis']) ? $databases['redis'] : null;
        if ($params === null) {
            $this->markTestSkipped('No redis server connection configured.');
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
            'expire' => 2.2,
            'keyPrefix' => self::$mutexPrefix
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
}
