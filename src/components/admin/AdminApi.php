<?php

namespace davidhirtz\yii2\shopify\components\admin;

use davidhirtz\yii2\shopify\components\GraphqlParser;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\TransferStats;
use Yii;
use yii\helpers\Json;

class AdminApi
{
    private array $_errors = [];

    public function __construct(
        private readonly string $shopifyShopName,
        private readonly string $shopifyAccessToken,
        private readonly string $shopifyApiVersion,
    )
    {
    }

    public function getProducts(int $batchSize = 20): ProductIterator
    {
        return new ProductIterator($this, $batchSize);
    }

    public function fetchProducts(int $limit, ?string $cursor = null): array
    {
        $query = $this->getGraphqlQueryByName('ProductsQuery');

        $data = $this->query($query, [
            'limit' => $limit,
            'cursor' => $cursor,
        ]);

        return $data['products']['edges'] ?? [];
    }

    protected function query(string $query, array $variables = []): array
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
            $options['on_stats'] = function (TransferStats $stats) {
                Yii::debug("Requesting Shopify Admin GraphQL API: {$stats->getEffectiveUri()}");
                Yii::debug($stats->getRequest()->getBody());
            };
        }

        $results = $this->request($uri, $options);

        foreach ($results['errors'] ?? [] as $error) {
            $this->_errors[] = $error['message'];
            Yii::error($error['message']);
        }

        return $results['data'] ?? [];
    }

    protected function request(string $uri, array $options = []): ?array
    {
        try {
            $request = (new Client())->post($uri, $options);
            return Json::decode($request->getBody()->getContents());
        } catch (Exception $exception) {
            // Return error to user as this could be a missing scope or invalid API key which could be fixed without
            // consulting the error log ...
            if ($exception instanceof ClientException) {
                // Todo handle strings
                $contents = Json::decode($exception->getResponse()->getBody()->getContents());
                $errors = $contents['errors'] ?? $exception->getMessage() ?: 'Unknown API Error';
                $this->_errors = (array)$errors;
            }

            Yii::error($exception->getMessage());
        }

        return null;
    }

    protected function getGraphqlQueryByName(string $name): string
    {
        return (new GraphqlParser())->load($name);
    }

    public function getErrors(): array
    {
        return $this->_errors;
    }
}