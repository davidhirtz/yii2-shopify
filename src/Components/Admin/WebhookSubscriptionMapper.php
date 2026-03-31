<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Components\Admin;

use Hirtz\Shopify\Components\ShopifyDateTime;
use Hirtz\Shopify\Components\ShopifyId;
use Hirtz\Shopify\Models\WebhookSubscription;

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
        $this->webhook->id = (new ShopifyId($this->data['id']))->toInt();
        $this->webhook->api_version = $this->data['apiVersion']['handle'];
        $this->webhook->callbackUrl = $this->data['endpoint']['callbackUrl'];
        $this->webhook->topic = $this->data['topic'];
        $this->webhook->updated_at = (new ShopifyDateTime($this->data['updatedAt']))->toDateTime();
        $this->webhook->created_at = (new ShopifyDateTime($this->data['createdAt']))->toDateTime();
    }

    public function __invoke(): WebhookSubscription
    {
        return $this->webhook;
    }
}
