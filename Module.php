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
    public $shopifyApiVersion;

    /**
     * @var string
     */
    public $latestShopifyApiVersion = '2022-04';

    /**
     * @var string
     */
    public $shopDomain;

    /**
     * @var array
     */
    public $webhooks = [
        [
            'topic' => 'products/create',
            'route' => ['/shopify/webhook/products-update'],
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

        $this->shopifyApiKey = $this->shopifyApiKey ?: Yii::$app->params['shopifyApiKey'] ?? null;
        $this->shopifyApiSecret = $this->shopifyApiSecret ?: Yii::$app->params['shopifyApiSecret'] ?? null;
        $this->shopifyAccessToken = $this->shopifyAccessToken ?: Yii::$app->params['shopifyAccessToken'] ?? null;
        $this->shopDomain = $this->shopDomain ?: Yii::$app->params['shopifyShopDomain'] ?? null;
        $this->shopifyApiVersion = $this->shopifyApiVersion ?: $this->latestShopifyApiVersion;

        if (!$this->shopDomain) {
            throw new InvalidConfigException('Shopify shop domain must be set. Either via "Module::$shopDomain" or via "shopifyShopDomain" param.');
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
        return "https://{$this->shopDomain}/{$query}";
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
                'shopDomain' => $this->shopDomain,
            ]);
        }

        return $this->_api;
    }
}