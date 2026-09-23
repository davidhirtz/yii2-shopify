<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Components\Admin;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\TransferStats;
use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\Json;

class AdminApi
{
    /**
     * @var list<string>
     */
    private array $errors = [];

    public function __construct(
        private readonly string $shopifyShopName,
        private readonly string $shopifyAccessToken,
        private readonly string $shopifyApiVersion,
    ) {
    }

    /**
     * @param array<string, mixed> $variables
     * @return array<string, mixed>
     */
    public function query(string $query, array $variables = []): array
    {
        $uri = "https://$this->shopifyShopName.myshopify.com/admin/api/$this->shopifyApiVersion/graphql.json";

        $body = array_filter([
            'query' => $query,
            'variables' => $variables,
        ]);

        $options = [
            'body' => json_encode($body),
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Shopify-Access-Token' => $this->shopifyAccessToken,
            ],
        ];

        if (YII_DEBUG) {
            $options['on_stats'] = function (TransferStats $stats) use ($body): void {
                Yii::debug("Requesting Shopify Admin GraphQL API: {$stats->getEffectiveUri()}");
                Yii::debug($body);
            };
        }

        $results = $this->request($uri, $options);

        foreach ($results['errors'] ?? [] as $error) {
            $this->errors[] = $error['message'];
            Yii::error($error['message']);
        }

        return $results['data'] ?? [];
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>|null
     */
    protected function request(string $uri, array $options = []): ?array
    {
        try {
            $request = $this->createClient()->post($uri, $options);
            return Json::decode($request->getBody()->getContents());
        } catch (Exception $exception) {
            // Return error to user as this could be a missing scope or invalid API key which could be fixed without
            // consulting the error log ...
            if ($exception instanceof BadResponseException) {
                $this->errors = $this->getResponseErrors($exception);
            }

            Yii::error($exception->getMessage());
        }

        return null;
    }

    protected function createClient(): Client
    {
        return new Client();
    }

    /**
     * Shopify answers an error in JSON, but whatever stands in front of it may not: an HTML page from a proxy, a
     * plain-text 429, an empty 503. Those fall back to the exception's message, which quotes the body.
     *
     * @return list<string>
     */
    private function getResponseErrors(BadResponseException $exception): array
    {
        try {
            $contents = Json::decode((string)$exception->getResponse()->getBody());
        } catch (InvalidArgumentException) {
            $contents = null;
        }

        $errors = is_array($contents) ? ($contents['errors'] ?? null) : null;

        if (!$errors) {
            return [$exception->getMessage() ?: 'Unknown API Error'];
        }

        return array_values(array_map(
            fn (mixed $error): string => is_array($error) ? ($error['message'] ?? Json::encode($error)) : (string)$error,
            (array)$errors
        ));
    }

    /**
     * @return list<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
