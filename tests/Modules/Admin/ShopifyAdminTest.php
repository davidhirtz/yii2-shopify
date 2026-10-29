<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Tests\Modules\Admin;

use Hirtz\Shopify\Components\ComponentTrait;
use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Models\Webhook;
use Hirtz\Shopify\Test\Fixtures\ProductFixture;
use Hirtz\Shopify\Test\Fixtures\ProductImageFixture;
use Hirtz\Shopify\Test\Fixtures\ProductVariantFixture;
use Hirtz\Shopify\Test\TestCase;
use Hirtz\Skeleton\Models\Redirect;
use Hirtz\Skeleton\Models\User;
use Hirtz\Skeleton\Test\Fixtures\UserFixture;
use Override;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * Only the pages that read the local records: everything else in this admin talks to the Shopify API.
 */
class ShopifyAdminTest extends TestCase
{
    use ComponentTrait;
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function fixtures(): array
    {
        return [
            'product' => ProductFixture::class,
            'product_image' => ProductImageFixture::class,
            'product_variant' => ProductVariantFixture::class,
            'user' => UserFixture::class,
        ];
    }

    public function testTheProductIndexListsTheProducts(): void
    {
        $this->login(Product::AUTH_SHOPIFY_PRODUCT);

        $html = Yii::$app->runAction('admin/shopify/product/index');

        self::assertIsString($html);
        self::assertStringContainsString('Sample T-Shirt', $html);
    }

    public function testTheProductIndexFiltersByStatusAndSearch(): void
    {
        $this->login(Product::AUTH_SHOPIFY_PRODUCT);

        $html = Yii::$app->runAction('admin/shopify/product/index', ['q' => 'Sample T-Shirt']);
        self::assertStringContainsString('Sample T-Shirt', $html);

        $html = Yii::$app->runAction('admin/shopify/product/index', ['q' => 'Nothing matches this']);
        self::assertStringNotContainsString('Sample T-Shirt', $html);

        $html = Yii::$app->runAction('admin/shopify/product/index', ['status' => Product::STATUS_DRAFT]);
        self::assertIsString($html);
    }

    public function testTheProductIndexIsForbiddenWithoutThePermission(): void
    {
        $this->getWebUser()->setIdentity($this->getUserFromFixture('admin'));

        $this->expectException(ForbiddenHttpException::class);
        Yii::$app->runAction('admin/shopify/product/index');
    }

    public function testTheDashboardLinksToShopifyOnlyWithAShopifyPermission(): void
    {
        static::getShopify()->shopifyShopName = 'shop-name';
        $this->login(Redirect::AUTH_REDIRECT);

        $html = Yii::$app->runAction('admin/dashboard/index');

        self::assertIsString($html);
        self::assertStringNotContainsString('myshopify.com/admin', $html);
    }

    public function testAWebhookManagerReachesTheDashboard(): void
    {
        static::getShopify()->shopifyShopName = 'shop-name';
        $this->login(Webhook::AUTH_SHOPIFY_WEBHOOK);

        $html = Yii::$app->runAction('admin/dashboard/index');

        self::assertIsString($html);
        self::assertStringContainsString('myshopify.com/admin', $html);
    }

    public function testTheWebhookIndexIsForbiddenWithoutThePermission(): void
    {
        $this->getWebUser()->setIdentity($this->getUserFromFixture('admin'));

        $this->expectException(ForbiddenHttpException::class);
        Yii::$app->runAction('admin/shopify/webhook/index');
    }

    /**
     * Without the secret the page says so rather than showing an empty list as if there were no subscriptions.
     */
    public function testTheWebhookIndexSaysWhenTheApiSecretIsMissing(): void
    {
        $this->login(Webhook::AUTH_SHOPIFY_WEBHOOK);
        static::getShopify()->shopifyApiSecret = null;

        Yii::$app->runAction('admin/shopify/webhook/index');

        self::assertNotEmpty($this->getWebSession()->getFlash('danger'));
    }

    /**
     * Without a shop name, or a token or the key and secret to exchange for one, the page says so instead of failing.
     */
    public function testTheWebhookIndexSaysWhenTheApiIsNotConfigured(): void
    {
        $this->login(Webhook::AUTH_SHOPIFY_WEBHOOK);
        static::getShopify()->shopifyShopName = null;

        $html = Yii::$app->runAction('admin/shopify/webhook/index');

        self::assertIsString($html);
        self::assertStringContainsString('must be set', (string)json_encode($this->getWebSession()->getFlash('danger')));
    }

