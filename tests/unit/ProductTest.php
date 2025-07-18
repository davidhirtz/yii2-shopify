<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\tests\unit;

use Codeception\Test\Unit;
use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\tests\fixtures\ProductFixture;
use davidhirtz\yii2\shopify\tests\fixtures\ProductVariantFixture;
use davidhirtz\yii2\shopify\tests\support\UnitTester;

class ProductTest extends Unit
{
    protected UnitTester $tester;

    public function _fixtures(): array
    {
        $dir = codecept_data_dir();

        return [
            'products' => [
                'class' => ProductFixture::class,
                'dataFile' => $dir . 'products.php',
            ],
            'product_variants' => [
                'class' => ProductVariantFixture::class,
                'dataFile' => $dir . 'product_variants.php',
            ],
        ];
    }

    public function testProductPrices(): void
    {
        /** @var Product $product */
        $product = $this->tester->grabFixture('products', 'product-1');

        self::assertEquals('€19.99', $product->variant->getFormattedPrice());
        self::assertEquals('€24.99', $product->variant->getFormattedCompareAtPrice());
        self::assertEquals('€0.99/KG', $product->variant->getFormattedUnitPrice());
    }
}
