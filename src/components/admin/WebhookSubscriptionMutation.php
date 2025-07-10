<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components\admin;

use davidhirtz\yii2\shopify\components\GraphqlParser;
use Yii;

class WebhookSubscriptionMutation
{
    private readonly AdminApi $api;
    private array $errors = [];

    public function __construct()
    {
        $this->api = Yii::$app->get('shopify')->getAdminApi();
    }

    public function create(string $topic, string $callbackUrl): bool
    {
        $data = $this->query('WebhookSubscriptionCreate', [
            'topic' => $topic,
            'webhookSubscription' => [
                'callbackUrl' => $callbackUrl,
                'format' => 'JSON',
            ],
        ]);

        return isset($data['webhookSubscription']['id']);
    }

    public function delete(int $id): bool
    {
        $data = $this->query('WebhookSubscriptionDelete', [
            'id' => "gid://shopify/WebhookSubscription/$id",
        ]);

        return isset($data['deletedWebhookSubscriptionId']);
    }

    protected function query(string $name, array $data): array
    {
        $query = (new GraphqlParser())->load($name);

        $result = $this->api->query($query, $data);
        $data = $result[lcfirst($name)] ?? [];

        foreach ($data['userErrors'] ?? [] as $error) {
            $this->errors[] = $error['message'] ?? 'Unknown error';
        }

        return $data;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
