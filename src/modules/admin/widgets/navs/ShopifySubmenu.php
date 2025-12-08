<?php

declare(strict_types=1);

namespace Hirtz\Shopify\modules\admin\widgets\navs;

use Hirtz\Shopify\models\Product;
use Hirtz\Shopify\models\Webhook;
use Hirtz\Skeleton\widgets\fontawesome\Submenu;
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
