<?php
/**
 * Webhooks
 * @see \davidhirtz\yii2\cms\modules\admin\controllers\WebhookController::actionIndex()
 *
 * @var View $this
 * @var array $webhooks
 */

use davidhirtz\yii2\shopify\modules\admin\widgets\grid\WebhookGridView;
use davidhirtz\yii2\shopify\modules\admin\widgets\nav\ShopifySubmenu;
use davidhirtz\yii2\skeleton\web\View;
use davidhirtz\yii2\skeleton\widgets\bootstrap\Panel;

$this->setTitle(Yii::t('shopify', 'Webhooks'));
$this->setBreadcrumb(Yii::t('shopify', 'Webhooks'), ['/admin/shopify-webhook/index']);
?>

<?= ShopifySubmenu::widget(); ?>

<?= Panel::widget([
    'content' => WebhookGridView::widget([
        'webhooks' => $webhooks,
    ]),
]); ?>