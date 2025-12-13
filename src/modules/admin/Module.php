<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin;

use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Models\Webhook;
use Hirtz\Shopify\Modules\Admin\Controllers\ProductController;
use Hirtz\Shopify\Modules\Admin\Controllers\WebhookController;
use Hirtz\Skeleton\Modules\Admin\Config\DashboardItemConfig;
use Hirtz\Skeleton\Modules\Admin\Config\DashboardPanelConfig;
use Hirtz\Skeleton\Modules\Admin\Config\MainMenuItemConfig;
use Hirtz\Skeleton\Modules\Admin\ModuleInterface;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @property \Hirtz\Skeleton\Modules\Admin\Module $module
 */
class Module extends \Hirtz\Skeleton\Base\Module implements ModuleInterface
{
    public $defaultRoute = 'product';

    #[\Override]
    public function init(): void
    {
        $this->controllerMap = ArrayHelper::merge($this->getCoreControllerMap(), $this->controllerMap);
        parent::init();
    }

    protected function getCoreControllerMap(): array
    {
        return [
            'product' => [
                'class' => ProductController::class,
                'viewPath' => '@shopify/modules/admin/views/product',
            ],
            'shopify-webhook' => [
                'class' => WebhookController::class,
                'viewPath' => '@shopify/modules/admin/views/webhook',
            ],
        ];
    }

    public function getDashboardPanels(): array
    {
        return [
            'shopify' => new DashboardPanelConfig(
                name: $this->getName(),
                items: [
                    'products' => new DashboardItemConfig(
                        label: Yii::t('shopify', 'View Products'),
                        url: ['/admin/product/index'],
                        icon: 'tags',
                        roles: [Product::AUTH_PRODUCT_UPDATE],
                    ),
                    'webhooks' => new DashboardItemConfig(
                        label: Yii::t('shopify', 'View Webhooks'),
                        url: ['/admin/shopify-webhook/index'],
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

    public function getMainMenuItems(): array
    {
        return [
            'shopify' => new MainMenuItemConfig(
                label: $this->getName(),
                url: ['/admin/product/index'],
                icon: 'tags',
                roles: [
                    Product::AUTH_PRODUCT_UPDATE,
                    Webhook::AUTH_WEBHOOK_UPDATE,
                ],
                routes: [
                    'admin/product',
                    'admin/shopify-webhook',
                ],
            ),
        ];
    }
}
