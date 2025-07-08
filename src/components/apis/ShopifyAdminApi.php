<?php

namespace davidhirtz\yii2\shopify\components\apis;

use davidhirtz\yii2\shopify\components\ShopifyComponent;
use davidhirtz\yii2\shopify\models\collections\ShopifyApiProductCollection;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\TransferStats;
use Yii;
use yii\helpers\Json;

class ShopifyAdminApi
{
    private array $_errors = [];

    public function __construct(private ShopifyComponent $shopify)
    {
    }

    public function getProducts(int $batchSize = 20): ShopifyApiProductCollection
    {
        return new ShopifyApiProductCollection($this, $batchSize);
    }

    public function fetchProducts(int $limit = 20, ?string $cursor = null): array
    {
        $productFragment = file_get_contents(Yii::getAlias('@shopify/components/graphql/ProductFields.gql'));
        $variantFragment = file_get_contents(Yii::getAlias('@shopify/components/graphql/ProductVariantFields.gql'));

        $query = <<<GRAPHQL
                    query GetProducts (\$limit: Int! \$cursor: String) {
                        products(first: \$limit, after: \$cursor) {
                            edges {
                                cursor
                                node {
                                    ...ProductFields
                                    variants(first: 250) {
                                        edges {
                                            cursor
                                            node {
                                                ...ProductVariantFields
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    $productFragment
                    $variantFragment
                    
                    GRAPHQL;

        $data = $this->query($query, [
            'limit' => $limit,
            'cursor' => $cursor,
        ]);

        return $data['products']['edges'] ?? [];
    }

    protected function query(string $query, array $variables = []): array
    {
        $uri = "https://{$this->shopify->shopifyShopName}.myshopify.com/admin/api/{$this->shopify->shopifyApiVersion}/graphql.json";

        $options = [
            'body' => json_encode(array_filter([
                'query' => $query,
                'variables' => $variables,
            ])),
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Shopify-Access-Token' => $this->shopify->shopifyAccessToken,
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

    public function getErrors(): array
    {
        return $this->_errors;
    }
}