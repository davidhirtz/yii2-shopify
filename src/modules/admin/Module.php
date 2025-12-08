<?php

declare(strict_types=1);

namespace Hirtz\Shopify\modules\admin;

use Hirtz\Shopify\models\Product;
use Hirtz\Shopify\models\Webhook;
use Hirtz\Shopify\modules\admin\controllers\ProductController;
use Hirtz\Shopify\modules\admin\controllers\WebhookController;
use Hirtz\Skeleton\modules\admin\config\DashboardItemConfig;
use Hirtz\Skeleton\modules\admin\config\DashboardPanelConfig;
use Hirtz\Skeleton\modules\admin\config\MainMenuItemConfig;
use Hirtz\Skeleton\modules\admin\ModuleInterface;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @property \Hirtz\Skeleton\modules\admin\Module $module
 */
class Module extends \Hirtz\Skeleton\base\Module implements ModuleInterface
{
    public $defaultRoute = 'product';

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
