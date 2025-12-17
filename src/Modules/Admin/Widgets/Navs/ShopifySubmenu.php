<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Widgets\Navs;

use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Models\Webhook;
use Hirtz\Skeleton\Widgets\Fontawesome\Submenu;
use Yii;

class ShopifySubmenu extends Submenu
{
    public ?Product $model = null;

    public function init(): void
    {
        $this->title = $this->title ?: Yii::t('shopify', 'Shopify');
        $this->items = [...$this->items, ...$this->getDefaultItems()];

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
