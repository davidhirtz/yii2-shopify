<?php

declare(strict_types=1);

$basePath = (getenv('BASE_PATH') ?: getcwd());
$config = require("$basePath/vendor/davidhirtz/yii2-skeleton/config/test.php");

// No `bootstrap` key: composer's `extra.bootstrap` reaches every bundle through `vendor/yiisoft/extensions.php`,
// so naming one here would run it a second time and register its event handlers twice.
return [
    ...$config,
    // The credentials are params, which is how a project configures them: `components.shopify` is only given its
    // class by `Bootstrap`, and a component config that names no class is refused before the bootstrap runs.
    'params' => [
        ...$config['params'],
        'shopifyShopName' => 'shop-name',
        'shopifyApiKey' => 'api-key',
        'shopifyApiSecret' => 'api-secret',
        'shopifyAccessToken' => 'access-token',
        'shopifyStorefrontAccessToken' => 'storefront-access-token',
    ],
];
