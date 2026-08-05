<?php

declare(strict_types = 1);

use yii\BaseYii;
use yii\redis\Connection;

class Yii extends BaseYii
{
    /**
     * @var BaseApplication
     */
    public static $app;
}

/**
 * @property-read Connection $redis
 */
abstract class BaseApplication extends yii\base\Application
{
}