    public function testTheWebhookIndexLinksToTheProductsOnlyWithTheirPermission(): void
    {
        $this->login(Webhook::AUTH_SHOPIFY_WEBHOOK);
        static::getShopify()->shopifyShopName = null;

        $html = Yii::$app->runAction('admin/shopify/webhook/index');

        self::assertIsString($html);
        self::assertStringNotContainsString('href="/admin/shopify/product/index"', $html);

        $this->login(Product::AUTH_SHOPIFY_PRODUCT);

        $html = Yii::$app->runAction('admin/shopify/webhook/index');

        self::assertIsString($html);
        self::assertStringContainsString('href="/admin/shopify/product/index"', $html);
    }

    public function testTheWebhooksAreNotInstalledWithoutTheApiSecret(): void
    {
        $this->login(Webhook::AUTH_SHOPIFY_WEBHOOK);

        $shopify = static::getShopify();
        $shopify->shopifyShopName = 'shop-name';
        $shopify->shopifyAccessToken = 'token';
        $shopify->shopifyApiSecret = null;

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = $this->getWebRequest();
        $request->setBodyParams([$request->csrfParam => $request->getCsrfToken()]);

        $response = Yii::$app->runAction('admin/shopify/webhook/create');

        self::assertInstanceOf(Response::class, $response);
        self::assertSame([Yii::t('shopify', 'WEBHOOK_SECRET_ERROR')], $this->getWebSession()->getFlash('danger'));
        self::assertNull($this->getWebSession()->getFlash('success'));
    }

    public function testASyncLeadsBackToTheFilteredGrid(): void
    {
        $this->login(Product::AUTH_SHOPIFY_PRODUCT);
        static::getShopify()->shopifyAccessToken = null;
        static::getShopify()->shopifyApiKey = null;

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = $this->getWebRequest();
        $request->setBodyParams([$request->csrfParam => $request->getCsrfToken()]);
        $request->getHeaders()->set('Referer', 'https://www.test.localhost/admin/shopify/product/index?status=3&q=Shirt&page=3');

        foreach (['product/update' => ['id' => 1], 'product/update-all' => []] as $route => $params) {
            $response = Yii::$app->runAction("admin/shopify/$route", $params);

            self::assertInstanceOf(Response::class, $response);
            self::assertStringEndsWith('/admin/shopify/product/index?status=3&q=Shirt&page=3', (string)$response->getHeaders()->get('location'), $route);
        }
    }

    public function testTheApiActionsRedirectWhenTheApiIsNotConfigured(): void
    {
        $this->login(Webhook::AUTH_SHOPIFY_WEBHOOK);
        $this->login(Product::AUTH_SHOPIFY_PRODUCT);

        $shopify = static::getShopify();
        $shopify->shopifyShopName = 'shop-name';
        $shopify->shopifyAccessToken = null;
        $shopify->shopifyApiKey = null;

        self::assertFalse($shopify->isAdminApiConfigured());

        $this->getWebRequest()->enableCsrfValidation = false;
        $method = $_SERVER['REQUEST_METHOD'] ?? null;
        $_SERVER['REQUEST_METHOD'] = 'POST';

        try {
            foreach (['webhook/create' => [], 'webhook/delete' => ['id' => 1], 'product/update' => ['id' => 1], 'product/update-all' => []] as $route => $params) {
                Yii::$app->runAction("admin/shopify/$route", $params);
                self::assertStringContainsString('must be set', (string)json_encode($this->getWebSession()->getFlash('danger', delete: true)), $route);
            }
        } finally {
            $_SERVER['REQUEST_METHOD'] = $method;
        }
    }

    private function login(string $permission): User
    {
        $user = $this->getUserFromFixture('admin');

        $auth = Yii::$app->getAuthManager();
        $auth->assign($auth->getPermission($permission), $user->id);

        $this->getWebUser()->setIdentity($user);

        return $user;
    }

    private function getUserFromFixture(string $key): User
    {
        /** @var UserFixture $fixture */
        $fixture = $this->getFixture('user');

        return User::findOne($fixture->data[$key]['id']);
    }
}
