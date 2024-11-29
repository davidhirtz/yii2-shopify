<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\modules;

use davidhirtz\yii2\shopify\Module;
use Yii;

trait ModuleTrait
{
    protected static ?Module $_module = null;

    public static function getModule(): Module
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('shopify');
        self::$_module ??= $module;

        return self::$_module;
    }
}
