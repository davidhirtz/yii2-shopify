<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\models;

use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\skeleton\base\traits\ModelTrait;
use Yii;
use yii\base\Model;

class WebhookSubscription extends Model
{
    use ModelTrait;

    public const string AUTH_WEBHOOK_UPDATE = 'shopifyWebhookUpdate';

    public ?DateTime $created_at = null;
    public ?DateTime $updated_at = null;
    public ?int $id = null;
    public ?string $api_version = null;
    public ?string $callbackUrl = null;
    public ?string $format = null;
    public ?string $topic = null;

    public function getFormattedTopic(): string
    {
        return $this->getTopics()[$this->topic] ?? ucfirst(str_replace('/', ' ', $this->topic));
    }

    protected function getTopics(): array
    {
        return [
            'PRODUCTS_CREATE' => Yii::t('shopify', 'Product created'),
            'products/update' => Yii::t('shopify', 'Product updated'),
            'products/delete' => Yii::t('shopify', 'Product deleted'),
        ];
    }

    #[\Override]
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
