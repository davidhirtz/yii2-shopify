<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Data;

use Hirtz\Shopify\Models\Webhook;
use Hirtz\Shopify\Modules\ModuleTrait;
use Yii;
use yii\data\ArrayDataProvider;

/**
 * @property Webhook[] $allModels
 */
class WebhookArrayDataProvider extends ArrayDataProvider
{
    use ModuleTrait;

    #[\Override]
    public function init(): void
    {
        $this->allModels = [];

        foreach (static::getModule()->getApi()->getWebhooks() as $data) {
            $this->allModels[] = Yii::createObject([
                'class' => Webhook::class,
                ...$data,
            ]);
        }

        usort($this->allModels, fn (Webhook $a, Webhook $b) => strcmp((string)$b->updated_at, (string)$a->updated_at));
        parent::init();
    }
}
