<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin;

use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Models\Webhook;
use Hirtz\Shopify\Modules\Admin\Widgets\Navs\ShopifyNavItem;
use Hirtz\Skeleton\Modules\Admin\Config\DashboardItem;
use Hirtz\Skeleton\Modules\Admin\ModuleInterface;
use Hirtz\Skeleton\Modules\Admin\Widgets\Panels\DashboardPanel;
use Hirtz\Skeleton\Widgets\Navs\Nav;
use Yii;

/**
 * @property \Hirtz\Skeleton\Modules\Admin\Module $module
 */
class Module extends \Hirtz\Skeleton\Base\Module implements ModuleInterface
{
    public $defaultRoute = 'product';

    public function getDashboardPanels(): array
    {
        return [
            'shopify' => new DashboardPanel(
                name: Yii::t('shopify', 'Shopify'),
                items: [
                    'products' => new DashboardItem(
                        label: Yii::t('shopify', 'View Products'),
                        url: ['/admin/shopify/product/index'],
                        icon: 'tags',
                        roles: [Product::AUTH_PRODUCT_UPDATE],
                    ),
                    'webhooks' => new DashboardItem(
                        label: Yii::t('shopify', 'View Webhooks'),
                        url: ['/admin/shopify/webhook/index'],
                        icon: 'satellite-dish',
                        roles: [Webhook::AUTH_WEBHOOK_UPDATE],
                    ),
                ]
            ),
        ];
    }

    protected function getName(): string
    {
        return Yii::t('shopify', 'Products');
    }

    public function aside(Nav $nav): Nav
    {
        return $nav->addItem(ShopifyNavItem::make());
    }
}
