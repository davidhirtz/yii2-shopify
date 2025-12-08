<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\modules\admin;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\models\Webhook;
use davidhirtz\yii2\shopify\modules\admin\controllers\ProductController;
use davidhirtz\yii2\shopify\modules\admin\controllers\WebhookController;
use davidhirtz\yii2\skeleton\modules\admin\config\DashboardItemConfig;
use davidhirtz\yii2\skeleton\modules\admin\config\DashboardPanelConfig;
use davidhirtz\yii2\skeleton\modules\admin\config\MainMenuItemConfig;
use davidhirtz\yii2\skeleton\modules\admin\ModuleInterface;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @property \davidhirtz\yii2\skeleton\modules\admin\Module $module
 */
class Module extends \davidhirtz\yii2\skeleton\base\Module implements ModuleInterface
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
