<?php

declare(strict_types=1);

namespace Hirtz\Shopify;

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
}
