<?php
/**
 * Entries
 * @see \davidhirtz\yii2\cms\modules\admin\controllers\ProductController::actionIndex()
 *
 * @var View $this
 * @var ProductActiveDataProvider $provider
 */

use davidhirtz\yii2\shopify\modules\admin\data\ProductActiveDataProvider;
use davidhirtz\yii2\shopify\modules\admin\widgets\grid\ProductGridView;
use davidhirtz\yii2\shopify\modules\admin\widgets\nav\ShopifySubmenu;
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