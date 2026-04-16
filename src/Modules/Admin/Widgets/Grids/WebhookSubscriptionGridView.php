<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Widgets\Grids;

use Hirtz\Shopify\Models\Webhook;
use Hirtz\Shopify\Modules\Admin\Controllers\WebhookController;
use Hirtz\Shopify\Modules\Admin\Data\WebhookSubscriptionArrayDataProvider;
use Hirtz\Shopify\Modules\ModuleTrait;
use Hirtz\Skeleton\Html\Div;
use Hirtz\Skeleton\Html\Td;
use Hirtz\Skeleton\Widgets\Buttons\Button;
use Hirtz\Skeleton\Widgets\Grids\Columns\ButtonColumn;
use Hirtz\Skeleton\Widgets\Grids\Columns\Buttons\DeleteGridButton;
use Hirtz\Skeleton\Widgets\Grids\Columns\Column;
use Hirtz\Skeleton\Widgets\Grids\Columns\DataColumn;
use Hirtz\Skeleton\Widgets\Grids\Columns\RelativeTimeColumn;
use Hirtz\Skeleton\Widgets\Grids\GridView;
use Override;
use Stringable;
use Yii;

/**
 * @property WebhookSubscriptionArrayDataProvider $provider
 */
class WebhookSubscriptionGridView extends GridView
{
    use ModuleTrait;

    #[Override]
    protected function configure(): void
    {
        $this->columns ??= [
            $this->getTopicColumn(),
            $this->getApiVersionColumn(),
            $this->getFormatColumn(),
            $this->getUpdatedAtColumn(),
            $this->getButtonColumn(),
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
