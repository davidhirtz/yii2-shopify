<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Migrations;

use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Models\Webhook;
use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Skeleton\I18n\Message;
use Hirtz\Skeleton\Models\User;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */
class M260914160000AuthItems extends Migration
{
    use MigrationTrait;

    private const array LEGACY_PRODUCT = ['shopifyProductUpdate'];
    private const array LEGACY_WEBHOOK = ['shopifyWebhookUpdate'];

    public function safeUp(): void
    {
        $this->addPermission(Product::AUTH_SHOPIFY_PRODUCT, $this->getProductDescription(), User::AUTH_ROLE_ADMIN);
        $this->replaceAuthItems(self::LEGACY_PRODUCT, Product::AUTH_SHOPIFY_PRODUCT);

        $this->addPermission(Webhook::AUTH_SHOPIFY_WEBHOOK, $this->getWebhookDescription(), User::AUTH_ROLE_ADMIN);
        $this->replaceAuthItems(self::LEGACY_WEBHOOK, Webhook::AUTH_SHOPIFY_WEBHOOK);
    }

    public function safeDown(): void
    {
        $this->restoreAuthItems(self::LEGACY_WEBHOOK, Webhook::AUTH_SHOPIFY_WEBHOOK, $this->getWebhookDescription());
        $this->restoreAuthItems(self::LEGACY_PRODUCT, Product::AUTH_SHOPIFY_PRODUCT, $this->getProductDescription());
    }

    private function getProductDescription(): Message
    {
        return Message::make('shopify', 'AUTH_SHOPIFY_PRODUCT_DESCRIPTION');
    }

    private function getWebhookDescription(): Message
    {
        return Message::make('shopify', 'AUTH_SHOPIFY_WEBHOOK_DESCRIPTION');
    }
}
