<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\modules\admin\widgets\grids;

use davidhirtz\yii2\shopify\models\WebhookSubscription;
use davidhirtz\yii2\shopify\modules\admin\controllers\WebhookController;
use davidhirtz\yii2\shopify\modules\admin\data\WebhookSubscriptionArrayDataProvider;
use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\modules\admin\widgets\grids\GridView;
use davidhirtz\yii2\skeleton\widgets\fontawesome\Icon;
use davidhirtz\yii2\timeago\TimeagoColumn;
use Override;
use Yii;

/**
 * @property WebhookSubscriptionArrayDataProvider $dataProvider
 */
class WebhookGridView extends GridView
{
    #[Override]
    public function init(): void
    {
        if (!$this->rowOptions) {
            $this->rowOptions = fn (WebhookSubscription $model) => ['id' => "#webhook-$model->id"];
        }

        if (!$this->columns) {
            $this->columns = [
                $this->topicColumn(),
                $this->apiVersionColumn(),
                $this->updatedAtColumn(),
                $this->buttonsColumn(),
            ];
        }

        $this->initFooter();

        parent::init();
    }

    protected function initFooter(): void
    {
        $this->footer ??= [
            [
                [
                    'content' => $this->getCreateAllWebhooksButton(),
                    'options' => ['class' => 'col text-right'],
                ],
            ],
        ];
    }

    public function topicColumn(): array
    {
        return [
            'attribute' => 'formattedTopic',
            'content' => function (WebhookSubscription $webhook): string {
                $html = Html::tag('div', $webhook->getFormattedTopic(), ['class' => 'strong']);
                $html .= Html::tag('div', $webhook->callbackUrl, ['class' => 'small']);

                return $html;
            }
        ];
    }

    public function apiVersionColumn(): array
    {
        return [
            'attribute' => 'api_version',
            'headerOptions' => ['class' => 'd-none d-lg-table-cell'],
            'contentOptions' => ['class' => 'd-none d-lg-table-cell text-nowrap'],
            'content' => fn (WebhookSubscription $webhook): string => $webhook->api_version
        ];
    }

    public function updatedAtColumn(): array
    {
        return [
            'class' => TimeagoColumn::class,
            'attribute' => 'updated_at',
        ];
    }

    public function buttonsColumn(): array
    {
        return [
            'contentOptions' => ['class' => 'text-right text-nowrap'],
            'content' => fn (WebhookSubscription $webhook): string => Html::buttons($this->getRowButtons($webhook))
        ];
    }

    /**
     * @see WebhookController::actionCreate()
     */
    protected function getCreateAllWebhooksButton(): string
    {
        $content = $this->dataProvider->getModels() ? Yii::t('shopify', 'Reload Webhooks') : Yii::t('shopify', 'Install Webhooks');

        return Html::a(Html::iconText('sync', $content), ['/admin/shopify-webhook/create'], [
            'class' => 'btn btn-secondary',
            'data-method' => 'post',
        ]);
    }

    protected function getRowButtons(WebhookSubscription $webhook): array
    {
        return [
            $this->getUnlinkButton($webhook),
        ];
    }

    /**
     * @see WebhookController::actionDelete()
     */
    protected function getUnlinkButton(WebhookSubscription $model): string
    {
        return Html::a((string)Icon::tag('trash'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data-confirm' => Yii::t('shopify', 'Are you sure you want to remove this webhook?'),
            'data-target' => "#webhook-$model->id",
            'data-ajax' => 'remove',
        ]);
    }
}
