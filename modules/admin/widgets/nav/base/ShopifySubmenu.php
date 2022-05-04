<?php

namespace davidhirtz\yii2\shopify\modules\admin\widgets\nav\base;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\models\Webhook;
use davidhirtz\yii2\skeleton\widgets\fontawesome\Submenu;
use Yii;

/**
 * Class Submenu
 * @package davidhirtz\yii2\shopify\modules\admin\widgets\nav\base
 */
class ShopifySubmenu extends Submenu
{
    /**
     * @var Product
     */
    public $model;

    /**
     * Initializes the nav items.
     */
    public function init()
    {
        $this->title = $this->title ?: Yii::t('shopify', 'Shopify');
        $this->items = array_merge($this->items, $this->getDefaultItems());

        parent::init();
    }

    /**
     * @return array
     */
    protected function getDefaultItems(): array
    {
        return [
            [
                'label' => Yii::t('shopify', 'Products'),
                'url' => ['/admin/product/index'],
                'icon' => 'tags',
                'roles' => [Product::AUTH_PRODUCT_UPDATE],
                'labelOptions' => [
                    'class' => 'd-none d-md-inline'
                ],
            ],
            [
                'label' => Yii::t('shopify', 'Webhooks'),
                'url' => ['/admin/shopify-webhook/index'],
                'icon' => 'satellite-dish',
                'roles' => [Webhook::AUTH_WEBHOOK_UPDATE],
                'labelOptions' => [
                    'class' => 'd-none d-md-inline'
                ],
            ],
        ];
    }
}