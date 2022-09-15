<?php

namespace davidhirtz\yii2\shopify;

use davidhirtz\yii2\shopify\components\rest\ShopifyAdminRestApi;
use davidhirtz\yii2\skeleton\modules\ModuleTrait;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class Module
 * @package davidhirtz\yii2\shopify
 */
class Module extends \yii\base\Module
{
    use ModuleTrait;

    /**
     * @var string
     */
    public $shopifyShopName;

    /**
     * @var string|null optional custom shopify domain
     */
    public $shopifyShopDomain;

    /**
     * @var string
     */
    public $shopifyApiKey;

    /**
     * @var string
     */
    public $shopifyApiSecret;

    /**
     * @var string
     */
    public $shopifyAccessToken;

    /**
     * @var string
     */
    public $shopifyStorefrontAccessToken;

    /**
     * @var string
     */
    public $shopifyApiVersion;

    /**
     * @var string
     */
    public $latestShopifyApiVersion = '2022-07';

    /**
     * @var array
     */
    public $webhooks = [
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

    /**
     * @var ShopifyAdminRestApi
     */
    private $_api;

    /**
     * @return void
     */
    public function init()
    {
        if ($this->enableI18nTables) {
            throw new InvalidConfigException('Shopify module does not support I18N database tables.');
        }

        $this->shopifyShopName ??= Yii::$app->params['shopifyShopName'] ?? null;

        $this->shopifyShopDomain ??= Yii::$app->params['shopifyShopDomain'] ?? "{$this->shopifyShopName}.myshopify.com";
        $this->shopifyShopDomain = parse_url($this->shopifyShopDomain, PHP_URL_HOST);

        $this->shopifyApiKey ??= Yii::$app->params['shopifyApiKey'] ?? null;
        $this->shopifyApiSecret ??= Yii::$app->params['shopifyApiSecret'] ?? null;
        $this->shopifyAccessToken ??= Yii::$app->params['shopifyAccessToken'] ?? null;
        $this->shopifyStorefrontAccessToken ??= Yii::$app->params['shopifyStorefrontAccessToken'] ?? null;
        $this->shopifyApiVersion ??= $this->latestShopifyApiVersion;

        if (!$this->shopifyShopName) {
            throw new InvalidConfigException('Shopify shop name must be set. Either via "Module::$shopifyShopName" or via "shopifyShopName" param.');
        }

        if (!$this->shopifyAccessToken) {
            throw new InvalidConfigException('Shopify Admin REST API access token must be set. Either via "Module::$shopifyAccessToken" or via "shopifyAccessToken" param.');
        }


        parent::init();
    }

    /**
     * @param string $query
     * @return string
     */
    public function getShopUrl($query = ''): string
    {
        return "{$this->shopifyShopDomain}/{$query}";
    }

    /**
     * @return ShopifyAdminRestApi
     */
    public function getApi()
    {
        if ($this->_api === null) {
            $this->_api = new ShopifyAdminRestApi([
                'shopifyAccessToken' => $this->shopifyAccessToken,
                'shopifyApiVersion' => $this->shopifyApiVersion,
                'shopifyShopName' => $this->shopifyShopName,
            ]);
        }

        return $this->_api;
    }
}