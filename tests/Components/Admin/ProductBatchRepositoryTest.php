<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Tests\Components\Admin;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Hirtz\Shopify\Components\Admin\AdminApi;
use Hirtz\Shopify\Components\Admin\ProductBatchRepository;
use Hirtz\Shopify\Components\ShopifyComponent;
use Hirtz\Shopify\Models\Product;
use Hirtz\Skeleton\Test\TestCase;
use Override;
use Yii;

class ProductBatchRepositoryTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $shopify = Yii::$app->get('shopify');
        self::assertInstanceOf(ShopifyComponent::class, $shopify);

        $shopify->shopifyShopName = 'shop-name';
        $shopify->shopifyAccessToken = 'access-token';

        foreach ([1, 2] as $id) {
            Yii::$app->getDb()->createCommand()->insert(Product::tableName(), [
                'id' => $id,
                'name' => "Product $id",
                'slug' => "product-$id",
                'last_import_at' => '2026-01-01 00:00:00',
                'created_at' => '2026-01-01 00:00:00',
            ])->execute();
        }
    }

    #[Override]
    protected function tearDown(): void
    {
        Yii::$container->clear(AdminApi::class);
        parent::tearDown();
    }

    public function testAConnectionFailureDeletesNothing(): void
    {
        $this->import(new ConnectException('Could not resolve host', new Request('POST', 'graphql.json')));

        self::assertSame(2, (int)Product::find()->count());
    }

    public function testAThrottledQueryDeletesNothing(): void
    {
        $this->import(new Response(200, [], '{"errors":[{"message":"Throttled","extensions":{"code":"THROTTLED"}}]}'));

        self::assertSame(2, (int)Product::find()->count());
    }

    public function testAnEmptyShopDeletesEveryProduct(): void
    {
        $this->import(new Response(200, [], '{"data":{"products":{"edges":[]}}}'));

        self::assertSame(0, (int)Product::find()->count());
    }

    private function import(Response|ConnectException $response): void
    {
        Yii::$container->set(AdminApi::class, new ProductBatchAdminApi(new MockHandler([$response])));
        (new ProductBatchRepository())->save();
    }
}

class ProductBatchAdminApi extends AdminApi
{
    public function __construct(private readonly MockHandler $handler)
    {
        parent::__construct('shop-name', 'access-token', '2026-07');
    }

    #[Override]
    protected function createClient(): Client
    {
        return new Client(['handler' => HandlerStack::create($this->handler)]);
    }
}
