<?php

namespace davidhirtz\yii2\shopify\modules\admin\widgets\grid\base;

use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\modules\admin\widgets\grid\GridView;
use yii\data\ArrayDataProvider;

/**
 * Class WebhookGridView
 * @package davidhirtz\yii2\shopify\modules\admin\widgets\grid\base
 */
class WebhookGridView extends GridView
{
    use ModuleTrait;

    /**
     * @var array
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

//        if (!$this->columns) {
//            $this->columns = [
//                $this->topicColumn(),
//                $this->addressColumn(),
//                $this->formatColumn(),
//                $this->apiVersionColumn(),
//                $this->buttonsColumn(),
//            ];
//        }

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
                        'content' => '',
                        'options' => ['class' => 'col'],
                    ],
                ],
            ];
        }
    }
}