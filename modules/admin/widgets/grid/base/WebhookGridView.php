<?php

namespace davidhirtz\yii2\shopify\modules\admin\widgets\grid\base;

use davidhirtz\yii2\shopify\models\Webhook;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\modules\admin\widgets\grid\GridView;
use davidhirtz\yii2\skeleton\widgets\fontawesome\Icon;
use davidhirtz\yii2\timeago\Timeago;
use Yii;
use yii\data\ArrayDataProvider;

/**
 * Class WebhookGridView
 * @package davidhirtz\yii2\shopify\modules\admin\widgets\grid\base
 */
class WebhookGridView extends GridView
{
    use ModuleTrait;

    /**
     * @var Webhook[]
     */
    public $webhooks;

    /**
     * @return void
     */
    public function init()
    {
        if (!$this->dataProvider) {
            $this->dataProvider = new ArrayDataProvider([
                'allModels' => $this->webhooks,
            ]);
        }

        if (!$this->rowOptions) {
            $this->rowOptions = function (Webhook $model) {
                return ['id' => "#webhook-{$model->id}"];
            };
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

    /**
     * Sets up grid footer.
     */
    protected function initFooter()
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

    /**
     * @return array
     */
    public function topicColumn()
    {
        return [
            'attribute' => 'topic',
            'content' => function (Webhook $webhook) {
                $html = Html::tag('div', $webhook->getFormattedTopic(), ['class' => 'strong']);
                $html .= Html::tag('div', $webhook->address, ['class' => 'small']);

                return $html;
            }
        ];
    }

    /**
     * @return array
     */
    public function apiVersionColumn()
    {
        return [
            'attribute' => 'api_version',
            'headerOptions' => ['class' => 'd-none d-lg-table-cell'],
            'contentOptions' => ['class' => 'd-none d-lg-table-cell text-nowrap'],
            'content' => function (Webhook $webhook) {
                return strtoupper($webhook->api_version);
            }
        ];
    }

    /**
     * @return array
     */
    public function formatColumn()
    {
        return [
            'attribute' => 'format',
            'headerOptions' => ['class' => 'd-none d-lg-table-cell'],
            'contentOptions' => ['class' => 'd-none d-lg-table-cell text-nowrap'],
            'content' => function (Webhook $webhook) {
                return strtoupper($webhook->format);
            }
        ];
    }

    /**
     * @return array
     */
    public function updatedAtColumn()
    {
        return [
            'headerOptions' => ['class' => 'd-none d-lg-table-cell'],
            'contentOptions' => ['class' => 'd-none d-lg-table-cell text-nowrap'],
            'content' => function (Webhook $webhook) {
                return Timeago::tag($webhook->updated_at);
            }
        ];
    }

    /**
     * @return array
     */
    public function buttonsColumn()
    {
        return [
            'contentOptions' => ['class' => 'text-right text-nowrap'],
            'content' => function (Webhook $webhook) {
                return Html::buttons($this->getRowButtons($webhook));
            }
        ];
    }

    /**
     * @return string
     */
    protected function getUpdateAllWebhooksButton()
    {
        $content = $this->dataProvider->getModels() ? Yii::t('shopify', 'Reload Webhooks') : Yii::t('shopify', 'Install Webhooks');

        return Html::a(Html::iconText('sync', $content), ['/admin/shopify-webhook/update-all'], [
            'class' => 'btn btn-secondary',
            'data-method' => 'post',
        ]);
    }

    /**
     * @param Webhook $webhook
     * @return array
     */
    protected function getRowButtons(Webhook $webhook)
    {
        return [
            $this->getUpdateButton($webhook),
        ];
    }

    /**
     * @param Webhook $model
     * @return string
     */
    protected function getUpdateButton($model): string
    {
        return Html::a(Icon::tag('trash'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data-confirm' => Yii::t('shopify', 'Are you sure you want to remove this webhook?'),
            'data-target' => "#webhook-{$model->id}",
            'data-ajax' => 'remove',
        ]);
    }
}