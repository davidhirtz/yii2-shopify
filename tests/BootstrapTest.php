<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Tests;

use Hirtz\Shopify\Test\TestCase;
use Yii;

class BootstrapTest extends TestCase
{
    public function testTheCredentialsAreSensitiveParams(): void
    {
        foreach (['shopifyAccessToken', 'shopifyApiKey', 'shopifyApiSecret', 'shopifyStorefrontAccessToken'] as $param) {
            self::assertContains($param, Yii::$app->sensitiveParams);
        }
    }
}
