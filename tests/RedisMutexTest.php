<?php
namespace yiiunit\extensions\redis;

use Yii;
use yii\helpers\ArrayHelper;
use yii\redis\Connection;
use yii\redis\Mutex;


/**
 * Tests for Redis mutex
 *
 * @group redis
 * @group mutex
 */
class RedisMutexTest extends TestCase
{
    /**
     * @param Connection $connection
     * @param array $params
     *
     * @return Mutex
     * @throws \yii\base\InvalidConfigException
     */
    protected function getMutexInstance(Connection $connection, array $params = [])
    {
        return Yii::createObject(ArrayHelper::merge($params, [
            'class' => Mutex::className(),
            'redis' => $connection,
        ]));
    }

    public function testAcquireLock()
    {
        $lockKeyPrefix = 'foo_';
        $testLockName = 'the_lock';

        $connection = $this->getConnection(true);
        $mutex = $this->getMutexInstance($connection, [
            'keyPrefix' => $lockKeyPrefix,
        ]);

        $this->assertTrue($mutex->acquire($testLockName));
        $this->assertNotNull($connection->get($lockKeyPrefix . $testLockName));
    }

    public function testReleaseLock()
    {
        $lockKeyPrefix = 'bar_';
        $testLockName = 'the_lock';

        $connection = $this->getConnection(true);
        $mutex = $this->getMutexInstance($connection, [
            'keyPrefix' => $lockKeyPrefix,
        ]);

        $mutex->acquire($testLockName);
        $this->assertTrue($mutex->release($testLockName));
        $this->assertNull($connection->get($lockKeyPrefix . $testLockName));
    }

    public function testConcurrentAcquireLock()
    {
        $testLockName = 'the_lock';

        $connection = $this->getConnection(true);

        $mutex1 = $this->getMutexInstance($connection);
        $mutex2 = $this->getMutexInstance($connection);

        $mutex1->acquire($testLockName);
        $this->assertFalse($mutex2->acquire($testLockName));

        $mutex1->release($testLockName);
        $this->assertTrue($mutex2->acquire($testLockName));
    }

    public function testConcurrentReleaseLock()
    {
        $testLockName = 'the_lock';

        $connection = $this->getConnection(true);

        $mutex1 = $this->getMutexInstance($connection);
        $mutex2 = $this->getMutexInstance($connection);

        $mutex1->acquire($testLockName);
        $this->assertFalse($mutex2->release($testLockName));
    }

    public function testLockTimeoutSuccess()
    {
        $testLockName = 'the_lock';

        $connection = $this->getConnection(true);

        $mutex1 = $this->getMutexInstance($connection, [
            'lockExpire' => 100,
        ]);
        $mutex2 = $this->getMutexInstance($connection);

        $mutex1->acquire($testLockName);
        $this->assertTrue($mutex2->acquire($testLockName, 300));
    }

    public function testLockTimeoutFailure()
    {
        $testLockName = 'the_lock';

        $connection = $this->getConnection(true);

        $mutex1 = $this->getMutexInstance($connection, [
            'lockExpire' => 10000,
        ]);
        $mutex2 = $this->getMutexInstance($connection);

        $mutex1->acquire($testLockName);
        $this->assertFalse($mutex2->acquire($testLockName, 200));
    }

    public function testLockExpire()
    {
        $testLockName = 'the_lock';
        $lockKeyPrefix = 'foo_';

        $connection = $this->getConnection(true);

        $mutex = $this->getMutexInstance($connection, [
            'lockExpire' => 100,
            'keyPrefix' => $lockKeyPrefix,
        ]);

        $mutex->acquire($testLockName);
        usleep(300000);
        $this->assertFalse($mutex->release($testLockName));
        $this->assertNull($connection->get($lockKeyPrefix . $testLockName));
    }

    public function testAcquireExistingLock()
    {
        $lockKeyPrefix = 'foo_';
        $testLockName = 'the_lock';

        $connection = $this->getConnection(true);
        $mutex = $this->getMutexInstance($connection, [
            'keyPrefix' => $lockKeyPrefix,
        ]);

        $mutex->acquire($testLockName);

        $this->assertFalse($mutex->acquire($testLockName));
    }

    public function testReleaseNonExistingLock()
    {
        $lockKeyPrefix = 'foo_';
        $testLockName = 'the_lock';

        $connection = $this->getConnection(true);
        $mutex = $this->getMutexInstance($connection, [
            'keyPrefix' => $lockKeyPrefix,
        ]);

        $mutex->acquire($testLockName);
        $mutex->release($testLockName);

        $this->assertFalse($mutex->release($testLockName));
    }
}
