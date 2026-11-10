<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Tests\Widgets\StructuredData;

use Hirtz\Shopify\Models\Product as ShopifyProduct;
use Hirtz\Shopify\Models\ProductImage;
use Hirtz\Shopify\Models\ProductVariant;
use Hirtz\Shopify\Widgets\StructuredData\Product;
use Hirtz\Skeleton\Test\TestCase;

class ProductTest extends TestCase
{
    public function testAProductWithOneVariantIsOneOffer(): void
    {
        $product = $this->createProduct([
            new ProductVariant(['id' => 1, 'name' => 'Default', 'price' => 1999, 'sku' => 'MUG-1']),
        ]);

        $product->populateRelation('images', [
            new ProductImage(['id' => 1, 'src' => 'https://cdn.shopify.com/mug.jpg']),
            new ProductImage(['id' => 2, 'src' => null]),
        ]);

        self::assertSame([
            '@type' => 'Product',
            'name' => 'Å Tasse 🍵',
            'description' => 'Mu' . "\u{308}" . 'llers Becher & Untersetzer',
            'image' => ['https://cdn.shopify.com/mug.jpg'],
            'brand' => ['@type' => 'Brand', 'name' => 'Töpferei'],
            'url' => 'https://www.test.localhost/mug',
            'offers' => [
                '@type' => 'Offer',
                'sku' => 'MUG-1',
                'price' => '19.99',
                'priceCurrency' => 'EUR',
                'availability' => 'https://schema.org/InStock',
                'url' => 'https://www.test.localhost/mug',
            ],
        ], Product::make()->product($product)->url('https://www.test.localhost/mug')->build());
    }

    public function testEachVariantIsANamedOfferWithItsStock(): void
    {
        $product = $this->createProduct([
            new ProductVariant(['id' => 1, 'name' => 'Klein', 'price' => 1000, 'inventory_tracked' => true, 'inventory_quantity' => 0, 'inventory_policy' => 'DENY']),
            new ProductVariant(['id' => 2, 'name' => 'Mittel', 'price' => 1250, 'inventory_tracked' => true, 'inventory_quantity' => 0, 'inventory_policy' => 'CONTINUE']),
            new ProductVariant(['id' => 3, 'name' => 'Groß', 'price' => 1500, 'inventory_tracked' => false, 'inventory_quantity' => 0]),
        ]);

        $offers = Product::make()->product($product)->build()['offers'] ?? self::fail('No offers');

        self::assertSame(['Klein', 'Mittel', 'Groß'], array_column($offers, 'name'));
        self::assertSame(['10.00', '12.50', '15.00'], array_column($offers, 'price'));
        self::assertSame([
            'https://schema.org/OutOfStock',
            'https://schema.org/InStock',
            'https://schema.org/InStock',
        ], array_column($offers, 'availability'));
    }

    public function testAProductWithoutAVariantOrANameRendersNothing(): void
    {
        self::assertNull(Product::make()->build());
        self::assertNull(Product::make()->product($this->createProduct([]))->build());

        $product = $this->createProduct([new ProductVariant(['id' => 1, 'price' => 100])]);
        $product->name = '';

        self::assertNull(Product::make()->product($product)->build());
    }

    /**
     * @param list<ProductVariant> $variants
     */
    private function createProduct(array $variants): ShopifyProduct
    {
        $product = new ShopifyProduct([
            'name' => 'Å Tasse 🍵',
            'content' => "<p>Mu\u{308}llers <b>Becher</b> &amp;\n Untersetzer</p>",
            'vendor' => 'Töpferei',
            'variant_count' => count($variants),
        ]);

        $product->populateRelation('variants', $variants);
        $product->populateRelation('images', []);

        return $product;
    }
}
