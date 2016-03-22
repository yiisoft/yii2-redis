<?php
/**
 * @link      http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license   http://www.yiiframework.com/license/
 */

namespace yii\redis;

use Yii;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\mutex\Mutex as AbstractMutex;

/**
 * Implements mutex based on Redis.
 * Single instance lock algorithm.
 * http://redis.io/topics/distlock
 *
 * Application configuration example:
 *
 * ```
 *  [
 *      'components' => [
 *          'redis' => [
 *              'class'    => 'yii\redis\Connection',
 *              'hostname' => 'localhost',
 *              'port'     => 6379,
 *              'database' => 0,
 *          ],
 *          'mutex' => [
 *              'class' => 'yii\redis\Mutex',
 *              'redis' => 'redis',
 *          ],
 *      ],
 *  ]
 * ```
 *
 * @see    yii\mutex\Mutex
 *
 * @author Alexander Zhuravlev <axelhex@gmail.com>
 * @since  2.0.6
 */
class Mutex extends AbstractMutex
{
    /**
     * @var Connection|string|array the Redis [[Connection]] object or the application component ID of the Redis
     *      [[Connection]]. This can also be an array that is used to create a redis [[Connection]] instance in case
     *      you do not want do configure redis connection as an application component. After the Session object is
     *      created, if you want to change this property, you should only assign it with a Redis [[Connection]] object.
     */
    public $redis = 'redis';

    /**
     * @var string a string prefixed to every cache key so that it is unique. If not set, it will use a prefix
     *      generated from [[Application::id]]. You may set this property to be an empty string if you don't want to
     *      use key prefix. It is recommended that you explicitly set this property to some static value if the cached
     *      data needs to be shared among multiple applications.
     */
    public $keyPrefix;

    /**
     * @var int redis key expire, ms
     */
    public $lockExpire = 3600000;

    /**
     * @var int sleep ms until next lock try during timeout waiting
     */
    public $lockWaitSleep = 200;

    /**
     * @var array Track redis lock values.
     *            http://redis.io/topics/distlock
     *            "This is important in order to avoid removing a lock that was created by another client."
     *            "Every lock is “signed” with a random string, so the lock will be removed only if it is still the one
     *            that was set by the client trying to remove it."
     *
     */
    private $_lockMap = [];

    /**
     * Initializes the redis Mutex component.
     * This method will initialize the [[redis]] property to make sure it refers to a valid redis connection.
     *
     * @throws InvalidConfigException if [[redis]] is invalid.
     */
    public function init()
    {
        parent::init();
        $this->redis = Instance::ensure($this->redis, Connection::className());

        if ($this->keyPrefix === null) {
            $this->keyPrefix = 'mutex_' . md5(Yii::$app->id) . '_';
        }
    }

    /** @inheritdoc */
    protected function acquireLock($name, $timeout = 0)
    {
        list($lockKey, $lockValue) = $this->addLock($name);

        if (null === $lockKey) {
            return false;
        }

        /**
         * Set lock command
         *
         * @return array|bool|null|string
         */
        $setLock = function () use ($name, $lockKey, $lockValue) {
            return $this->redis->executeCommand('SET', [$lockKey, $lockValue, 'PX', $this->lockExpire, 'NX']);
        };

        if ($setLock()) {
            return true;
        }

        while ($timeout > 0) {
            usleep($this->lockWaitSleep * 1000);
            $timeout -= $this->lockWaitSleep;

            if ($setLock()) {
                return true;
            }
        }

        $this->deleteLock($name);

        return false;
    }

    /** @inheritdoc */
    protected function releaseLock($name)
    {
        list($lockKey, $lockValue) = $this->getLock($name);

        if (null === $lockKey) {
            return false;
        }

        $luaScript = 'if redis.call("GET", KEYS[1]) == ARGV[1] then
                        return redis.call("DEL", KEYS[1])
                    else
                        return 0
                    end';

        if ($this->redis->executeCommand('EVAL', [$luaScript, 1, $lockKey, $lockValue])) {
            $this->deleteLock($name);

            return true;
        }

        return false;
    }

    /**
     * Generates redis key for a lock name.
     *
     * @param string $lockName
     *
     * @return string
     */
    protected function getLockKey($lockName)
    {
        return $this->keyPrefix . $lockName;
    }

    /**
     * Gets lock from the local map
     *
     * @param string $name
     *
     * @return array|null lock's [keyName, keyValue] pair for redis or null if there is no such lock
     */
    protected function getLock($name)
    {
        return isset($this->_lockMap[$name]) ? [$this->getLockKey($name), $this->_lockMap[$name]] : null;
    }

    /**
     * Adds lock to the local map
     *
     * @param string $name
     *
     * @return array|null lock's [keyName, keyValue] pair for redis or null if it's already exists in $lockMap
     */
    protected function addLock($name)
    {
        if (isset($this->_lockMap[$name])) {
            return null;
        }

        $lockValue = Yii::$app->security->generateRandomString();
        $this->_lockMap[$name] = $lockValue;

        return [$this->getLockKey($name), $lockValue];
    }

    /**
     * Deletes lock from the local map
     *
     * @param string $name
     *
     * @return bool
     */
    protected function deleteLock($name)
    {
        unset($this->_lockMap[$name]);
    }
}
