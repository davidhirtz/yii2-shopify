<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\controllers;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\web\Controller;
use Yii;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

class WebhookController extends Controller
{
    use ModuleTrait;

    #[\Override]
    public function init(): void
    {
        $this->enableCsrfValidation = false;
        parent::init();
    }

    #[\Override]
    public function beforeAction($action): bool
    {
        $hmacHeader = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? '';
        $data = $this->getRequestBody();

        if (!Yii::$app->get('shopify')->validateHmac($hmacHeader, $data)) {
            throw new UnauthorizedHttpException();
        }

        return parent::beforeAction($action);
    }

    /**
     * Webhook endpoint for webhook topics "products/create".
     */
    public function actionProductsCreate(): void
    {
        // Todo
        $data = Json::decode($this->getRequestBody());

        Yii::warning("Webhook 'products/create' not implemented yet.");
        Yii::warning($data);
    }

    /**
     * Webhook endpoint for webhook topics "products/update".
     */
    public function actionProductsUpdate(): void
    {
        // Todo
        $data = Json::decode($this->getRequestBody());

        Yii::warning("Webhook 'products/create' not implemented yet.");
        Yii::warning($data);
    }

    /**
     * Webhook endpoint for webhook topic "products/delete".
     */
    public function actionProductsDelete(): void
    {
        // Todo verify
        $data = Json::decode(file_get_contents('php://input'));
        $product = Product::findOne($data['id'] ?? null);

        if (!$product) {
            throw new NotFoundHttpException();
        }

        $product->delete();
    }

    private function getRequestBody(): string|false
    {
        return file_get_contents('php://input');
    }
}