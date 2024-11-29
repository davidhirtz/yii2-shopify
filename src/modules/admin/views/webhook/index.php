<?php
declare(strict_types=1);

/**
 * @see WebhookController::actionIndex()
 *
 * @var View $this
 * @var array $webhooks
 */

use davidhirtz\yii2\shopify\modules\admin\controllers\WebhookController;
use davidhirtz\yii2\shopify\modules\admin\widgets\grids\WebhookGridView;
use davidhirtz\yii2\shopify\modules\admin\widgets\navs\ShopifySubmenu;
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
