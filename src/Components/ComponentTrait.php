<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Components;

use Yii;
use yii\base\InvalidConfigException;

trait ComponentTrait
{
    /**
     * `Application` validates the component definitions before `Bootstrap` runs and supplies the class, so a
     * project's own `components.shopify` entry that omits it leaves something else registered under the id.
     */
    public static function getShopify(): ShopifyComponent
    {
        $shopify = Yii::$app->get('shopify');

        if (!$shopify instanceof ShopifyComponent) {
            throw new InvalidConfigException('The "shopify" component must be a ' . ShopifyComponent::class . '.');
        }

        return $shopify;
    }
}
