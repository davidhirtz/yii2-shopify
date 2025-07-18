<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify;

use davidhirtz\yii2\skeleton\modules\ModuleTrait;
use Override;
use yii\base\InvalidConfigException;

class Module extends \yii\base\Module
{
    use ModuleTrait;

    public string $defaultCurrency = 'EUR';

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
        if ($this->enableI18nTables) {
            throw new InvalidConfigException('Shopify module does not support I18N database tables.');
        }

        parent::init();
    }
}
