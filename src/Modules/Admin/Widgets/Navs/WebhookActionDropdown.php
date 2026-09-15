<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Widgets\Navs;

use Hirtz\Shopify\Components\ComponentTrait;
use Hirtz\Shopify\Modules\Admin\Data\WebhookSubscriptionArrayDataProvider;
use Hirtz\Skeleton\Widgets\Buttons\Button;
use Hirtz\Skeleton\Widgets\Navs\ActionDropdown;
use Hirtz\Skeleton\Widgets\Traits\ProviderTrait;
use Override;
use Stringable;
use Yii;

class WebhookActionDropdown extends ActionDropdown
{
    use ComponentTrait;
    /**
     * @use ProviderTrait<WebhookSubscriptionArrayDataProvider>
     */
    use ProviderTrait;

    #[Override]
    protected function configure(): void
    {
        $this->addDefaultItems();
        parent::configure();
    }

    protected function addDefaultItems(): void
    {
        $this->addItem($this->getViewWebhooksButton(), $this->getUpdateAllWebhooksButton());
    }

    protected function getViewWebhooksButton(): string|Stringable
    {
        return Button::make()
            ->primary()
            ->url(static::getShopify()->getShopUrl('admin/settings/notifications/webhooks'))
            ->icon('external-link')
            ->text(Yii::t('shopify', 'WEBHOOK_ACTION_DROPDOWN_VIEW_WEBHOOKS'))
            ->target('_blank');
    }

    /**
     * @see WebhookController::actionUpdateAll()
     */
    protected function getUpdateAllWebhooksButton(): ?Stringable
    {
        return Button::make()
            ->primary()
            ->content($this->provider->getModels()
                ? Yii::t('shopify', 'WEBHOOK_ACTION_DROPDOWN_RELOAD_WEBHOOKS')
                : Yii::t('shopify', 'WEBHOOK_ACTION_DROPDOWN_INSTALL_WEBHOOKS'))
            ->icon('sync')
            ->post(['/admin/shopify/webhook/create']);
    }
}
