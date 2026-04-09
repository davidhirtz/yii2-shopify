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
use Hirtz\Shopify\Modules\Admin\Widgets\Navs\ShopifySubmenu;
use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Grids\GridContainer;

$this->title(Yii::t('shopify', 'Products'));
$this->addBreadcrumb(Yii::t('shopify', 'Products'), ['/admin/shopify/product/index']);

echo ShopifySubmenu::make();

echo GridContainer::make()
    ->grid(ProductGridView::make()
        ->provider($provider));
