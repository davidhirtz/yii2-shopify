<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Models;

use davidhirtz\yii2\datetime\DateTime;
use Hirtz\Skeleton\Base\Traits\ModelTrait;
use Override;
use Yii;
use yii\base\Model;

class WebhookSubscription extends Model
{
    use ModelTrait;

    public const string AUTH_WEBHOOK_UPDATE = 'shopifyWebhookUpdate';

    public int $id;
    public string $api_version;
    public string $callbackUrl;
    public string $topic;
    public ?DateTime $updated_at = null;
    public ?DateTime $created_at = null;

    #[Override]
    public function attributeLabels(): array
    {
        return [
            'address' => Yii::t('shopify', 'WEBHOOK_SUBSCRIPTION_ADDRESS_LABEL'),
            'topic' => Yii::t('shopify', 'WEBHOOK_SUBSCRIPTION_TOPIC_LABEL'),
            'format' => Yii::t('shopify', 'WEBHOOK_SUBSCRIPTION_FORMAT_LABEL'),
            'api_version' => Yii::t('shopify', 'WEBHOOK_SUBSCRIPTION_API_VERSION_LABEL'),
            'updated_at' => Yii::t('skeleton', 'WEBHOOK_SUBSCRIPTION_UPDATED_AT_LABEL'),
        ];
    }
}
