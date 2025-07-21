<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\tests\support;

use davidhirtz\yii2\shopify\tests\fixtures\ProductFixture;
use davidhirtz\yii2\shopify\tests\fixtures\ProductVariantFixture;

trait ProductFixtureTrait
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

}