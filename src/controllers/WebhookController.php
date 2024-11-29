<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\controllers;

use davidhirtz\yii2\shopify\models\forms\ProductShopifyAdminRestApiForm;
use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\Module;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\web\Controller;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

class WebhookController extends Controller
{
    use ModuleTrait;

    /**
     * Disables CSRF validation for webhook endpoints
     */
    public function init(): void
    {
        $this->enableCsrfValidation = false;
        parent::init();
    }

    /**
     * Validates webhooks from Shopify, this only works when is {@see Module::$shopifyApiSecret} set
     */
    public function beforeAction($action): bool
    {
        $hmacHeader = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? '';
        $data = file_get_contents('php://input');

        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, (string) static::getModule()->shopifyApiSecret, true));

        if (!hash_equals($hmacHeader, $calculatedHmac)) {
            throw new UnauthorizedHttpException();
        }

        return parent::beforeAction($action);
    }

    /**
     * Webhook endpoint for webhook topics "products/create".
     */
    public function actionProductsCreate(): void
    {
        $data = Json::decode(file_get_contents('php://input'));
        ProductShopifyAdminRestApiForm::createOrUpdateFromApiData($data);
    }

    /**
     * Webhook endpoint for webhook topics "products/update".
     */
    public function actionProductsUpdate(): void
    {
        $data = Json::decode(file_get_contents('php://input'));
        ProductShopifyAdminRestApiForm::createOrUpdateFromApiData($data);
    }

    /**
     * Webhook endpoint for webhook topic "products/delete".
     */
    public function actionProductsDelete(): void
    {
        $data = Json::decode(file_get_contents('php://input'));
        $product = Product::findOne($data['id'] ?? null);

        if (!$product) {
            throw new NotFoundHttpException();
        }

        $product->delete();
    }
}
