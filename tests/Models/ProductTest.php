<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Tests\Models;

use Hirtz\Shopify\Models\Product;
use Hirtz\Skeleton\Test\TestCase;

class ProductTest extends TestCase
{
    public function testTrailOptions(): void
    {
        $product = new Product();

        self::assertSame(
            ['Size: S, M', 'Color: –'],
            $product->formatTrailAttributeValue('options', [
                ['name' => 'Size', 'values' => ['S', 'M']],
                ['name' => 'Color', 'values' => []],
            ]),
        );
    }

    /**
     * Products synchronised through the REST API, before 2.2, stored their options keyed by name.
     */
    public function testTrailOptionsKeyedByName(): void
    {
        self::assertSame(
            ['Size: S, M', 'Option 2: Red'],
            (new Product())->formatTrailAttributeValue('options', [
                'Size' => ['S', 'M'],
                1 => ['values' => ['Red']],
            ]),
        );
    }
}
