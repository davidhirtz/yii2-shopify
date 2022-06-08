<?php

namespace davidhirtz\yii2\shopify\assets;

use yii\web\AssetBundle;

/**
 * FrontendAssetBundle represents a collection of JS files to use the Shopify JS Buy SDK.
 */
class FrontendAssetBundle extends AssetBundle
{
    /**
     * @var string
     */
    public $basePath = '@shopify/assets/frontend';

    /**
     * @var string[]
     */
    public $js = [
        'js/shopify.js',
        'https://sdks.shopifycdn.com/js-buy-sdk/v2/latest/index.umd.min.js',
    ];
}