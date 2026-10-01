<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Tests\Modules\Admin\Widgets;

use Hirtz\Shopify\Models\Webhook;
use Hirtz\Shopify\Models\WebhookSubscription;
use Hirtz\Shopify\Modules\Admin\Data\WebhookSubscriptionArrayDataProvider;
use Hirtz\Shopify\Modules\Admin\Widgets\Grids\WebhookSubscriptionGridView;
use Hirtz\Shopify\Test\TestCase;

/**
 * The provider answers what the Admin API lists, so the grid renders `WebhookSubscription`, not the `Webhook` a
 * project declares.
 */
class WebhookSubscriptionGridViewTest extends TestCase
{
    public function testASubscriptionIsRendered(): void
    {
        $subscription = WebhookSubscription::create();
        $subscription->id = 99;
        $subscription->api_version = '2026-07';
        $subscription->callbackUrl = 'https://www.domain.localhost/shopify/webhook/products-update';
        $subscription->topic = 'PRODUCTS_UPDATE';
        $subscription->format = 'JSON';

        $html = (string)WebhookSubscriptionGridView::make()
            ->provider(new WebhookSubscriptionArrayDataProvider(['allModels' => [$subscription]]));

        self::assertStringContainsString(Webhook::getTopics()['products/update'], $html);
        self::assertStringContainsString($subscription->callbackUrl, $html);
        self::assertStringContainsString('2026-07', $html);
        self::assertStringContainsString('JSON', $html);
        self::assertStringContainsString('id=99', $html);
    }
}
