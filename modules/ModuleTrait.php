<?php

namespace davidhirtz\yii2\shopify\modules;

use davidhirtz\yii2\shopify\Module;
use Yii;

/**
 * Trait ModuleTrait
 * @package davidhirtz\yii2\shopify\components
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
            static::$_module = Yii::$app->getModule('shopify');
        }

        return static::$_module;
    }
}