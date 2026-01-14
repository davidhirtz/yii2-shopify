<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Test\Traits;

use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Test\Fixtures\ProductFixture;
use Hirtz\Shopify\Test\Fixtures\ProductImageFixture;
use Hirtz\Shopify\Test\Fixtures\ProductVariantFixture;

trait ShopifyFixtureTrait
{
    public function fixtures(): array
    {
        return [
            'product' => ProductFixture::class,
            'product_image' => ProductImageFixture::class,
            'product_variant' => ProductVariantFixture::class,
        ];
    }

    protected function getProductFixture(): ProductFixture
    {
        /** @var ProductFixture $fixture */
        $fixture = $this->getFixture('product');
        return $fixture;
    }

    protected function getProductFixtureData(string $key): array
    {
        $fixture = $this->getProductFixture();
        return $fixture->data[$key];
    }

    protected function getProductFromFixture(string $key): Product
    {
        return Product::findOne($this->getProductFixtureData($key)['id']);
    }
}
