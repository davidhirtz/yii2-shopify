<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin;

use Hirtz\Skeleton\I18n\Lang;
use Hirtz\Shopify\Modules\Admin\Widgets\Navs\ShopifyNavItem;
use Hirtz\Skeleton\Html\A;
use Hirtz\Skeleton\Modules\Admin\ModuleInterface;
use Hirtz\Skeleton\Widgets\Navs\Nav;
use Hirtz\Skeleton\Widgets\Panels\Dashboard;
use Hirtz\Skeleton\Widgets\Panels\DashboardItem;
use Override;
use Yii;

/**
 * @property \Hirtz\Skeleton\Modules\Admin\Module $module
 */
class Module extends \Hirtz\Skeleton\Base\Module implements ModuleInterface
{
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
            ->label(Lang::t('shopify', 'MODULE_SHOPIFY_DASHBOARD'))
            ->link(fn (A $link) => $link->target('_blank'))
            ->order(60)
            ->url(Yii::$app->get('shopify')->getShopUrl('admin')));
    }
}
