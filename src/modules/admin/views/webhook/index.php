<?php
declare(strict_types=1);

/**
 * @see WebhookController::actionIndex()
 *
 * @var View $this
 * @var array $webhooks
 */

use Hirtz\Shopify\modules\admin\controllers\WebhookController;
use Hirtz\Shopify\modules\admin\widgets\grids\WebhookGridView;
use Hirtz\Shopify\modules\admin\widgets\navs\ShopifySubmenu;
use Hirtz\Skeleton\web\View;
use Hirtz\Skeleton\widgets\bootstrap\Panel;

$this->title(Yii::t('shopify', 'Webhooks'));
$this->setBreadcrumb(Yii::t('shopify', 'Webhooks'), ['/admin/shopify-webhook/index']);
?>

<?= ShopifySubmenu::widget(); ?>

<?= Panel::widget([
    'content' => WebhookGridView::widget([
        'webhooks' => $webhooks,
    ]),
]); ?>
