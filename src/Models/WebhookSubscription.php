<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Models;

use Hirtz\Skeleton\Db\DateTime;
use Hirtz\Skeleton\Base\Traits\ModelTrait;
use Override;
use Yii;
use yii\base\Model;

class WebhookSubscription extends Model
{
    use ModelTrait;

    public int $id;
    public string $api_version;
    public string $callbackUrl;
    public string $topic;
    public string $format;
    public ?DateTime $updated_at = null;
    public ?DateTime $created_at = null;

    /**
     * The API answers the topic as an enum value (`PRODUCTS_UPDATE`), where `Webhook` names it as the REST path.
     */
    public function getFormattedTopic(): string
    {
        $topic = strtolower((string)preg_replace('/_/', '/', $this->topic, 1));
        return Webhook::getTopics()[$topic] ?? ucfirst(str_replace('/', ' ', $topic));
    }

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
