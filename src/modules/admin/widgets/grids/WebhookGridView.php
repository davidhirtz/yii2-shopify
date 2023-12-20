<?php

namespace davidhirtz\yii2\shopify\modules\admin\widgets\grids;

use davidhirtz\yii2\shopify\models\Webhook;
use davidhirtz\yii2\shopify\modules\admin\controllers\WebhookController;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\modules\admin\widgets\grids\GridView;
use davidhirtz\yii2\skeleton\widgets\fontawesome\Icon;
use davidhirtz\yii2\timeago\Timeago;
use Yii;
use yii\data\ArrayDataProvider;

class WebhookGridView extends GridView
{
    use ModuleTrait;

    /**
     * @var Webhook[]
     */
    public ?array $webhooks = [];

    public function init(): void
    {
        if (!$this->dataProvider) {
            $this->dataProvider = new ArrayDataProvider([
                'allModels' => $this->webhooks,
            ]);
        }

        if (!$this->rowOptions) {
            $this->rowOptions = fn(Webhook $model) => ['id' => "#webhook-$model->id"];
        }

        if (!$this->columns) {
            $this->columns = [
                $this->topicColumn(),
                $this->apiVersionColumn(),
                $this->formatColumn(),
                $this->updatedAtColumn(),
                $this->buttonsColumn(),
            ];
        }

        $this->initFooter();

        parent::init();
    }

    protected function initFooter(): void
    {
        if ($this->footer === null) {
            $this->footer = [
                [
                    [
                        'content' => $this->getUpdateAllWebhooksButton(),
                        'options' => ['class' => 'col text-right'],
                    ],
                ],
            ];
        }
    }

    public function topicColumn(): array
    {
        return [
            'attribute' => 'topic',
            'content' => function (Webhook $webhook): string {
                $html = Html::tag('div', $webhook->getFormattedTopic(), ['class' => 'strong']);
                $html .= Html::tag('div', $webhook->address, ['class' => 'small']);

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
            'content' => fn(Webhook $webhook): string => strtoupper($webhook->api_version)
        ];
    }

    public function formatColumn(): array
    {
        return [
            'attribute' => 'format',
            'headerOptions' => ['class' => 'd-none d-lg-table-cell'],
            'contentOptions' => ['class' => 'd-none d-lg-table-cell text-nowrap'],
            'content' => fn(Webhook $webhook): string => strtoupper($webhook->format)
        ];
    }

    public function updatedAtColumn(): array
    {
        return [
            'headerOptions' => ['class' => 'd-none d-lg-table-cell'],
            'contentOptions' => ['class' => 'd-none d-lg-table-cell text-nowrap'],
            'content' => fn(Webhook $webhook): string => Timeago::tag($webhook->updated_at)
        ];
    }

    public function buttonsColumn(): array
    {
        return [
            'contentOptions' => ['class' => 'text-right text-nowrap'],
            'content' => fn(Webhook $webhook): string => Html::buttons($this->getRowButtons($webhook))
        ];
    }

    /**
     * @see WebhookController::actionUpdateAll()
     */
    protected function getUpdateAllWebhooksButton(): string
    {
        $content = $this->dataProvider->getModels() ? Yii::t('shopify', 'Reload Webhooks') : Yii::t('shopify', 'Install Webhooks');

        return Html::a(Html::iconText('sync', $content), ['/admin/shopify-webhook/update-all'], [
            'class' => 'btn btn-secondary',
            'data-method' => 'post',
        ]);
    }

    protected function getRowButtons(Webhook $webhook): array
    {
        return [
            $this->getUnlinkButton($webhook),
        ];
    }

    /**
     * @see WebhookController::actionDelete()
     */
    protected function getUnlinkButton(Webhook $model): string
    {
        return Html::a(Icon::tag('trash'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data-confirm' => Yii::t('shopify', 'Are you sure you want to remove this webhook?'),
            'data-target' => "#webhook-$model->id",
            'data-ajax' => 'remove',
        ]);
    }
}