<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Tests\Modules\Admin\Widgets\Navs;

use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Models\Webhook;
use Hirtz\Shopify\Modules\Admin\Widgets\Navs\ShopifyNavItem;
use Hirtz\Shopify\Test\TestCase;
use Hirtz\Skeleton\Test\Traits\UserFixtureTrait;

class ShopifyNavItemTest extends TestCase
{
    use UserFixtureTrait;

    public function testItLeadsToTheProducts(): void
    {
        $this->loginWith(Product::AUTH_SHOPIFY_PRODUCT);

        self::assertStringStartsWith('<li class="nav-item"><a class="nav-link" href="/admin/shopify/product/index">', ShopifyNavItem::make()->render());
    }

    public function testItLeadsToTheWebhooksOfAUserWhoMayOnlyOpenThose(): void
    {
        $this->loginWith(Webhook::AUTH_SHOPIFY_WEBHOOK);

        self::assertStringStartsWith('<li class="nav-item"><a class="nav-link" href="/admin/shopify/webhook/index">', ShopifyNavItem::make()->render());
    }

    private function loginWith(string $permission): void
    {
        $user = $this->getUserFromFixture('admin');
        $this->assignPermission($user->id, $permission);
        $this->getWebUser()->setIdentity($user);
    }
}
