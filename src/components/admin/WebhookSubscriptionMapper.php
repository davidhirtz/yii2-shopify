<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components\admin;

use davidhirtz\yii2\shopify\components\ShopifyDateTime;
use davidhirtz\yii2\shopify\components\ShopifyId;
use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\models\WebhookSubscription;

readonly class WebhookSubscriptionMapper
{
    protected WebhookSubscription $webhook;

    public function __construct(protected array $data)
    {
        $this->webhook = WebhookSubscription::create();
        $this->setAttributes();
    }

    protected function setAttributes(): void
    {
        $this->webhook->created_at = (new ShopifyDateTime($this->data['createdAt']))->toDateTime();
        $this->webhook->callbackUrl = $this->data['endpoint']['callbackUrl'];
        $this->webhook->id = (new ShopifyId($this->data['id']))->toInt();
        $this->webhook->topic = $this->data['topic'];
        $this->webhook->updated_at = (new ShopifyDateTime($this->data['updatedAt']))->toDateTime();
    }

    public function __invoke(): WebhookSubscription
    {
        return $this->webhook;
    }
}
