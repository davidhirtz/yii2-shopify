<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\tests\unit\models;

use Codeception\Test\Unit;
use davidhirtz\yii2\shopify\tests\support\ProductFixtureTrait;

class ProductTest extends Unit
{
    use ProductFixtureTrait;

    public function test()
    {

    }

    public function testFormatTrailAttributeValue(): void
    {
        $product = $this->tester->getProductFixture();

        $expected = ['Size: XS, SM, LG, XL'];

        $previousOptions = [
            [
                'name' => 'Size',
                'values' => ['XS', 'SM', 'LG', 'XL'],
            ]
        ];

        self::assertEquals($expected, $product->formatTrailAttributeValue('options', $product->options));
        self::assertEquals($expected, $product->formatTrailAttributeValue('options', $previousOptions));
        self::assertEquals('Test Product', $product->formatTrailAttributeValue('name', $product->name));
    }

    public function testTrailMethods(): void
    {
        $product = $this->tester->getProductFixture();

        $attributes = [
            'id',
            'status',
            'variant_id',
            'image_id',
            'name',
            'content',
            'slug',
            'tags',
            'vendor',
            'product_type',
            'options',
        ];

        self::assertEquals($attributes, array_values($product->getTrailAttributes()));
        self::assertEquals($product->name, $product->getTrailModelName());
    }
}
