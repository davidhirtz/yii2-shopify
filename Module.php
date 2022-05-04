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
            throw new InvalidConfigException('The shopify module does not support I18N database tables.');
        }

        if (!$this->shopDomain) {
            $this->shopDomain = Yii::$app->params['shopifyShopDomain'] ?? null;
        }

        if (!$this->shopifyAccessToken) {
            $this->shopifyAccessToken = Yii::$app->params['shopifyAccessToken'] ?? null;
        }

        parent::init();
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