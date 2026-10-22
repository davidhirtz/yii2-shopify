<?php

declare(strict_types=1);

namespace Hirtz\Shopify;

use Yii;

class Module extends \Hirtz\Skeleton\Base\Module
{
    /**
     * @var list<array{topic: string, route: array<int|string, mixed>}>
     */
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

    /**
     * The same for every admin: Shopify subscribes each address anew.
     *
     * @param array<int|string, mixed> $route
     */
    public function getWebhookAddress(array $route): string
    {
        return Yii::$app->getUrlManager()->createCanonicalUrl($route);
    }
}
