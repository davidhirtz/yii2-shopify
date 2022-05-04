<?php

namespace davidhirtz\yii2\shopify\modules\admin\controllers;

use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\web\Controller;
use Yii;

/**
 * Class WebhookController
 * @package davidhirtz\yii2\shopify\modules\admin\controllers
 */
class WebhookController extends Controller
{
    use ModuleTrait;

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'webhooks' => static::getModule()->getApi()->getWebhooks(),
        ]);
    }
}