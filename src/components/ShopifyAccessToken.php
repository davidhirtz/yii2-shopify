<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Yii;
use yii\base\InvalidConfigException;
use yii\caching\CacheInterface;

final readonly class ShopifyAccessToken
{
    private const string CACHE_KEY = 'shopify_access_token';
    private const array REQUIRED_SCOPES = ['read_inventory', 'read_products'];

    private CacheInterface $cache;

    public function __construct(
        private string $shopifyShopName,
        private string $shopifyApiKey,
        private string $shopifyApiSecret,
    ) {
        $this->cache = Yii::$app->getCache();
    }

    public function __invoke(): string
    {
        $accessToken = $this->cache->get(self::CACHE_KEY);

        if (!$accessToken) {
            Yii::debug('Retrieving new Shopify access token from API.');

            try {
                $request = (new Client())->post("https://$this->shopifyShopName.myshopify.com/admin/oauth/access_token", [
                    'form_params' => [
                        'client_id' => $this->shopifyApiKey,
                        'client_secret' => $this->shopifyApiSecret,
                        'grant_type' => 'client_credentials',
                    ],
                ]);
            } catch (ClientException $e) {
                Yii::error($e->getMessage());
                throw new InvalidConfigException('Failed to retrieve Shopify access token (HTTP Error ' . $e->getResponse()->getStatusCode() . ')');
            }

            $data = json_decode($request->getBody()->getContents());

            if (!isset($data->access_token, $data->scope, $data->expires_in)) {
                throw new InvalidConfigException('Shopify access token is invalid.');
            }

            foreach (self::REQUIRED_SCOPES as $scope) {
                if (!str_contains($data->scope, $scope)) {
                    throw new InvalidConfigException("Shopify access token is missing required scope: $scope");
                }
            }

            $accessToken = $data->access_token;
            $this->cache->set(self::CACHE_KEY, $accessToken, $data->expires_in);
        }

        return $accessToken;
    }
}
