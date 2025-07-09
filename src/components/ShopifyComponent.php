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
    public ?string $shopifyApiKey;
    public ?string $shopifyApiSecret;
    public ?string $shopifyShopDomain;
    public ?string $shopifyShopName;
    public ?string $shopifyStorefrontAccessToken = null;
    public string $shopifyApiVersion = self::API_VERSION;

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

    public function getAdminApi(): AdminApi
    {
        if(!isset($this->shopifyShopName, $this->shopifyAccessToken)) {
            throw new InvalidConfigException('Shopify shop name and access token must be set.');
        }

        return new AdminApi(
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
