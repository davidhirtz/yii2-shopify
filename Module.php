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

    public const AUTH_SHOPIFY_ADMIN = 'shopifyAdmin';

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

        if (!$this->shopDomain) {
            $this->shopDomain = Yii::$app->params['shopifyShopDomain'] ?? null;
        }

        if (!$this->shopDomain) {
            throw new InvalidConfigException('Shopify shop domain must be set. Either via "Module::$shopDomain" or via "shopifyShopDomain" param.');
        }

        if (!$this->shopifyAccessToken) {
            $this->shopifyAccessToken = Yii::$app->params['shopifyAccessToken'] ?? null;
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