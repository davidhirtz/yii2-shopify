<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Widgets\Grids;

use Hirtz\Shopify\Models\WebhookSubscription;
use Hirtz\Shopify\Modules\Admin\Controllers\WebhookController;
use Hirtz\Shopify\Modules\Admin\Data\WebhookSubscriptionArrayDataProvider;
use Hirtz\Shopify\Modules\ModuleTrait;
use Hirtz\Skeleton\Html\Div;
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
 * @extends GridView<WebhookSubscription>
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

    /**
     * The column renders the cell, so the content is what goes inside it.
     */
    protected function getTopicColumnContent(WebhookSubscription $webhook): string
    {
        return Div::make()->content($webhook->getFormattedTopic())->class('strong')
            . Div::make()->content($webhook->callbackUrl)->class('small');
    }

    protected function getApiVersionColumn(): ?Column
    {
        return DataColumn::make()
            ->property('api_version')
            ->content($this->getApiVersionColumnContent(...))
            ->hiddenForSmallDevices()
            ->nowrap();
    }

    protected function getApiVersionColumnContent(WebhookSubscription $webhook): string
    {
        return strtoupper((string)$webhook->api_version);
    }

    protected function getFormatColumn(): ?Column
    {
        return DataColumn::make()
            ->property('format')
            ->content($this->getFormatColumnContent(...))
            ->hiddenForMediumDevices()
            ->nowrap();
    }

    protected function getFormatColumnContent(WebhookSubscription $webhook): string
    {
        return strtoupper((string)$webhook->format);
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
     * @return list<Stringable>
     */
    protected function getButtonColumnContent(WebhookSubscription $webhook): array
    {
        return [
            $this->getUnlinkButton($webhook),
        ];
    }

    /**
     * @see WebhookController::actionDelete()
     */
    protected function getUnlinkButton(WebhookSubscription $model): ?Stringable
    {
        return DeleteGridButton::make()
            ->model($model)
            ->url(['/admin/shopify/webhook/delete', 'id' => $model->id])
            ->label(Yii::t('shopify', 'WEBHOOK_SUBSCRIPTION_DELETE_BUTTON'))
            ->title(Yii::t('shopify', 'WEBHOOK_SUBSCRIPTION_DELETE_CONFIRM'));
    }
}
