<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Tests\Modules\Controllers;

use GuzzleHttp\Psr7\Response;
use Hirtz\Shopify\Components\Admin\AdminApi;
use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Test\MockAdminApi;
use Hirtz\Shopify\Test\TestCase;
use Hirtz\Skeleton\Test\Traits\FunctionalTestTrait;
use Hirtz\Skeleton\Test\Traits\UserFixtureTrait;
use Hirtz\Skeleton\Db\DateTime;
use Override;
use Yii;

class ProductControllerFunctionTest extends TestCase
{
    use FunctionalTestTrait;
    use UserFixtureTrait;

    public function testIndexAsGuest(): void
    {
        $this->open('/admin/shopify/product/index');
        self::assertCurrentUrlEquals('https://www.test.localhost/admin/account/login');
    }

    public function testIndexWithoutPermission(): void
    {
        $user = $this->getUserFromFixture('admin');
        $this->getWebUser()->login($user);

        $this->open('/admin/shopify/product/index');
        self::assertResponseStatusCodeSame(403);
    }

    public function testIndexWithPermission(): void
    {
        $user = $this->getUserFromFixture('admin');
        $this->assignAdminRole($user->id);

        $this->getWebUser()->login($user);

        $this->open('/admin/shopify/product/index');
        self::assertResponseIsSuccessful();
    }

    /**
     * Only a product Shopify answers as gone may be deleted: deleting it disables the entry showing it.
     */
    public function testAFailedRefreshKeepsTheProduct(): void
    {
        $product = $this->createProduct();
        $this->postUpdate($product->id, new Response(200, [], '{"errors":[{"message":"Throttled"}]}'));

        self::assertNotNull(Product::findOne($product->id));
        self::assertAnySelectorTextContains('[data-alert="danger"]', 'Throttled');
    }

    public function testARefreshOfAProductShopifyNoLongerHasDeletesIt(): void
    {
        $product = $this->createProduct();
        $this->postUpdate($product->id, new Response(200, [], '{"data":{"product":null}}'));

        self::assertNull(Product::findOne($product->id));
    }

    private function createProduct(): Product
    {
        $product = Product::create();
        $product->id = 1;
        $product->name = 'Product';
        $product->slug = 'product';
        $product->last_import_at = $product->created_at = new DateTime();
        self::assertTrue($product->insert(false), print_r($product->getErrors(), true));

        return $product;
    }

    private function postUpdate(int|string $id, Response $response): void
    {
        Yii::$container->set(AdminApi::class, new MockAdminApi($response));

        $user = $this->getUserFromFixture('admin');
        $this->assignAdminRole($user->id);
        $this->getWebUser()->login($user);

        $this->open('/admin/shopify/product/index');
        $request = $this->getWebRequest();

        self::$crawler = self::$client->request('POST', "https://www.test.localhost/admin/shopify/product/update?id=$id", [
            $request->csrfParam => $request->getCsrfToken(),
        ]);
    }

    #[Override]
    protected function tearDown(): void
    {
        Yii::$container->clear(AdminApi::class);
        parent::tearDown();
    }
}
