<?php

declare(strict_types=1);

namespace Hirtz\Shopify;

use Hirtz\Shopify\Components\Rest\ShopifyAdminRestApi;
use Hirtz\Skeleton\Modules\ModuleTrait;
use Override;
use Yii;
use yii\base\InvalidConfigException;

class Module extends \yii\base\Module
{
    use ModuleTrait;

    public ?string $shopifyShopName;
    public ?string $shopifyShopDomain;
    public ?string $shopifyApiKey;
    public ?string $shopifyApiSecret;
    public ?string $shopifyAccessToken;
    public ?string $shopifyStorefrontAccessToken;
    public ?string $shopifyApiVersion;
    public string $latestShopifyApiVersion = '2026-01';

    public array $webhooks = [
        [
            'topic' => 'products/create',
            'route' => ['/shopify/webhook/products-create'],
        ],
        [
            'topic' => 'products/update',
            'route' => ['/shopify/webhook/products-update'],
        ],
        [
            'topic' => 'products/delete',
            'route' => ['/shopify/webhook/products-delete'],
        ],
    ];

    private ShopifyAdminRestApi $_api;

    #[Override]
    public function init(): void
    {
        if ($this->enableI18nTables) {
            throw new InvalidConfigException('Shopify module does not support I18N database tables.');
        }

        $this->shopifyShopName ??= Yii::$app->params['shopifyShopName'] ?? null;

        $this->shopifyShopDomain ??= Yii::$app->params['shopifyShopDomain'] ?? "$this->shopifyShopName.myshopify.com";
        $this->shopifyShopDomain = rtrim((string)preg_replace('(^https??//)', '', (string)$this->shopifyShopDomain), '/');

        $this->shopifyApiKey ??= Yii::$app->params['shopifyApiKey'] ?? null;
        $this->shopifyApiSecret ??= Yii::$app->params['shopifyApiSecret'] ?? null;
        $this->shopifyAccessToken ??= Yii::$app->params['shopifyAccessToken'] ?? null;
        $this->shopifyStorefrontAccessToken ??= Yii::$app->params['shopifyStorefrontAccessToken'] ?? null;
        $this->shopifyApiVersion ??= $this->latestShopifyApiVersion;

        parent::init();
    }

    public function getShopUrl(string $query = ''): string
    {
        return "https://$this->shopifyShopDomain/$query";
    }

    public function getApi(): ShopifyAdminRestApi
    {
        $this->_api ??= new ShopifyAdminRestApi([
            'shopifyAccessToken' => $this->shopifyAccessToken,
            'shopifyApiVersion' => $this->shopifyApiVersion,
            'shopifyShopName' => $this->shopifyShopName,
        ]);

        return $this->_api;
    }
}
