<?php

declare(strict_types=1);

/**
 * @see ProductController::actionIndex()
 *
 * @var View $this
 * @var ProductActiveDataProvider $provider
 */

use Hirtz\Shopify\Modules\Admin\Controllers\ProductController;
use Hirtz\Shopify\Modules\Admin\Data\ProductActiveDataProvider;
use Hirtz\Shopify\Modules\Admin\Widgets\Grids\ProductGridView;
use Hirtz\Shopify\Modules\Admin\Widgets\Navs\ProductHeader;
use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Grids\GridContainer;

echo ProductHeader::make()
    ->provider($provider);

echo GridContainer::make()
    ->grid(ProductGridView::make()
        ->provider($provider));
