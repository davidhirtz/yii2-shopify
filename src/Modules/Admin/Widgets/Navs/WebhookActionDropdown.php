<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Widgets\Navs;

use Hirtz\Shopify\Modules\Admin\Data\WebhookSubscriptionArrayDataProvider;
use Hirtz\Skeleton\Widgets\Buttons\Button;
use Hirtz\Skeleton\Widgets\Navs\ActionDropdown;
use Hirtz\Skeleton\Widgets\Traits\ProviderTrait;
use Override;
use Stringable;
use Yii;

class WebhookActionDropdown extends ActionDropdown
{
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
            ->url(Yii::$app->get('shopify')->getShopUrl('admin/settings/notifications/webhooks'))
            ->icon('external-link')
            ->text(Yii::t('shopify', 'View Webhooks'))
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
                ? Yii::t('shopify', 'Reload Webhooks')
                : Yii::t('shopify', 'Install Webhooks'))
            ->icon('sync')
            ->post(['/admin/shopify/webhook/create']);
    }
}
