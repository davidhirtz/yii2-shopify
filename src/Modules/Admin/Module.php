<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin;

use Hirtz\Shopify\Components\ComponentTrait;
use Hirtz\Shopify\Modules\Admin\Widgets\Navs\ShopifyNavItem;
use Hirtz\Skeleton\Html\A;
use Hirtz\Skeleton\Modules\Admin\ModuleInterface;
use Hirtz\Skeleton\Widgets\Navs\Nav;
use Hirtz\Skeleton\Widgets\Panels\Dashboard;
use Hirtz\Skeleton\Widgets\Panels\DashboardItem;
use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Models\Webhook;
use Override;
use Yii;

/**
 * @property \Hirtz\Skeleton\Modules\Admin\Module $module
 */
class Module extends \Hirtz\Skeleton\Base\Module implements ModuleInterface
{
    use ComponentTrait;
    public $defaultRoute = 'product';

    #[Override]
    public function aside(Nav $nav): Nav
    {
        return $nav->addItem(ShopifyNavItem::make());
    }

    #[Override]
    public function dashboard(Dashboard $dashboard): Dashboard
    {
        return $dashboard->addItem(DashboardItem::make()
            ->icon('brand:shopify')
            ->label(Yii::t('shopify', 'SHOPIFY_DASHBOARD_BUTTON'))
            ->link(fn (A $link) => $link->target('_blank'))
            ->order(60)
            ->roles([Product::AUTH_SHOPIFY_PRODUCT, Webhook::AUTH_SHOPIFY_WEBHOOK])
            ->url(static::getShopify()->getShopUrl('admin')));
    }
}
