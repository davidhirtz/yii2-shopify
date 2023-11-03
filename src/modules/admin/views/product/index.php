<?php
/**
 * @see ProductController::actionIndex()
 *
 * @var View $this
 * @var ProductActiveDataProvider $provider
 */

use davidhirtz\yii2\shopify\modules\admin\controllers\ProductController;
use davidhirtz\yii2\shopify\modules\admin\data\ProductActiveDataProvider;
use davidhirtz\yii2\shopify\modules\admin\widgets\grids\ProductGridView;
use davidhirtz\yii2\shopify\modules\admin\widgets\navs\ShopifySubmenu;
use davidhirtz\yii2\skeleton\web\View;
use davidhirtz\yii2\skeleton\widgets\bootstrap\Panel;

$this->setTitle(Yii::t('shopify', 'Products'));
$this->setBreadcrumb(Yii::t('shopify', 'Products'), ['/admin/product/index']);
?>

<?= ShopifySubmenu::widget(); ?>

<?= Panel::widget([
    'content' => ProductGridView::widget([
        'dataProvider' => $provider,
    ]),
]); ?>