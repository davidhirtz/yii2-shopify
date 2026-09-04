<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Widgets\Navs;

use Hirtz\Skeleton\I18n\Lang;
use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Models\Webhook;
use Hirtz\Skeleton\Widgets\Navs\NavItem;
use Override;
use Yii;

class ShopifyNavItem extends NavItem
{
    public function __construct(array $config = [])
    {
        $this->label ??= Lang::t('shopify', 'COMMON_PRODUCTS');
        $this->icon ??= 'brand:shopify';
        $this->order ??= 50;
        $this->roles ??= [Product::AUTH_PRODUCT_UPDATE];
        $this->url ??= ['/admin/shopify/product/index'];

        $this->routes(['shopify/product']);

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
        $this->addItem($this->getWebhooksItem());
    }

    protected function getWebhooksItem(): NavItem
    {
        return NavItem::make()
            ->label(Lang::t('shopify', 'COMMON_WEBHOOKS'))
            //->icon('satellite-dish')
            ->url(['/admin/shopify/webhook/index'])
            ->order(20)
            ->roles([Webhook::AUTH_WEBHOOK_UPDATE])
            ->routes(['shopify/webhook']);
    }
}
