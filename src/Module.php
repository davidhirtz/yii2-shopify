<?php

declare(strict_types=1);

namespace Hirtz\Shopify;

use Hirtz\Skeleton\Web\Request;
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
     * Without a language prefix: the admin's own language would add one, and Shopify subscribes each address anew.
     *
     * @param array<int|string, mixed> $route
     */
    public function getWebhookAddress(array $route): string
    {
        $urlManager = Yii::$app->getUrlManager();

        if ($urlManager->i18nUrl) {
            $route[Request::current()->languageParam ?? 'language'] = false;
        }

        return $urlManager->createAbsoluteUrl($route);
    }
}
