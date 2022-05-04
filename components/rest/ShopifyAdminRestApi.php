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
    public $shopifyAccessToken;

    /**
     * @var string
     */
    public $shopifyApiVersion;

    /**
     * @var string
     */
    public $shopDomain;

    /**
     * @var string
     */
    public $latestShopifyApiVersion = '2022-04';

    /**
     * @var array
     */
    private $_errors = [];

    /**
     * @var Client
     */
    private $_client;

    /**
     * @return void
     */
    public function init()
    {
        if (!$this->shopifyApiVersion) {
            $this->shopifyApiVersion = $this->latestShopifyApiVersion;
        }

        parent::init();
    }

    /**
     * @return array
     */
    public function getProducts(): array
    {
        $results = $this->get('products', ['limit' => static::SHOPIFY_MAX_PRODUCT_LIMIT], [
            'X-Shopify-Api-Features' => 'include-presentment-prices',
        ]);

        return $results['products'] ?? [];
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function getProduct($id)
    {
        $results = $this->get("products/{$id}", [], [
            'X-Shopify-Api-Features' => 'include-presentment-prices',
        ]);

        return $results['product'] ?? [];
    }

    /**
     * @return array
     */
    public function getWebhooks(): array
    {
        $results = $this->get('products', ['limit' => static::SHOPIFY_MAX_PRODUCT_LIMIT]);
        return $results['webhooks'] ?? [];
    }

    /**
     * @param string $endpoint
     * @param array $query
     * @param array $headers
     * @return array
     */
    public function get($endpoint, $query = [], $headers = []): ?array
    {
        return $this->request('GET', $endpoint, $query, $headers);
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array $query
     * @param array $headers
     * @return array|null
     */
    public function request($method, $endpoint, $query = [], $headers = []): ?array
    {
        $uri = "https://{$this->shopDomain}/admin/api/{$this->shopifyApiVersion}/{$endpoint}.json";
        $headers['X-Shopify-Access-Token'] = $this->shopifyAccessToken;

        try {
            $request = $this->getClient()->request($method, $uri, [
                'headers' => $headers,
                'query' => $query,
                'on_stats' => function (TransferStats $stats) use (&$url) {
                    Yii::debug("Requesting Shopify Admin REST API: {$stats->getEffectiveUri()}");
                },
            ]);

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
                $this->_errors[] = $contents['errors'] ?? $exception->getMessage() ?: 'Unknown API Error';
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