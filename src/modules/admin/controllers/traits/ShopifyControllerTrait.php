<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\modules\admin\controllers\traits;

use davidhirtz\yii2\shopify\components\ShopifyComponent;
use Override;
use Yii;

trait ShopifyControllerTrait
{
    protected ShopifyComponent $shopify;

    #[Override]
    public function init(): void
    {
        $this->shopify = Yii::$app->get('shopify');
        parent::init();
    }
}
