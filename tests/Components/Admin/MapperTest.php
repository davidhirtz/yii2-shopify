<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Tests\Components\Admin;

use Hirtz\Shopify\Components\Admin\ProductMapper;
use Hirtz\Shopify\Components\Admin\ProductMediaMapper;
use Hirtz\Shopify\Components\Admin\ProductVariantMapper;
use Hirtz\Shopify\Components\Admin\WebhookSubscriptionMapper;
use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Test\TestCase;
use Hirtz\Shopify\Test\Traits\ShopifyFixtureTrait;

/**
 * The mappers turn a GraphQL payload into the records the shop is stored as, so they decide what a sync writes.
 */
class MapperTest extends TestCase
{
    use ShopifyFixtureTrait;

    public function testAProductIsMappedFromItsPayload(): void
    {
        $product = (new ProductMapper($this->getProductData()))();

        self::assertSame(1234567890, $product->id);
        self::assertSame(Product::STATUS_ENABLED, $product->status);
        self::assertSame('A T-Shirt', $product->name);
        self::assertSame('a-t-shirt', $product->slug);
        self::assertSame('<p>Cotton.</p>', $product->content);
        self::assertSame('Clothing', $product->product_type);
        self::assertSame('Acme', $product->vendor);
        self::assertSame(['summer', 'cotton'], $product->tags);

        self::assertSame('2026-09-13 10:00:00', $product->created_at->format('Y-m-d H:i:s'));
    }

    /**
     * A draft is not visible in the shop, and an archived product is gone from it.
     */
    public function testTheShopifyStatusBecomesTheRecordStatus(): void
    {
        foreach (
            [
            'ACTIVE' => Product::STATUS_ENABLED,
            'DRAFT' => Product::STATUS_DRAFT,
            'ARCHIVED' => Product::STATUS_DISABLED,
            ] as $status => $expected
        ) {
            $product = (new ProductMapper($this->getProductData(['status' => $status])))();
            self::assertSame($expected, $product->status, $status);
        }
    }

    /**
     * A shop that sells one variant per product carries Shopify's placeholder option, which is not an option the
     * store should show.
     */
    public function testThePlaceholderOptionIsDropped(): void
    {
        $data = $this->getProductData([
            'options' => [
                ['name' => 'Title', 'position' => 1, 'values' => ['Default Title']],
            ],
        ]);

        self::assertNull((new ProductMapper($data))()->options);
    }

    public function testTheOptionsAreOrderedByTheirPosition(): void
    {
        $data = $this->getProductData([
            'options' => [
                ['name' => 'Color', 'position' => 2, 'values' => ['Red']],
                ['name' => 'Size', 'position' => 1, 'values' => ['S', 'M']],
            ],
        ]);

        $options = (new ProductMapper($data))()->options;

        self::assertSame(['Size', 'Color'], array_column($options, 'name'));
        self::assertSame(['S', 'M'], $options[0]['values']);
    }

    /**
     * A payload for a product that is already stored updates it rather than inserting a second one.
     */
    public function testAKnownProductIsUpdatedInPlace(): void
    {
        $product = $this->getProductFromFixture('product-1');

        $mapped = (new ProductMapper($this->getProductData(['id' => "gid://shopify/Product/$product->id"])))();

        self::assertFalse($mapped->getIsNewRecord());
        self::assertSame($product->id, $mapped->id);
        self::assertSame('A T-Shirt', $mapped->name);
    }

    public function testAVariantIsMappedWithItsPriceInCents(): void
    {
        $product = $this->getProductFromFixture('product-1');
        $product->options = [
            ['name' => 'Size', 'values' => ['Small']],
            ['name' => 'Color', 'values' => ['Red']],
        ];

        $variant = (new ProductVariantMapper($product, $this->getVariantData()))();

        self::assertSame(1999, $variant->price);
        self::assertSame(2499, $variant->compare_at_price);
        self::assertSame('Small / Red', $variant->name);
        self::assertSame('SKU-1', $variant->sku);
        self::assertSame(10, $variant->inventory_quantity);
        self::assertTrue($variant->inventory_tracked);
        self::assertSame('Small', $variant->option_1);
        self::assertSame('Red', $variant->option_2);
        self::assertNull($variant->option_3);
        self::assertSame('KG', $variant->weight_unit);
    }

