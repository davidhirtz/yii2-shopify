<?php
/**
 * Webhooks
 * @see \davidhirtz\yii2\cms\modules\admin\controllers\WebhookController::actionIndex()
 *
 * @var View $this
 * @var ProductActiveDataProvider $provider
 */

use davidhirtz\yii2\shopify\modules\admin\data\ProductActiveDataProvider;
use davidhirtz\yii2\shopify\modules\admin\widgets\grid\ProductGridView;
use davidhirtz\yii2\skeleton\web\View;
use davidhirtz\yii2\skeleton\widgets\bootstrap\Panel;

$this->setTitle(Yii::t('shopify', 'Webhooks'));
?>

<?= Panel::widget([
    'content' => ProductGridView::widget([
        'dataProvider' => $provider,
    ]),
]); ?>