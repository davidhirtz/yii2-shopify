<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Tests\Models;

use Hirtz\Shopify\Models\ProductVariant;
use Hirtz\Shopify\Test\TestCase;
use Yii;

class ProductVariantTest extends TestCase
{
    public function testAFreeVariantHasAPrice(): void
    {
        $variant = ProductVariant::create();
        $variant->price = 0;
        $variant->unit_price = 0;
        $variant->unit_price_measurement = 'KG';

        $zero = Yii::$app->getFormatter()->asCurrency(0, $variant::getShopify()->defaultCurrency);

        self::assertSame($zero, $variant->getFormattedPrice());
        self::assertSame("$zero/KG", $variant->getFormattedUnitPrice());
        self::assertSame('', $variant->getFormattedCompareAtPrice());
    }
}
