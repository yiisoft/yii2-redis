<?php

namespace yiiunit\extensions\predis\sentinel;

use Yii;
use yii\base\InvalidConfigException;
use yii\di\Container;
use yii\helpers\ArrayHelper;
use yii\redis\Predis\PredisConnection;

/**
 * This is the base class for all yii framework unit tests.
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public static $params;

    /**
     * Returns a test configuration param from /data/config.php
     *
     * @param string $name params name
     * @param mixed $default default value to use when param is not set.
     * @return mixed  the value of the configuration param
     */
    public static function getParam($name, $default = null)
    {
        if (static::$params === null) {
            static::$params = require(__DIR__ . '/config/config.php');
        }


        return isset(static::$params[$name]) ? static::$params[$name] : $default;
    }

    /**
     * Clean up after test.
     * By default the application created with [[mockApplication]] will be destroyed.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->destroyApplication();
    }

    /**
     * Populates Yii::$app with a new application
     * The application will be destroyed on tearDown() automatically.
     *
     * @param array $config The application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockApplication(array $config = [], $appClass = '\yii\console\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
        ], $config));
    }

    /**
     * Mocks web application
     *
     * @param array $config
     * @param string $appClass
     */
    protected function mockWebApplication(array $config = [], $appClass = '\yii\web\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
            'components' => [
                'request' => [
                    'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                    'scriptFile' => __DIR__ . '/index.php',
                    'scriptUrl' => '/index.php',
                ],
            ],
        ], $config));
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
        Yii::$container = new Container();
    }

    protected function setUp(): void
    {
        $databases = self::getParam('databases');
        $params = isset($databases['redis']) ? $databases['redis'] : null;
        $this->assertNotNull($params, 'No redis server connection configured.');

        $this->mockApplication(['components' => ['redis' => new PredisConnection($params)]]);

        parent::setUp();
    }

    /**
     * @param boolean $reset whether to clean up the test database
     * @return PredisConnection
     * @throws InvalidConfigException
     */
    public function getConnection(bool $reset = true): PredisConnection
    {
        $databases = self::getParam('databases');
        $params = isset($databases['redis']) ? $databases['redis'] : [];
        $db = new PredisConnection($params);
        if ($reset) {
            $db->open();
            $db->flushdb();
        }

        return $db;
    }

    /**
     * Invokes a inaccessible method.
     *
     * @param $object
     * @param $method
     * @param array $args
     * @param bool $revoke whether to make method inaccessible after execution
     * @return mixed
     */
    protected function invokeMethod($object, $method, $args = [], $revoke = true)
    {
        $reflection = new \ReflectionObject($object);
        $method = $reflection->getMethod($method);

        if (\PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $result = $method->invokeArgs($object, $args);
        if ($revoke && \PHP_VERSION_ID < 80100) {
            $method->setAccessible(false);
        }

        return $result;
    }
}
