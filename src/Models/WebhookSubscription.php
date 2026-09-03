<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Models;

use Hirtz\Skeleton\I18n\Lang;
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
            'address' => Lang::t('shopify', 'WEBHOOK_SUBSCRIPTION_ADDRESS_LABEL'),
            'topic' => Lang::t('shopify', 'WEBHOOK_SUBSCRIPTION_TOPIC_LABEL'),
            'format' => Lang::t('shopify', 'WEBHOOK_SUBSCRIPTION_FORMAT_LABEL'),
            'api_version' => Lang::t('shopify', 'WEBHOOK_SUBSCRIPTION_API_VERSION_LABEL'),
            'updated_at' => Lang::t('skeleton', 'WEBHOOK_SUBSCRIPTION_UPDATED_AT_LABEL'),
        ];
    }
}
