<?php

namespace davidhirtz\yii2\shopify\components\rest;

use davidhirtz\yii2\skeleton\helpers\ArrayHelper;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\TransferStats;
use Yii;
use yii\base\BaseObject;

/**
 * Class ShopifyAdminRestApi
 * @package davidhirtz\yii2\shopify\models
 *
 */
class ShopifyAdminRestApi extends BaseObject
{
    public const SHOPIFY_MAX_PRODUCT_LIMIT = 250;

    /**
     * @var string
     */
    public $shopifyShopName;

    /**
     * @var string
     */
    public $shopifyAccessToken;

    /**
     * @var string
     */
    public $shopifyApiVersion;

    /**
     * @var array
     */
    private $_errors = [];

    /**
     * @var Client
     */
    private $_client;

    /**
     * @return array
     */
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

    /**
     * @param int $id
     * @return array|null
     */
    public function getProduct($id)
    {
        $results = $this->get("products/{$id}", [
            'headers' => [
                'X-Shopify-Api-Features' => 'include-presentment-prices',
            ],
        ]);

        return $results['product'] ?? [];
    }

    /**
     * @return array
     */
    public function getWebhooks(): array
    {
        $results = $this->get('webhooks', [
            'query' => [
                'limit' => static::SHOPIFY_MAX_PRODUCT_LIMIT,
            ],
        ]);

        return $results['webhooks'] ?? [];
    }

    /**
     * @param array $params
     * @return array
     */
    public function setWebhook($params): array
    {
        $results = $this->post('webhooks', [
            'form_params' => [
                'webhook' => $params,
            ],
        ]);

        return $results['webhook'] ?? [];
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteWebhook($id)
    {
        $this->request('DELETE', "webhooks/{$id}");
        return empty($this->getErrors());
    }

    /**
     * @param string $endpoint
     * @param array $options
     * @return array
     */
    public function get($endpoint, $options = []): ?array
    {
        return $this->request('GET', $endpoint, $options);
    }

    /**
     * @param string $endpoint
     * @param array $options
     * @return array
     */
    public function post($endpoint, $options = []): ?array
    {
        return $this->request('POST', $endpoint, $options);
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array $options
     * @return array|null
     */
    public function request($method, $endpoint, $options = []): ?array
    {
        $uri = "https://{$this->shopifyShopName}.myshopify.com/admin/api/{$this->shopifyApiVersion}/{$endpoint}.json";

        $options['headers']['X-Shopify-Access-Token'] = $this->shopifyAccessToken;
        $options['on_stats'] = function (TransferStats $stats) use (&$url) {
            Yii::debug("Requesting Shopify Admin REST API: {$stats->getEffectiveUri()}");
        };

        try {
            $request = $this->getClient()->request($method, $uri, $options);
            $content = json_decode($request->getBody()->getContents(), true);

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
                $contents = json_decode($exception->getResponse()->getBody()->getContents(), true);
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
     * @param array $headers
     * @return false|mixed
     */
    private function getNextLinkFromHeader($headers)
    {
        if ($links = explode(',', $headers['Link'][0] ?? '')) {
            foreach ($links as $link) {
                if (preg_match('/<(.*)>;\srel=\"next\"/', $link, $matches)) {
                    return $matches[1];
                }
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->_errors;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        if ($this->_client === null) {
            $this->_client = new Client();
        }

        return $this->_client;
    }
}