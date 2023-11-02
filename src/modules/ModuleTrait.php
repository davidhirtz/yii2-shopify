<?php

namespace davidhirtz\yii2\shopify\modules;

use davidhirtz\yii2\shopify\Module;
use Yii;

trait ModuleTrait
{
    protected static ?Module $_module = null;

    public static function getModule(): Module
    {
        static::$_module ??= Yii::$app->getModule('shopify');
        return static::$_module;
    }
}