<?php
declare(strict_types=1);

/**
 * @see WebhookController::actionIndex()
 *
 * @var View $this
 * @var array $webhooks
 */

use Hirtz\Shopify\Modules\Admin\Controllers\WebhookController;
use Hirtz\Shopify\Modules\Admin\Widgets\Grids\WebhookGridView;
use Hirtz\Shopify\Modules\Admin\Widgets\Navs\ShopifySubmenu;
use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Bootstrap\Panel;

$this->title(Yii::t('shopify', 'Webhooks'));
$this->setBreadcrumb(Yii::t('shopify', 'Webhooks'), ['/admin/shopify-webhook/index']);
?>

<?= ShopifySubmenu::widget(); ?>

<?= Panel::widget([
    'content' => WebhookGridView::widget([
        'webhooks' => $webhooks,
    ]),
]); ?>
