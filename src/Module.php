<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify;

use davidhirtz\yii2\shopify\components\ShopifyComponent;
use davidhirtz\yii2\skeleton\modules\ModuleTrait;
use Yii;
use yii\base\InvalidConfigException;

class Module extends \yii\base\Module
{
    use ModuleTrait;

    /**
     * @var string|null the Shopify API secret, defaults to params `shopifyApiSecret`.
     */
    public ?string $shopifyApiSecret = null;


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

    private ?ShopifyComponent $_api = null;

    public function init(): void
    {
        if ($this->enableI18nTables) {
            throw new InvalidConfigException('Shopify module does not support I18N database tables.');
        }

        parent::init();
    }
}
