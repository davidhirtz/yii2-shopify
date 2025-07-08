<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components;

use davidhirtz\yii2\shopify\components\admin\AdminApi;
use Yii;
use yii\base\Component;

class ShopifyComponent extends Component
{
    private const string API_VERSION = '2025-07';

    public ?string $shopifyAccessToken = null;
    public string $shopifyApiKey;
    public string $shopifyApiSecret;
    public ?string $shopifyShopDomain;
    public string $shopifyShopName;
    public ?string $shopifyStorefrontAccessToken = null;
    public string $shopifyApiVersion = self::API_VERSION;

    public function init(): void
    {
        $this->shopifyShopName ??= Yii::$app->params['shopifyShopName'];
        $this->shopifyShopDomain ??= Yii::$app->params['shopifyShopDomain'] ?? null;

        $this->shopifyShopDomain = $this->shopifyShopDomain
            ? rtrim((string)preg_replace('(^https??//)', '', (string)$this->shopifyShopDomain), '/')
            : "$this->shopifyShopName.myshopify.com";

        $this->shopifyApiKey ??= Yii::$app->params['shopifyApiKey'];
        $this->shopifyApiSecret ??= Yii::$app->params['shopifyApiSecret'];
        $this->shopifyAccessToken ??= Yii::$app->params['shopifyAccessToken'] ?? null;
        $this->shopifyStorefrontAccessToken ??= Yii::$app->params['shopifyStorefrontAccessToken'] ?? null;

        parent::init();
    }

    public function getAdminApi(): AdminApi
    {
        return new AdminApi($this);
    }

    public function getShopUrl(string $query = ''): string
    {
        return "https://$this->shopifyShopDomain/$query";
    }
}
