<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\tests\unit\models;

use Codeception\Test\Unit;
use davidhirtz\yii2\shopify\tests\support\ProductFixtureTrait;

class ProductVariantTest extends Unit
{
    use ProductFixtureTrait;

    public function testProductPrices(): void
    {
        $product = $this->tester->getProductFixture();

        self::assertEquals('€19.99', $product->variant->getFormattedPrice());
        self::assertEquals('€24.99', $product->variant->getFormattedCompareAtPrice());
        self::assertEquals('€0.99/KG', $product->variant->getFormattedUnitPrice());
    }

    public function testTrailMethods(): void
    {
        $product = $this->tester->getProductFixture();

        $attributes = [
            'id',
            'product_id',
            'image_id',
            'name',
            'price',
            'compare_at_price',
            'presentment_prices',
            'option_1',
            'option_2',
            'option_3',
            'barcode',
            'sku',
            'is_taxable',
            'weight',
            'weight_unit',
            'inventory_quantity',
            'inventory_tracked',
            'inventory_policy',
            'unit_price',
            'unit_price_measurement',
        ];

        self::assertEquals($attributes, array_values($product->variant->getTrailAttributes()));
        self::assertEquals($product->name, $product->variant->getTrailModelName());
        self::assertEquals('https://test.myshopify.com/admin/products/1', $product->variant->getTrailModelAdminRoute());
    }
}
