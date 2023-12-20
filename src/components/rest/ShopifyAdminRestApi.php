<?php

namespace davidhirtz\yii2\shopify\components\rest;

use davidhirtz\yii2\skeleton\helpers\ArrayHelper;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\TransferStats;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Json;

class ShopifyAdminRestApi extends BaseObject
{
    public const SHOPIFY_MAX_PRODUCT_LIMIT = 250;

    public ?string $shopifyShopName = null;
    public ?string $shopifyAccessToken = null;
    public ?string $shopifyApiVersion = null;
    private array $_errors = [];
    private ?Client $_client = null;

    public function getProducts(): array
    {
        $results = $this->get('products', [
            'query' => [
                'limit' => static::SHOPIFY_MAX_PRODUCT_LIMIT,
            ],
            'headers' => [
                'X-Shopify-Api-Features' => 'include-presentment-prices',
            ],
        ]);

        return $results['products'] ?? [];
    }

    public function getProduct(int $id): array
    {
        $results = $this->get("products/$id", [
            'headers' => [
                'X-Shopify-Api-Features' => 'include-presentment-prices',
            ],
        ]);

        return $results['product'] ?? [];
    }

    public function getWebhooks(): array
    {
        $results = $this->get('webhooks', [
            'query' => [
                'limit' => static::SHOPIFY_MAX_PRODUCT_LIMIT,
            ],
        ]);

        return $results['webhooks'] ?? [];
    }

    public function setWebhook(array $params): array
    {
        $results = $this->post('webhooks', [
            'form_params' => [
                'webhook' => $params,
            ],
        ]);

        return $results['webhook'] ?? [];
    }

    public function deleteWebhook(int $id): bool
    {
        $this->request('DELETE', "webhooks/$id");
        return empty($this->getErrors());
    }

    public function get(string $endpoint, array $options = []): ?array
    {
        return $this->request('GET', $endpoint, $options);
    }

    public function post(string $endpoint, array $options = []): ?array
    {
        return $this->request('POST', $endpoint, $options);
    }

    public function request(string $method, string $endpoint, array $options = []): ?array
    {
        $uri = "https://$this->shopifyShopName.myshopify.com/admin/api/$this->shopifyApiVersion/$endpoint.json";

        $options['headers']['X-Shopify-Access-Token'] = $this->shopifyAccessToken;
        $options['on_stats'] = function (TransferStats $stats) use (&$url) {
            Yii::debug("Requesting Shopify Admin REST API: {$stats->getEffectiveUri()}");
        };

        try {
            $request = $this->getClient()->request($method, $uri, $options);
            $content = Json::decode($request->getBody()->getContents());

            if ($content) {
                if ($next = $this->getNextLinkFromHeader($request->getHeaders())) {
                    $query = parse_url($next, PHP_URL_QUERY);
                    $content = ArrayHelper::merge($content, $this->request($method, $endpoint, $query));
                }
            }

            return $content;
        } catch (Exception $exception) {
            // Return error to user as this could be a missing scope or invalid API key which could be fixed without
            // consulting the error log ...
            if ($exception instanceof ClientException) {
                $contents = Json::decode($exception->getResponse()->getBody()->getContents());
                $this->_errors = $contents['errors'] ?? [$exception->getMessage() ?: 'Unknown API Error'];
                Yii::debug($this->_errors);
            }

            Yii::error($exception);
        }

        return null;
    }

    /**
     * Finds "next" link in header for paginated results.
     * For more information see: https://shopify.dev/api/usage/pagination-rest
     */
    private function getNextLinkFromHeader(array $headers): ?string
    {
        if ($links = explode(',', $headers['Link'][0] ?? '')) {
            foreach ($links as $link) {
                if (preg_match('/<(.*)>;\srel=\"next\"/', $link, $matches)) {
                    return $matches[1];
                }
            }
        }

        return null;
    }

    public function getErrors(): array
    {
        return $this->_errors;
    }

    public function getClient(): Client
    {
        $this->_client ??= new Client();
        return $this->_client;
    }
}