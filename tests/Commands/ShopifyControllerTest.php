<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Tests\Commands;

use GuzzleHttp\Psr7\Response;
use Hirtz\Shopify\Commands\ShopifyController;
use Hirtz\Shopify\Components\Admin\AdminApi;
use Hirtz\Shopify\Components\ShopifyComponent;
use Hirtz\Shopify\Test\MockAdminApi;
use Hirtz\Skeleton\Helpers\FileHelper;
use Hirtz\Skeleton\Test\TestCase;
use Hirtz\Skeleton\Test\Traits\StdOutBufferControllerTrait;
use Override;
use Yii;
use yii\console\ExitCode;

class ShopifyControllerTest extends TestCase
{
    private string $configPath = '@runtime/shopify-config';

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        FileHelper::createDirectory($this->configPath);
        Yii::$container->set(AdminApi::class, StorefrontAdminApi::class);
        StorefrontAdminApi::$result = [];
        StorefrontAdminApi::$variables = [];

        $shopify = $this->getShopify();
        $shopify->shopifyShopName = 'shop-name';
        $shopify->shopifyAccessToken = 'access-token';
        $shopify->shopifyStorefrontAccessToken = 'storefront-access-token';
    }

    #[Override]
    protected function tearDown(): void
    {
        FileHelper::removeDirectory($this->configPath);
        Yii::$container->clear(AdminApi::class);
        parent::tearDown();
    }

    public function testStorefrontAccessTokenIsSaved(): void
    {
        StorefrontAdminApi::$result = [
            'storefrontAccessTokenCreate' => [
                'storefrontAccessToken' => ['accessToken' => 'new-token', 'title' => 'App'],
                'userErrors' => [],
            ],
        ];

        $controller = $this->createController();
        $controller->interactive = false;
        $controller->actionStorefrontAccessToken();

        self::assertSame(['input' => ['title' => Yii::$app->name]], StorefrontAdminApi::$variables);
        self::assertStringContainsString('Shopify storefront access token saved.', $controller->flushStdOutBuffer());
        self::assertSame('new-token', $this->getConfigFile()['shopifyStorefrontAccessToken'] ?? null);
        self::assertSame('new-token', Yii::$app->params['shopifyStorefrontAccessToken'] ?? null);
        self::assertSame('new-token', $this->getShopify()->shopifyStorefrontAccessToken);
    }

    public function testAnExistingStorefrontAccessTokenIsKeptUnlessConfirmed(): void
    {
        $controller = $this->createController();
        $controller->interactive = true;
        $controller->actionStorefrontAccessToken();

        self::assertStringContainsString('overwrite the existing storefront access token', $controller->flushStdOutBuffer());
        self::assertSame([], StorefrontAdminApi::$variables);
        self::assertFileDoesNotExist((string)Yii::getAlias("$this->configPath/params.php"));
    }

    public function testStorefrontAccessTokenUserErrors(): void
    {
        StorefrontAdminApi::$result = [
            'storefrontAccessTokenCreate' => [
                'storefrontAccessToken' => null,
                'userErrors' => [['field' => ['input'], 'message' => 'Access denied']],
            ],
        ];

        $this->getShopify()->shopifyStorefrontAccessToken = null;

        $controller = $this->createController();
        $controller->actionStorefrontAccessToken();

        $output = $controller->flushStdOutBuffer();

        self::assertStringContainsString('Access denied', $output);
        self::assertStringContainsString('Failed to create storefront access token.', $output);
        self::assertFileDoesNotExist((string)Yii::getAlias("$this->configPath/params.php"));
    }

    /**
     * The errors are only printed: without an exit code a failing cron job would never alert.
     */
    public function testAFailedImportExitsWithAnError(): void
    {
        $this->mockAdminApi(new Response(200, [], '{"errors":[{"message":"Throttled"}]}'));

        $controller = $this->createController();
        $controller->interactive = false;

        self::assertSame(ExitCode::UNSPECIFIED_ERROR, $controller->runAction('import'));
        self::assertStringContainsString('Throttled', $controller->flushStdOutBuffer());
    }

    public function testAFailedWebhookSubscriptionIsReported(): void
    {
        $this->mockAdminApi(new Response(200, [], '{"errors":[{"message":"Access denied for webhookSubscriptionCreate field."}]}'));

        $controller = $this->createController();

        self::assertSame(ExitCode::UNSPECIFIED_ERROR, $controller->actionWebhookCreate('PRODUCTS_UPDATE', 'https://www.test.localhost/'));
        self::assertStringContainsString('Access denied', $controller->flushStdOutBuffer());
    }

    private function mockAdminApi(Response ...$responses): void
    {
        Yii::$container->set(AdminApi::class, new MockAdminApi(...$responses));
    }

    /**
     * @return array<string, mixed>
     */
    private function getConfigFile(): array
    {
        return require (string)Yii::getAlias("$this->configPath/params.php");
    }

    private function getShopify(): ShopifyComponent
    {
        $shopify = Yii::$app->get('shopify');
        self::assertInstanceOf(ShopifyComponent::class, $shopify);

        return $shopify;
    }

    private function createController(): TestShopifyController
    {
        return new TestShopifyController('shopify', Yii::$app, [
            'config' => "$this->configPath/params.php",
        ]);
    }
}

class TestShopifyController extends ShopifyController
{
    use StdOutBufferControllerTrait;
}

class StorefrontAdminApi extends AdminApi
{
    /**
     * @var array<string, mixed>
     */
    public static array $result = [];

    /**
     * @var array<string, mixed>
     */
    public static array $variables = [];

    #[Override]
    public function query(string $query, array $variables = []): array
    {
        self::$variables = $variables;
        return self::$result;
    }
}
