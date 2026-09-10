<?php

declare(strict_types=1);

namespace Hirtz\Shopify;

use Override;
use Yii;

class Module extends \yii\base\Module
{
    public ?string $shopifyShopName = null;
    public ?string $shopifyShopDomain = null;
    public ?string $shopifyApiKey = null;
    public ?string $shopifyApiSecret = null;
    public ?string $shopifyAccessToken = null;
    public ?string $shopifyStorefrontAccessToken = null;
    public ?string $shopifyApiVersion = null;
    public string $latestShopifyApiVersion = '2026-01';

    public array $webhooks = [
        [
            'topic' => 'PRODUCTS_CREATE',
            'route' => ['/shopify/webhook/products-create'],
        ],
        [
            'topic' => 'PRODUCTS_UPDATE',
            'route' => ['/shopify/webhook/products-update'],
        ],
        [
            'topic' => 'PRODUCTS_DELETE',
            'route' => ['/shopify/webhook/products-delete'],
        ],
    ];

    #[Override]
    public function init(): void
    {
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
}