    public function testAVariantWithoutAComparePriceOrAnImage(): void
    {
        $product = $this->getProductFromFixture('product-1');

        $variant = (new ProductVariantMapper($product, $this->getVariantData([
            'compareAtPrice' => null,
            'media' => ['nodes' => []],
            'sku' => '',
            'barcode' => '',
        ])))();

        self::assertNull($variant->compare_at_price);
        self::assertNull($variant->image_id);
        self::assertNull($variant->sku);
        self::assertNull($variant->barcode);
    }

    public function testTheUnitPriceCarriesItsOwnCurrency(): void
    {
        $product = $this->getProductFromFixture('product-1');

        $variant = (new ProductVariantMapper($product, $this->getVariantData([
            'unitPrice' => ['amount' => '3.99', 'currencyCode' => 'USD'],
            'unitPriceMeasurement' => ['referenceUnit' => 'KILOGRAMS'],
        ])))();

        self::assertSame(399, $variant->unit_price);
        self::assertSame('KILOGRAMS', $variant->unit_price_measurement);
    }

    public function testAnImageIsMappedFromItsPreview(): void
    {
        $product = $this->getProductFromFixture('product-1');

        $image = (new ProductMediaMapper($product, [
            'id' => 'gid://shopify/MediaImage/55',
            'preview' => [
                'image' => [
                    'altText' => 'A t-shirt',
                    'height' => 800,
                    'width' => 600,
                    'url' => 'https://cdn.shopify.com/a.jpg',
                ],
            ],
        ]))();

        self::assertSame(55, $image->id);
        self::assertSame($product->id, $image->product_id);
        self::assertSame('A t-shirt', $image->alt_text);
        self::assertSame(800, $image->height);
        self::assertSame('https://cdn.shopify.com/a.jpg', $image->src);
    }

    public function testAWebhookSubscriptionIsMapped(): void
    {
        $subscription = (new WebhookSubscriptionMapper([
            'id' => 'gid://shopify/WebhookSubscription/99',
            'apiVersion' => ['handle' => '2026-01'],
            'endpoint' => ['callbackUrl' => 'https://www.domain.localhost/shopify/webhook/products-update'],
            'topic' => 'PRODUCTS_UPDATE',
            'updatedAt' => '2026-09-13T10:00:00Z',
            'createdAt' => '2026-09-12T10:00:00Z',
        ]))();

        self::assertSame(99, $subscription->id);
        self::assertSame('2026-01', $subscription->api_version);
        self::assertSame('PRODUCTS_UPDATE', $subscription->topic);
        self::assertStringEndsWith('products-update', $subscription->callbackUrl);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function getProductData(array $overrides = []): array
    {
        return [
            'id' => 'gid://shopify/Product/1234567890',
            'status' => 'ACTIVE',
            'title' => 'A T-Shirt',
            'handle' => 'a-t-shirt',
            'descriptionHtml' => '<p>Cotton.</p>',
            'productType' => 'Clothing',
            'vendor' => 'Acme',
            'tags' => ['summer', 'cotton'],
            'options' => [
                ['name' => 'Size', 'position' => 1, 'values' => ['Small', 'Large']],
            ],
            'updatedAt' => '2026-09-13T10:00:00Z',
            'createdAt' => '2026-09-13T10:00:00Z',
            ...$overrides,
        ];
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function getVariantData(array $overrides = []): array
    {
        return [
            'id' => 'gid://shopify/ProductVariant/5555',
            'title' => 'Small / Red',
            'price' => '19.99',
            'compareAtPrice' => '24.99',
            'sku' => 'SKU-1',
            'barcode' => '123',
            'taxable' => true,
            'inventoryPolicy' => 'DENY',
            'inventoryQuantity' => 10,
            'inventoryItem' => [
                'tracked' => true,
                'measurement' => ['weight' => ['value' => 0.5, 'unit' => 'KILOGRAMS']],
            ],
            'media' => ['nodes' => [['id' => 'gid://shopify/MediaImage/77']]],
            'selectedOptions' => [
                ['name' => 'Size', 'value' => 'Small'],
                ['name' => 'Color', 'value' => 'Red'],
            ],
            'unitPrice' => null,
            'unitPriceMeasurement' => null,
            'updatedAt' => '2026-09-13T10:00:00Z',
            'createdAt' => '2026-09-13T10:00:00Z',
            ...$overrides,
        ];
    }
}
