<?php

namespace davidhirtz\yii2\shopify\modules\admin;

use Yii;

/**
 * Class Module
 * @package davidhirtz\yii2\shopify\modules\admin
 * @property \davidhirtz\yii2\skeleton\modules\admin\Module $module
 */
class Module extends \yii\base\Module
{
    /**
     * @var string the module display name, defaults to "Products"
     */
    public $name;

    /**
     * @var array containing the admin menu items
     */
    public $navbarItems = [];

    /**
     * @var array containing the panel items
     */
    public $panels = [];

    /**
     * @var string
     */
    public $defaultRoute = 'product';

    /**
     * @var array
     */
    protected $defaultControllerMap = [
        'product' => [
            'class' => 'davidhirtz\yii2\shopify\modules\admin\controllers\ProductController',
            'viewPath' => '@shopify/modules/admin/views/product',
        ],
        'shopify-webhook' => [
            'class' => 'davidhirtz\yii2\shopify\modules\admin\controllers\WebhookController',
            'viewPath' => '@shopify/modules/admin/views/webhook',
        ],
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!Yii::$app->getRequest()->getIsConsoleRequest()) {
            if (Yii::$app->getUser()->can('admin')) {
                if (!$this->navbarItems) {
                    $this->navbarItems = [
                        'shopify' => [
                            'label' => $this->name ?: Yii::t('shopify', 'Products'),
                            'icon' => 'shopping-cart',
                            'url' => ['/admin/product/index'],
                            'active' => ['admin/product', 'shopify/'],
                        ]
                    ];
                }

                if (!$this->panels) {
                    $this->panels = [
                        'shopify' => [
                            'name' => $this->name ?: Yii::t('shopify', 'Products'),
                            'items' => [
                                [
                                    'label' => Yii::t('shopify', 'View All Products'),
                                    'url' => ['/admin/product/index'],
                                    'icon' => 'shopping-cart',
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