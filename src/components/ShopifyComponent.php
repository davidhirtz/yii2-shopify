<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components;

use davidhirtz\yii2\shopify\components\admin\AdminApi;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class ShopifyComponent extends Component
{
    private const string API_VERSION = '2025-07';

    public ?string $shopifyAccessToken = null;
    public ?string $shopifyApiKey = null;
    public ?string $shopifyApiSecret = null;
    public ?string $shopifyShopDomain = null;
    public ?string $shopifyShopName = null;
    public ?string $shopifyStorefrontAccessToken = null;
    public string $shopifyApiVersion = self::API_VERSION;
    public string $defaultCurrency = 'EUR';

    private AdminApi $api;

    public function init(): void
    {
        $this->shopifyShopName ??= Yii::$app->params['shopifyShopName'] ?? null;
        $this->shopifyShopDomain ??= Yii::$app->params['shopifyShopDomain'] ?? null;

        $this->shopifyShopDomain = $this->shopifyShopDomain
            ? rtrim((string)preg_replace('(^https??//)', '', (string)$this->shopifyShopDomain), '/')
            : "$this->shopifyShopName.myshopify.com";

        $this->shopifyApiKey ??= Yii::$app->params['shopifyApiKey'] ?? null;
        $this->shopifyApiSecret ??= Yii::$app->params['shopifyApiSecret'] ?? null;
        $this->shopifyAccessToken ??= Yii::$app->params['shopifyAccessToken'] ?? null;
        $this->shopifyStorefrontAccessToken ??= Yii::$app->params['shopifyStorefrontAccessToken'] ?? null;

        parent::init();
    }

    public function validateHmac(string $hmacHeader, string $data): bool
    {
        if (!$this->shopifyApiSecret) {
            throw new InvalidConfigException('Shopify API secret must be set to validate HMAC.');
        }

        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, $this->shopifyApiSecret, true));
        return hash_equals($hmacHeader, $calculatedHmac);
    }

    public function getAdminApi(): AdminApi
    {
        if (!isset($this->shopifyShopName, $this->shopifyAccessToken)) {
            throw new InvalidConfigException('Shopify shop name and access token must be set.');
        }

        return $this->api ??= new AdminApi(
            $this->shopifyShopName,
            $this->shopifyAccessToken,
            $this->shopifyApiVersion
        );
    }

    public function getShopUrl(string $query = ''): string
    {
        return "https://$this->shopifyShopDomain/$query";
    }
}
