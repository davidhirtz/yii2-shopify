<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\tests\unit\models\queries;

use Codeception\Test\Unit;
use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\tests\support\ProductFixtureTrait;

class ProductQueryTest extends Unit
{
    use ProductFixtureTrait;

    public function testMatchingScope(): void
    {
        $found = Product::find()
            ->matching('Test')
            ->count();

        self::assertEquals(1, $found);

        $found = Product::find()
            ->matching("1")
            ->count();

        self::assertEquals(1, $found);
    }
}
