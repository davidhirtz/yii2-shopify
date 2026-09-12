<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Widgets\Navs;

use Hirtz\Shopify\Modules\Admin\Data\WebhookSubscriptionArrayDataProvider;
use Hirtz\Skeleton\Widgets\Navs\Header;
use Hirtz\Skeleton\Widgets\Traits\ProviderTrait;
use Stringable;
use Yii;

class WebhookHeader extends Header
{
    /**
     * @use ProviderTrait<WebhookSubscriptionArrayDataProvider>
     */
    use ProviderTrait;

    #[\Override]
    protected function configure(): void
    {
        $this->breadcrumbs ??= [Yii::t('shopify', 'COMMON_SHOPIFY') => ['/admin/shopify/product/index']];
        $this->title ??= Yii::t('shopify', 'COMMON_WEBHOOKS');

        $this->addContent($this->getWebhookActionDropdown());

        parent::configure();
    }

    protected function getWebhookActionDropdown(): ?Stringable
    {
        return WebhookActionDropdown::make()
            ->provider($this->provider);
    }
}
