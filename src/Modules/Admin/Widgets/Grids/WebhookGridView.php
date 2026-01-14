<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Widgets\Grids;

use Hirtz\Shopify\Models\Webhook;
use Hirtz\Shopify\Modules\Admin\Controllers\WebhookController;
use Hirtz\Shopify\Modules\Admin\Data\WebhookArrayDataProvider;
use Hirtz\Shopify\Modules\ModuleTrait;
use Hirtz\Skeleton\Html\Button;
use Hirtz\Skeleton\Html\Div;
use Hirtz\Skeleton\Html\Td;
use Hirtz\Skeleton\Widgets\Grids\Columns\ButtonColumn;
use Hirtz\Skeleton\Widgets\Grids\Columns\Buttons\DeleteGridButton;
use Hirtz\Skeleton\Widgets\Grids\Columns\Column;
use Hirtz\Skeleton\Widgets\Grids\Columns\DataColumn;
use Hirtz\Skeleton\Widgets\Grids\Columns\RelativeTimeColumn;
use Hirtz\Skeleton\Widgets\Grids\GridView;
use Stringable;
use Yii;

/**
 * @property WebhookArrayDataProvider $provider
 */
class WebhookGridView extends GridView
{
    use ModuleTrait;

    protected function configure(): void
    {
        $this->columns ??= [
            $this->getTopicColumn(),
            $this->getApiVersionColumn(),
            $this->getFormatColumn(),
            $this->getUpdatedAtColumn(),
            $this->getButtonColumn(),
        ];

        $this->footer ??= [
            $this->getUpdateAllWebhooksButton()
        ];

        parent::configure();
    }

    protected function getTopicColumn(): ?Column
    {
        return DataColumn::make()
            ->property('topic')
            ->content($this->getTopicColumnContent(...));
    }

    protected function getTopicColumnContent(Webhook $webhook): ?Stringable
    {
        return Td::make()
            ->content(
                Div::make()
                    ->content($webhook->getFormattedTopic())
                    ->class('strong'),
                Div::make()
                    ->content($webhook->address)
                    ->class('small')
            );
    }

    protected function getApiVersionColumn(): ?Column
    {
        return DataColumn::make()
            ->property('api_version')
            ->content($this->getApiVersionColumnContent(...))
            ->hiddenForSmallDevices();
    }

    protected function getApiVersionColumnContent(Webhook $webhook): ?Stringable
    {
        return Td::make()
            ->content(strtoupper((string)$webhook->api_version))
            ->class('text-nowrap');
    }

    protected function getFormatColumn(): ?Column
    {
        return DataColumn::make()
            ->property('api_version')
            ->content($this->getFormatColumnContent(...))
            ->hiddenForMediumDevices();
    }

    protected function getFormatColumnContent(Webhook $webhook): ?Stringable
    {
        return Td::make()
            ->content(strtoupper((string)$webhook->format))
            ->class('text-nowrap');
    }

    protected function getUpdatedAtColumn(): ?Column
    {
        return RelativeTimeColumn::make()
            ->property('updated_at');
    }

    protected function getButtonColumn(): ?Column
    {
        return ButtonColumn::make()
            ->content($this->getButtonColumnContent(...));
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
            ->post(['/admin/shopify-webhook/update-all']);
    }

    protected function getButtonColumnContent(Webhook $webhook): array
    {
        return [
            $this->getUnlinkButton($webhook),
        ];
    }

    /**
     * @see WebhookController::actionDelete()
     */
    protected function getUnlinkButton(Webhook $model): ?Stringable
    {
        return DeleteGridButton::make()
            ->model($model)
            ->title(Yii::t('shopify', 'Are you sure you want to remove this webhook?'));
    }
}
