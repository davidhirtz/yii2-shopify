<?php

namespace davidhirtz\yii2\shop\modules\admin;

use Yii;

/**
 * Class Module
 * @package davidhirtz\yii2\shop\modules\admin
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
            'class' => 'davidhirtz\yii2\shop\modules\admin\controllers\ProductController',
            'viewPath' => '@shop/modules/admin/views/product',
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
                        [
                            'label' => $this->name ?: Yii::t('shop', 'Products'),
                            'icon' => 'shopping-cart',
                            'url' => ['/admin/product/index'],
                            'active' => ['admin/product', 'shop/'],
                        ]
                    ];
                }

                if (!$this->panels) {
                    $this->panels = [
                        [
                            'name' => $this->name ?: Yii::t('shop', 'Products'),
                            'items' => [
                                [
                                    'label' => Yii::t('shop', 'View All Products'),
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