<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\modules\admin\data;

use davidhirtz\yii2\shopify\components\admin\WebhookSubscriptionBatchQuery;
use davidhirtz\yii2\shopify\components\admin\WebhookSubscriptionMapper;
use davidhirtz\yii2\shopify\models\WebhookSubscription;
use yii\data\ArrayDataProvider;

/**
 * @property WebhookSubscription[] $allModels
 */
class WebhookSubscriptionArrayDataProvider extends ArrayDataProvider
{
    #[\Override]
    public function init(): void
    {
        if (!$this->allModels) {
            $this->allModels = $this->getModelsFromApi();
        }

        parent::init();
    }

    protected function getModelsFromApi(): array
    {
        $models = [];

        foreach ((new WebhookSubscriptionBatchQuery(250)) as $data) {
            $models[] = (new WebhookSubscriptionMapper($data['node']))();
        }

        return $models;
    }

    #[\Override]
    public function getSort()
    {
        return parent::getSort();
    }
}
