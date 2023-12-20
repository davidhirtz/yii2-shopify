<?php

namespace davidhirtz\yii2\shopify\modules\admin;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\models\Webhook;
use davidhirtz\yii2\shopify\modules\admin\controllers\ProductController;
use davidhirtz\yii2\shopify\modules\admin\controllers\WebhookController;
use Yii;

/**
 * @property \davidhirtz\yii2\skeleton\modules\admin\Module $module
 */
class Module extends \yii\base\Module
{
    /**
     * @var string|null the module display name, defaults to "Products"
     */
    public ?string $name = null;

    /**
     * @var array containing the admin menu items
     */
    public array $navbarItems = [];

    /**
     * @var array containing the panel items
     */
    public array $panels = [];

    public $defaultRoute = 'product';

    protected array $defaultControllerMap = [
        'product' => [
            'class' => ProductController::class,
            'viewPath' => '@shopify/modules/admin/views/product',
        ],
        'shopify-webhook' => [
            'class' => WebhookController::class,
            'viewPath' => '@shopify/modules/admin/views/webhook',
        ],
    ];

    public function init(): void
    {
        if (!Yii::$app->getRequest()->getIsConsoleRequest()) {
            if (Yii::$app->getUser()->can('admin')) {
                if (!$this->navbarItems) {
                    $this->navbarItems = [
                        'shopify' => [
                            'label' => $this->name ?: Yii::t('shopify', 'Products'),
                            'icon' => 'tags',
                            'url' => ['/admin/product/index'],
                            'active' => ['admin/product', 'admin/shopify-webhook'],
                            'roles' => [
                                Product::AUTH_PRODUCT_UPDATE,
                                Webhook::AUTH_WEBHOOK_UPDATE,
                            ],
                        ],
                    ];
                }

                if (!$this->panels) {
                    $this->panels = [
                        'shopify' => [
                            'name' => $this->name ?: Yii::t('shopify', 'Products'),
                            'items' => [
                                [
                                    'label' => Yii::t('shopify', 'View Products'),
                                    'url' => ['/admin/product/index'],
                                    'icon' => 'tags',
                                    'roles' => [Product::AUTH_PRODUCT_UPDATE],
                                ],
                                [
                                    'label' => Yii::t('shopify', 'View Webhooks'),
                                    'url' => ['/admin/shopify-webhook/index'],
                                    'icon' => 'satellite-dish',
                                    'roles' => [Webhook::AUTH_WEBHOOK_UPDATE],
                                ],
                            ],
                        ],
                    ];
                }
            }

            $this->module->navbarItems = array_merge($this->module->navbarItems, $this->navbarItems);
            $this->module->panels = array_merge($this->module->panels, $this->panels);
        }

        $this->module->controllerMap = array_merge($this->module->controllerMap, $this->defaultControllerMap, $this->controllerMap);

        parent::init();
    }
}