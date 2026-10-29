<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Widgets\Navs;

use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Modules\Admin\Data\WebhookSubscriptionArrayDataProvider;
use Hirtz\Skeleton\Models\Breadcrumb;
use Hirtz\Skeleton\Widgets\Navs\Header;
use Hirtz\Skeleton\Widgets\Traits\ProviderTrait;
use Override;
use Stringable;
use Yii;

class WebhookHeader extends Header
{
    /**
     * @use ProviderTrait<WebhookSubscriptionArrayDataProvider>
     */
    use ProviderTrait;

    #[Override]
    protected function configure(): void
    {
        $this->breadcrumbs ??= $this->webuser->can(Product::AUTH_SHOPIFY_PRODUCT)
            ? [new Breadcrumb(Yii::t('shopify', 'PRODUCT_PLURAL'), ['/admin/shopify/product/index'])]
            : [];

        $this->title ??= Yii::t('shopify', 'WEBHOOK_PLURAL');

        $this->addContent($this->getWebhookActionDropdown());

        parent::configure();
    }

    protected function getWebhookActionDropdown(): ?Stringable
    {
        return WebhookActionDropdown::make()
            ->provider($this->provider);
    }
}
