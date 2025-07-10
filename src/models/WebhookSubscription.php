<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\models;

use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\skeleton\base\traits\ModelTrait;
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
            'address' => Yii::t('shopify', 'URL'),
            'topic' => Yii::t('shopify', 'Event'),
            'format' => Yii::t('shopify', 'Format'),
            'api_version' => Yii::t('shopify', 'API Version'),
            'updated_at' => Yii::t('skeleton', 'Last Update'),
        ];
    }
}
