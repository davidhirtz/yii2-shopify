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
use Hirtz\Skeleton\Models\User;
use Hirtz\Skeleton\Test\Fixtures\UserFixture;
use Override;
use Yii;
use yii\web\ForbiddenHttpException;

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
