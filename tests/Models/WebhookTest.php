<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Tests\Models;

use Hirtz\Shopify\Components\ComponentTrait;
use Hirtz\Shopify\Models\Webhook;
use Hirtz\Shopify\Test\TestCase;
use Yii;

/**
 * The webhook is what is registered with Shopify, so its address and API version have to be the ones this
 * installation actually answers on.
 */
class WebhookTest extends TestCase
{
    use ComponentTrait;
    public function testTheAddressIsBuiltFromTheRouteAndTheVersionFromTheComponent(): void
    {
        $webhook = Webhook::create();
        $webhook->topic = 'products/update';
        $webhook->route = ['/shopify/webhook/products-update'];

        self::assertTrue($webhook->validate(), print_r($webhook->getErrors(), true));

        self::assertStringEndsWith('/shopify/webhook/products-update', $webhook->address);

        // The component is what `AdminApi` calls the API with, so the subscription must name the same version.
        self::assertSame(static::getShopify()->shopifyApiVersion, $webhook->api_version);
    }

    public function testAnAddressAndAVersionThatAreGivenAreKept(): void
    {
        $webhook = Webhook::create();
        $webhook->topic = 'products/delete';
        $webhook->address = 'https://www.example.com/hook';
        $webhook->api_version = '2020-01';

        self::assertTrue($webhook->validate());
        self::assertSame('https://www.example.com/hook', $webhook->address);
        self::assertSame('2020-01', $webhook->api_version);
    }

    public function testAWebhookNeedsATopic(): void
    {
        $webhook = Webhook::create();
        $webhook->route = ['/shopify/webhook/products-update'];

        self::assertFalse($webhook->validate());
        self::assertArrayHasKey('topic', $webhook->getErrors());
    }

    public function testOnlyJsonAndXmlAreAcceptedAsAFormat(): void
    {
        $webhook = Webhook::create();
        $webhook->topic = 'products/update';
        $webhook->route = ['/shopify/webhook/products-update'];
        $webhook->format = 'yaml';

        self::assertFalse($webhook->validate());
        self::assertArrayHasKey('format', $webhook->getErrors());
    }

    public function testTheTopicIsNamedInTheAdminLanguage(): void
    {
        $webhook = Webhook::create();
        $webhook->topic = 'products/update';

        self::assertSame(Webhook::getTopics()['products/update'], $webhook->getFormattedTopic());

        $webhook->topic = 'orders/paid';
        self::assertSame('Orders paid', $webhook->getFormattedTopic());
    }
}
