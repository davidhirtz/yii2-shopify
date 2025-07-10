<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components\admin;

use davidhirtz\yii2\shopify\components\GraphqlParser;
use Yii;

class WebhookSubscriptionMutation
{
    private array $errors = [];
    private AdminApi $api;

    public function __construct()
    {
        $this->api = Yii::$app->get('shopify')->getAdminApi();
    }

    public function create(string $topic, string $callbackUrl): bool
    {
        $query = (new GraphqlParser())->load('WebhookSubscriptionCreateMutation');

        $result = $this->api->query($query, [
            'topic' => $topic,
            'webhookSubscription' => [
                'callbackUrl' => $callbackUrl,
                'format' => 'JSON',
            ],
        ]);

        $data = $result['webhookSubscriptionCreate'] ?? [];
        return $this->parseResponse($data);
    }

    public function delete(int $id): bool
    {
        $query = (new GraphqlParser())->load('WebhookSubscriptionDeleteMutation');

        $result = $this->api->query($query, [
            'id' => "gid://shopify/WebhookSubscription/$id"
        ]);

        $data = $result['webhookSubscriptionDelete'] ?? [];
        return $this->parseResponse($data);
    }

    protected function parseResponse(array $data): bool
    {
        foreach ($data['userErrors'] ?? [] as $error) {
            $this->errors[] = $error['message'] ?? 'Unknown error';
        }

        return !$this->getErrors();
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
