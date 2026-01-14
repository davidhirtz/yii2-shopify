<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Tests\Modules\Data;

use Hirtz\Shopify\Modules\Admin\Data\ProductActiveDataProvider;
use Hirtz\Shopify\Test\TestCase;
use Hirtz\Shopify\Test\Traits\ShopifyFixtureTrait;

class ProductActiveDataProviderTest extends TestCase
{
    use ShopifyFixtureTrait;

    public function testProductActiveDataProvider(): void
    {
        $product = $this->getProductFromFixture('product-1');

        $dataProvider = new ProductActiveDataProvider();
        $models = $dataProvider->getModels();

        self::assertNotEmpty($models);
        self::assertCount(2, $models);
        self::assertEquals($product->name, $models[0]->name);
    }
}
