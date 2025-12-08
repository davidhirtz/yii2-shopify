<?php
declare(strict_types=1);

/**
 * @see ProductController::actionIndex()
 *
 * @var View $this
 * @var ProductActiveDataProvider $provider
 */

use Hirtz\Shopify\modules\admin\controllers\ProductController;
use Hirtz\Shopify\modules\admin\data\ProductActiveDataProvider;
use Hirtz\Shopify\modules\admin\widgets\grids\ProductGridView;
use Hirtz\Shopify\modules\admin\widgets\navs\ShopifySubmenu;
use Hirtz\Skeleton\web\View;
use Hirtz\Skeleton\widgets\bootstrap\Panel;

$this->title(Yii::t('shopify', 'Products'));
$this->setBreadcrumb(Yii::t('shopify', 'Products'), ['/admin/product/index']);
?>

<?= ShopifySubmenu::widget(); ?>

<?= Panel::widget([
    'content' => ProductGridView::widget([
        'dataProvider' => $provider,
    ]),
]); ?>
