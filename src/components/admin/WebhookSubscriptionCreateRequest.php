<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components\admin;

use davidhirtz\yii2\shopify\components\GraphqlParser;
use Override;
use Yii;

class WebhookSubscriptionCreateRequest extends AbstractMutationRequest
{
    public function __construct(protected readonly string $topic, protected readonly string $url)
    {
    }

    #[Override]
    protected function getResponse(): array
    {
        $query = (new GraphqlParser())->load('WebhookSubscriptionCreateMutation');
        $shopify = Yii::$app->get('shopify');

        $result = $shopify->getAdminApi()->query($query, [
            'topic' => $this->topic,
            'webhookSubscription' => [
                'callbackUrl' => $this->url,
                'format' => 'JSON',
            ],
        ]);

        return $result['webhookSubscriptionCreate'] ?? [];
    }
}
