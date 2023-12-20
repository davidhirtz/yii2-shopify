<?php

namespace davidhirtz\yii2\shopify\modules\admin\widgets\navs;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\models\Webhook;
use davidhirtz\yii2\skeleton\widgets\fontawesome\Submenu;
use Yii;

class ShopifySubmenu extends Submenu
{
    public ?Product $model = null;

    public function init(): void
    {
        $this->title = $this->title ?: Yii::t('shopify', 'Shopify');
        $this->items = array_merge($this->items, $this->getDefaultItems());

        parent::init();
    }

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
