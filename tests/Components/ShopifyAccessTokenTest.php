<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Tests\Components;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Hirtz\Shopify\Components\ShopifyAccessToken;
use Hirtz\Shopify\Components\ShopifyComponent;
use Hirtz\Shopify\Test\TestCase;
use Override;
use Psr\Http\Message\RequestInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\caching\ArrayCache;

class ShopifyAccessTokenTest extends TestCase
{
    #[Override]
    protected function tearDown(): void
    {
        Yii::$container->clear(ShopifyAccessToken::class);
        parent::tearDown();
    }

    public function testTheGrantIsPostedToTheShop(): void
    {
        $handler = new MockHandler([self::createTokenResponse()]);
        $token = new TestShopifyAccessToken($handler);

        self::assertSame('temporary-token', $token());

        $request = $handler->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);
        self::assertSame('https://shop-name.myshopify.com/admin/oauth/access_token', (string)$request->getUri());

        parse_str((string)$request->getBody(), $body);
        self::assertSame(['client_id' => 'api-key', 'client_secret' => 'api-secret', 'grant_type' => 'client_credentials'], $body);
    }

    public function testTheTokenIsCached(): void
    {
        $cache = new ArrayCache();

        self::assertSame('temporary-token', (new TestShopifyAccessToken(new MockHandler([self::createTokenResponse()]), $cache))());
        self::assertSame('temporary-token', (new TestShopifyAccessToken(new MockHandler([]), $cache))());
    }

    public function testAWriteScopeGrantsTheReadScope(): void
    {
        $token = new TestShopifyAccessToken(new MockHandler([self::createTokenResponse('write_products,read_inventory')]));

        self::assertSame('temporary-token', $token());
    }

    public function testAMissingScopeIsRefused(): void
    {
        $token = new TestShopifyAccessToken(new MockHandler([self::createTokenResponse('read_products')]));

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('read_inventory');

        $token();
    }

    public function testRefusedCredentialsThrow(): void
    {
        $token = new TestShopifyAccessToken(new MockHandler([new Response(401, [], '{"error":"invalid_client"}')]));

        $this->expectException(InvalidConfigException::class);

        $token();
    }

    public function testAStaticTokenIsUsedWithoutAGrant(): void
    {
        Yii::$container->set(ShopifyAccessToken::class, fn () => self::fail('The grant was requested.'));

        (new ShopifyComponent())->getAdminApi();

        $this->expectNotToPerformAssertions();
    }

    public function testTheAdminApiFallsBackToTheGrant(): void
    {
        $handler = new MockHandler([self::createTokenResponse()]);
        Yii::$container->set(ShopifyAccessToken::class, fn (): ShopifyAccessToken => new TestShopifyAccessToken($handler));

        $shopify = new ShopifyComponent();
        $shopify->shopifyAccessToken = '';
        $shopify->getAdminApi();

        self::assertSame(0, $handler->count());
    }

    public function testNeitherATokenNorCredentialsThrows(): void
    {
        $shopify = new ShopifyComponent();
        $shopify->shopifyAccessToken = null;
        $shopify->shopifyApiKey = null;

        $this->expectException(InvalidConfigException::class);

        $shopify->getAdminApi();
    }

    private static function createTokenResponse(string $scope = 'read_products,read_inventory'): Response
    {
        return new Response(200, [], (string)json_encode([
            'access_token' => 'temporary-token',
            'scope' => $scope,
            'expires_in' => 86399,
        ]));
    }
}

class TestShopifyAccessToken extends ShopifyAccessToken
{
    public function __construct(private readonly MockHandler $handler, ?ArrayCache $cache = null)
    {
        parent::__construct('shop-name', 'api-key', 'api-secret', $cache);
    }

    #[Override]
    protected function createClient(): Client
    {
        return new Client(['handler' => HandlerStack::create($this->handler)]);
    }
}
