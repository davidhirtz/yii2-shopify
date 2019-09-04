<?php

namespace davidhirtz\yii2\shop\modules;

use davidhirtz\yii2\shop\Module;
use Yii;

/**
 * Trait ModuleTrait
 * @package davidhirtz\yii2\shop\components
 */
trait ModuleTrait
{
    /**
     * @var Module
     */
    protected static $_module;

    /**
     * @return Module
     */
    public static function getModule()
    {
        if (static::$_module === null) {
            static::$_module = Yii::$app->getModule('shop');
        }

        return static::$_module;
    }
}