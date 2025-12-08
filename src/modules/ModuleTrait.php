<?php

declare(strict_types=1);

namespace Hirtz\Shopify\modules;

use Hirtz\Shopify\Module;
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
