<?php

namespace davidhirtz\yii2\shopify;

use davidhirtz\yii2\shopify\components\rest\ShopifyAdminRestApi;
use davidhirtz\yii2\skeleton\modules\ModuleTrait;
use Yii;
use yii\base\InvalidConfigException;

class Module extends \yii\base\Module
{
    use ModuleTrait;

    /**
     * @var string|null the Shopify shop name, defaults to params `shopifyShopDomain` domain name.
     */
    public ?string $shopifyShopName = null;

    /**
     * @var string|null the optional custom Shopify shop domain
     */
    public ?string $shopifyShopDomain = null;

    /**
     * @var string|null the Shopify API key, defaults to params `shopifyApiKey`.
     */
    public ?string $shopifyApiKey = null;

    /**
     * @var string|null the Shopify API secret, defaults to params `shopifyApiSecret`.
     */
    public ?string $shopifyApiSecret = null;

    /**
     * @var string|null the Shopify Admin REST API access token, defaults to params `shopifyAccessToken`.
     */
    public ?string $shopifyAccessToken = null;

    /**
     * @var string|null the Shopify Storefront API access token, defaults to params `shopifyStorefrontAccessToken`.
     */
    public ?string $shopifyStorefrontAccessToken = null;

    /**
     * @var string|null the Shopify Admin REST API version, defaults to the latest version.
     */
    public ?string $shopifyApiVersion = null;

    /**
     * @var string the latest Shopify Admin REST API version supported by this module.
     */
    protected string $latestShopifyApiVersion = '2024-01';

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

    private ?ShopifyAdminRestApi $_api = null;

    public function init(): void
    {
        if ($this->enableI18nTables) {
            throw new InvalidConfigException('Shopify module does not support I18N database tables.');
        }

        $this->shopifyShopName ??= Yii::$app->params['shopifyShopName'] ?? null;

        $this->shopifyShopDomain ??= Yii::$app->params['shopifyShopDomain'] ?? "$this->shopifyShopName.myshopify.com";
        $this->shopifyShopDomain = rtrim((string) preg_replace('(^https?://)', '', (string)$this->shopifyShopDomain), '/');

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
