<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Data;

use Hirtz\Shopify\Components\Admin\WebhookSubscriptionBatchQuery;
use Hirtz\Shopify\Components\Admin\WebhookSubscriptionMapper;
use Hirtz\Shopify\Models\WebhookSubscription;
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
