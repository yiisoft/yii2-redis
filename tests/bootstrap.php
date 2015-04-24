<?php

// ensure we get report on all possible php errors
error_reporting(-1);

define('YII_ENABLE_ERROR_HANDLER', false);
define('YII_DEBUG', true);
$_SERVER['SCRIPT_NAME'] = '/' . __DIR__;
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

Yii::setAlias('@yiiunit/extensions/redis', __DIR__);
Yii::setAlias('@yii/redis', dirname(__DIR__));

if (!class_exists('yiiunit\framework\ar\ActiveRecordTestTrait')) {
    if (is_file(__DIR__ . '/../../../tests/framework/ar/ActiveRecordTestTrait.php')) {
        require_once(__DIR__ . '/../../../tests/framework/ar/ActiveRecordTestTrait.php');
    } elseif (is_file(__DIR__ . '/ActiveRecordTestTrait.php')) {
        require_once(__DIR__ . '/ActiveRecordTestTrait.php');
    } else {
        file_put_contents(__DIR__ . '/ActiveRecordTestTrait.php', file_get_contents('https://raw.githubusercontent.com/yiisoft/yii2/master/tests/framework/ar/ActiveRecordTestTrait.php'));
        require_once(__DIR__ . '/ActiveRecordTestTrait.php');
    }
}

if (!class_exists('yiiunit\TestCase')) {
    if (is_file(__DIR__ . '/../../../tests/TestCase.php')) {
        require_once(__DIR__ . '/../../../tests/TestCase.php');
    } elseif (is_file(__DIR__ . '/BaseTestCase.php')) {
        require_once(__DIR__ . '/BaseTestCase.php');
    } else {
        file_put_contents(__DIR__ . '/BaseTestCase.php', file_get_contents('https://raw.githubusercontent.com/yiisoft/yii2/master/tests/TestCase.php'));
        require_once(__DIR__ . '/BaseTestCase.php');
    }
}

if (!class_exists('yiiunit\framework\caching\CacheTestCase')) {
    if (is_file(__DIR__ . '/../../../tests/framework/caching/CacheTestCase.php')) {
        require_once(__DIR__ . '/../../../tests/framework/caching/CacheTestCase.php');
    } elseif (is_file(__DIR__ . '/CacheTestCase.php')) {
        require_once(__DIR__ . '/CacheTestCase.php');
    } else {
        file_put_contents(__DIR__ . '/CacheTestCase.php', file_get_contents('https://raw.githubusercontent.com/yiisoft/yii2/master/tests/framework/caching/CacheTestCase.php'));
        require_once(__DIR__ . '/CacheTestCase.php');
    }
}
