<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Components\Admin;

use Hirtz\Shopify\Components\GraphqlParser;

class WebhookSubscriptionBatchQuery extends BatchQuery
{
    protected function fetchData(): array
    {
        $data = $this->api->query($this->getQuery(), [
            'limit' => $this->batchSize,
            'cursor' => $this->currentCursor,
        ]);

        return $data['webhookSubscriptions']['edges'] ?? [];
    }

    protected function getQuery(): string
    {
        return (new GraphqlParser())->load('WebhookSubscriptionsQuery');
    }
}
