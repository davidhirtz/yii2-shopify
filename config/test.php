<?php

declare(strict_types=1);

use Hirtz\Shopify\Bootstrap;

$basePath = (getenv('BASE_PATH') ?: getcwd());
$config = require("$basePath/vendor/davidhirtz/yii2-skeleton/config/test.php");

return [
    ...$config,
    'bootstrap' => [
        Bootstrap::class,
    ],
    'modules' => [
        'shopify' => [
            'shopifyShopName' => 'shop-name',
            'shopifyApiKey' => 'api-key',
            'shopifyApiSecret' => 'api-secret',
            'shopifyAccessToken' => 'access-token',
            'shopifyStorefrontAccessToken' => 'storefront-access-token',
        ],
    ],
];
