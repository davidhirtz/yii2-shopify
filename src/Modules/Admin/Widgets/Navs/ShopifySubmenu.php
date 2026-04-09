<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Widgets\Navs;

use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Models\Webhook;
use Hirtz\Skeleton\Widgets\Navs\NavItem;
use Hirtz\Skeleton\Widgets\Navs\Submenu;
use Override;
use Yii;

class ShopifySubmenu extends Submenu
{
    protected ?Product $product = null;

    #[Override]
    protected function configure(): void
    {
        $this->title ??= Yii::t('shopify', 'Shopify');
        $this->items = $this->getDefaultItems();

        parent::configure();
    }

    protected function getDefaultItems(): array
    {
        return [
            NavItem::make()
                ->label(Yii::t('shopify', 'Products'))
                ->url(['/admin/shopify/product/index'])
                ->routes(['admin/shopify/product/'])
                ->icon('tags')
                ->roles([Product::AUTH_PRODUCT_UPDATE]),
            NavItem::make()
                ->label(Yii::t('shopify', 'Webhooks'))
                ->url(['/admin/shopify/webhook/index'])
                ->routes(['admin/shopify/webhook/'])
                ->icon('satellite-dish')
                ->roles([Webhook::AUTH_WEBHOOK_UPDATE]),
        ];
    }
}
