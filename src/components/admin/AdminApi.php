<?php

namespace davidhirtz\yii2\shopify\components\admin;

use davidhirtz\yii2\shopify\components\GraphqlParser;
use davidhirtz\yii2\shopify\components\ShopifyComponent;
use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\skeleton\log\ActiveRecordErrorLogger;
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

    public function getProducts(int $batchSize = 20): AdminApiProductIterator
    {
        return new AdminApiProductIterator($this, $batchSize);
    }

    public function fetchProducts(int $limit, ?string $cursor = null): array
    {
        $query = $this->getGraphqlFile('ProductsQuery');

        $data = $this->query($query, [
            'limit' => $limit,
            'cursor' => $cursor,
        ]);

        return $data['products']['edges'] ?? [];
    }

    public function updateOrCreateProduct(array $data): Product
    {
        $this->prepareApiData($data);
        $mapper = new AdminApiProductDataMapper($data);
        dd($data);

        if ($mapper->product->save()) {
            // Todo media + variants
        }

        if ($mapper->product->getErrors()) {
            ActiveRecordErrorLogger::log($mapper->product);
        }

        return $mapper->product;
    }

    protected function query(string $query, array $variables = []): array
    {
        $uri = "https://{$this->shopifyShopName}.myshopify.com/admin/api/{$this->shopifyApiVersion}/graphql.json";

        $options = [
            'body' => json_encode(array_filter([
                'query' => $query,
                'variables' => $variables,
            ])),
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Shopify-Access-Token' => $this->shopifyAccessToken,
            ],
        ];

        if (YII_DEBUG) {
            $options['on_stats'] = function (TransferStats $stats) {
                Yii::debug("Requesting Shopify Admin GraphQL API: {$stats->getEffectiveUri()}");
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

    protected function getGraphqlFile(string $name): string
    {
        return (new GraphqlParser())->load($name);
    }

    protected function prepareApiData(array &$data): void
    {
        $data['id'] = (int)substr(strrchr($data['id'], '/'), 1);
    }

    public function getErrors(): array
    {
        return $this->_errors;
    }
}