<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Components;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yii;
use yii\base\InvalidConfigException;
use yii\caching\CacheInterface;
use yii\helpers\Json;

/**
 * An app created in the Shopify Dev Dashboard has no permanent Admin API token: it exchanges its client id and
 * secret for one that expires, which is cached until shortly before it does.
 *
 * @see https://shopify.dev/docs/apps/build/authentication-authorization/access-tokens/client-credentials-grant
 */
class ShopifyAccessToken
{
    /**
     * A `write_` scope grants the `read_` one, and Shopify then lists only the former.
     */
    private const array REQUIRED_SCOPES = ['products', 'inventory'];

    private const int EXPIRY_MARGIN = 60;

    public function __construct(
        private readonly string $shopifyShopName,
        private readonly string $shopifyApiKey,
        private readonly string $shopifyApiSecret,
        private readonly ?CacheInterface $cache = null,
    ) {
    }

    public function __invoke(): string
    {
        $key = [self::class, $this->shopifyShopName, $this->shopifyApiKey];
        $accessToken = $this->cache?->get($key);

        if (is_string($accessToken) && $accessToken !== '') {
            return $accessToken;
        }

        Yii::debug('Requesting a Shopify access token through the client credentials grant');

        try {
            $response = $this->createClient()->post("https://$this->shopifyShopName.myshopify.com/admin/oauth/access_token", [
                'form_params' => [
                    'client_id' => $this->shopifyApiKey,
                    'client_secret' => $this->shopifyApiSecret,
                    'grant_type' => 'client_credentials',
                ],
            ]);
        } catch (GuzzleException $exception) {
            Yii::error($exception->getMessage());
            throw new InvalidConfigException('Shopify refused the client credentials of "' . $this->shopifyShopName . '".', 0, $exception);
        }

        $data = Json::decode($response->getBody()->getContents());
        $accessToken = $data['access_token'] ?? null;
        $scope = $data['scope'] ?? null;
        $expiresIn = $data['expires_in'] ?? null;

        if (!is_string($accessToken) || $accessToken === '' || !is_string($scope) || !is_int($expiresIn)) {
            throw new InvalidConfigException('Shopify answered the client credentials grant without an access token.');
        }

        $scopes = explode(',', $scope);

        foreach (self::REQUIRED_SCOPES as $required) {
            if (!array_intersect(["read_$required", "write_$required"], $scopes)) {
                throw new InvalidConfigException("The Shopify app lacks the \"read_$required\" scope.");
            }
        }

        $this->cache?->set($key, $accessToken, max(1, $expiresIn - self::EXPIRY_MARGIN));

        return $accessToken;
    }

    protected function createClient(): Client
    {
        return new Client();
    }
}
