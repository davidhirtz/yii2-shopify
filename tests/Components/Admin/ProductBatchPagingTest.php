<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Tests\Components\Admin;

use GuzzleHttp\Psr7\Response;
use Hirtz\Shopify\Components\Admin\AdminApi;
use Hirtz\Shopify\Components\Admin\ProductMediaBatchRepository;
use Hirtz\Shopify\Components\Admin\ProductVariantBatchRepository;
use Hirtz\Shopify\Models\ProductImage;
use Hirtz\Shopify\Models\ProductVariant;
use Hirtz\Shopify\Test\MockAdminApi;
use Hirtz\Shopify\Test\TestCase;
use Hirtz\Shopify\Test\Traits\ShopifyFixtureTrait;
use Override;
use Yii;
use yii\helpers\Json;

/**
 * Variants and media beyond the product's first page are fetched page by page, and the local ones Shopify did not
 * list are deleted only after every page arrived: a failed page reads as the end of the list (#338).
 */
class ProductBatchPagingTest extends TestCase
{
    use ShopifyFixtureTrait;

    private const string THROTTLED = '{"errors":[{"message":"Throttled","extensions":{"code":"THROTTLED"}}]}';

    #[Override]
    protected function tearDown(): void
    {
        Yii::$container->clear(AdminApi::class);
        parent::tearDown();
    }

    public function testAFailedVariantPageDeletesNoVariant(): void
    {
        $this->saveVariants(new Response(200, [], self::THROTTLED));

        self::assertSame([1, 2, 10], $this->getVariantIds());
    }

    public function testTheVariantsOfEveryPageAreKeptAndTheRestDeleted(): void
    {
        $this->saveVariants(new Response(200, [], Json::encode([
            'data' => ['product' => ['variants' => ['edges' => [$this->getVariantEdge(11)]]]],
        ])));

        self::assertSame([10, 11], $this->getVariantIds());
    }

    public function testAFailedMediaPageDeletesNoImage(): void
    {
        $this->saveMedia(new Response(200, [], self::THROTTLED));

        self::assertSame([1, 2, 10], $this->getImageIds());
    }

    public function testTheMediaOfEveryPageAreKeptAndTheRestDeleted(): void
    {
        $this->saveMedia(new Response(200, [], Json::encode([
            'data' => ['product' => ['media' => ['edges' => [$this->getMediaEdge(11)]]]],
        ])));

        self::assertSame([10, 11], $this->getImageIds());
    }

    private function saveVariants(Response $nextPage): void
    {
        Yii::$container->set(AdminApi::class, new MockAdminApi($nextPage));

        (new ProductVariantBatchRepository($this->getProductFromFixture('product-1'), [
            'variants' => ['edges' => [$this->getVariantEdge(10)]],
            'variantsCount' => ['count' => 2],
        ]))->save();
    }

    private function saveMedia(Response $nextPage): void
    {
        Yii::$container->set(AdminApi::class, new MockAdminApi($nextPage));

        (new ProductMediaBatchRepository($this->getProductFromFixture('product-1'), [
            'media' => ['edges' => [$this->getMediaEdge(10)]],
            'mediaCount' => ['count' => 2],
        ]))->save();
    }

    /**
     * @return list<int>
     */
    private function getVariantIds(): array
    {
        return array_map(intval(...), ProductVariant::find()
            ->select('id')
            ->where(['product_id' => 1])
            ->orderBy(['id' => SORT_ASC])
            ->column());
    }

    /**
     * @return list<int>
     */
    private function getImageIds(): array
    {
        return array_map(intval(...), ProductImage::find()
            ->select('id')
            ->where(['product_id' => 1])
            ->orderBy(['id' => SORT_ASC])
            ->column());
    }

    /**
     * @return array<string, mixed>
     */
    private function getVariantEdge(int $id): array
    {
        return [
            'cursor' => "variant-$id",
            'node' => [
                'id' => "gid://shopify/ProductVariant/$id",
                'title' => "Variant $id",
                'price' => '19.99',
                'compareAtPrice' => null,
                'sku' => '',
                'barcode' => '',
                'taxable' => true,
                'inventoryPolicy' => 'DENY',
                'inventoryQuantity' => 1,
                'inventoryItem' => ['tracked' => false],
                'media' => ['nodes' => []],
                'selectedOptions' => [],
                'unitPrice' => null,
                'unitPriceMeasurement' => null,
                'updatedAt' => '2026-09-13T10:00:00Z',
                'createdAt' => '2026-09-13T10:00:00Z',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getMediaEdge(int $id): array
    {
        return [
            'cursor' => "media-$id",
            'node' => [
                'id' => "gid://shopify/MediaImage/$id",
                'preview' => [
                    'image' => [
                        'altText' => null,
                        'height' => 800,
                        'width' => 600,
                        'url' => "https://cdn.shopify.com/$id.jpg",
                    ],
                ],
            ],
        ];
    }
}
