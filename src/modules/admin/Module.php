<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\modules\admin;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\models\WebhookSubscription;
use davidhirtz\yii2\shopify\modules\admin\controllers\ProductController;
use davidhirtz\yii2\shopify\modules\admin\controllers\WebhookController;
use davidhirtz\yii2\skeleton\modules\admin\ModuleInterface;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @property \davidhirtz\yii2\skeleton\modules\admin\Module $module
 */
class Module extends \davidhirtz\yii2\skeleton\base\Module implements ModuleInterface
{
    /**
     * @var string|null the module display name, defaults to "Products"
     */
    public ?string $name = null;
    public $defaultRoute = 'product';

    public function init(): void
    {
        $this->name ??= Yii::t('shopify', 'Products');
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
            'shopify' => [
                'name' => $this->name,
                'items' => [
                    'products' => [
                        'label' => Yii::t('shopify', 'View Products'),
                        'url' => ['/admin/product/index'],
                        'icon' => 'tags',
                        'roles' => [Product::AUTH_PRODUCT_UPDATE],
                    ],
                    'webhooks' => [
                        'label' => Yii::t('shopify', 'View Webhooks'),
                        'url' => ['/admin/shopify-webhook/index'],
                        'icon' => 'satellite-dish',
                        'roles' => [WebhookSubscription::AUTH_WEBHOOK_UPDATE],
                    ],
                ],
            ],
        ];
    }

    public function getNavBarItems(): array
    {
        return [
            'shopify' => [
                'label' => $this->name ?: Yii::t('shopify', 'Products'),
                'icon' => 'tags',
                'url' => ['/admin/product/index'],
                'active' => ['admin/product', 'admin/shopify-webhook'],
                'roles' => [
                    Product::AUTH_PRODUCT_UPDATE,
                    WebhookSubscription::AUTH_WEBHOOK_UPDATE,
                ],
            ],
        ];
    }
}
