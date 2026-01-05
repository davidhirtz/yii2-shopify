<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules;

use Hirtz\Shopify\Module;
use Yii;

trait ModuleTrait
{
    public static function getModule(): Module
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('shopify');
        return $module;
    }
}
