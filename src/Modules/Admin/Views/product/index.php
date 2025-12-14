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
use Hirtz\Skeleton\Widgets\Bootstrap\Panel;

$this->title(Yii::t('shopify', 'Products'));
$this->setBreadcrumb(Yii::t('shopify', 'Products'), ['/admin/product/index']);
?>

<?= ShopifySubmenu::widget(); ?>

<?= Panel::widget([
    'content' => ProductGridView::widget([
        'dataProvider' => $provider,
    ]),
]); ?>
