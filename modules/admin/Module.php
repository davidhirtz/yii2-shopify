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
    public $defaultRoute = 'entry';

    /**
     * @var array
     */
    protected $defaultControllerMap = [
        'entry' => [
            'class' => 'davidhirtz\yii2\shop\modules\admin\controllers\ProductController',
            'viewPath' => '@shop/modules/admin/views/entry',
        ],
        'asset' => [
            'class' => 'davidhirtz\yii2\shop\modules\admin\controllers\AssetController',
            'viewPath' => '@shop/modules/admin/views/asset',
        ],
        'section' => [
            'class' => 'davidhirtz\yii2\shop\modules\admin\controllers\SectionController',
            'viewPath' => '@shop/modules/admin/views/section',
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
                            'icon' => 'book',
                            'url' => ['/admin/entry/index'],
                            'active' => ['admin/entry', 'admin/section', 'shop/'],
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
                                    'url' => ['/admin/entry/index'],
                                    'icon' => 'book',
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