<?php

declare(strict_types=1);

/**
 * @see WebhookController::actionIndex()
 *
 * @var View $this
 * @var WebhookSubscriptionArrayDataProvider $provider
 */

use Hirtz\Shopify\Modules\Admin\Controllers\WebhookController;
use Hirtz\Shopify\Modules\Admin\Data\WebhookSubscriptionArrayDataProvider;
use Hirtz\Shopify\Modules\Admin\Widgets\Grids\WebhookSubscriptionGridView;
use Hirtz\Shopify\Modules\Admin\Widgets\Navs\WebhookHeader;
use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Grids\GridContainer;

echo WebhookHeader::make()
    ->provider($provider);

echo GridContainer::make()
    ->grid(WebhookSubscriptionGridView::make()
        ->provider($provider));
