<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Widgets\Navs;

use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Models\Webhook;
use Hirtz\Skeleton\Widgets\Navs\NavItem;
use Override;
use Yii;

class ShopifyNavItem extends NavItem
{
    public function __construct(array $config = [])
    {
        $this->label ??= Yii::t('shopify', 'Shopify');
        $this->icon ??= 'brand:shopify';
        $this->order ??= 50;
        $this->url ??= ['/admin/shopify/product/index'];

        parent::__construct($config);
    }

    #[Override]
    protected function configure(): void
    {
        $this->addSubnavItems();
        parent::configure();
    }

    protected function addSubnavItems(): void
    {
        $this->addItems($this->getProductsItem(), $this->getWebhooksItem());
    }

    protected function getProductsItem(): NavItem
    {
        return NavItem::make()
            ->label(Yii::t('shopify', 'Products'))
            ->order(10)
            ->url(['/admin/shopify/product/index'])
            ->roles([Product::AUTH_PRODUCT_UPDATE])
            ->routes(['shopify/product']);
    }

    protected function getWebhooksItem(): NavItem
    {
        return NavItem::make()
            ->label(Yii::t('shopify', 'Webhooks'))
            ->order(20)
            ->url(['/admin/shopify/webhook/index'])
            ->roles([Webhook::AUTH_WEBHOOK_UPDATE])
            ->routes(['shopify/webhook']);
    }
}
