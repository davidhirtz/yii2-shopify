<?php

namespace davidhirtz\yii2\shopify\controllers;

use davidhirtz\yii2\shopify\models\forms\ProductShopifyAdminRestApiForm;
use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\Module;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\web\Controller;
use yii\base\Action;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

/**
 * Class WebhookController
 * @package davidhirtz\yii2\shopify\controllers
 */
class WebhookController extends Controller
{
    use ModuleTrait;

    /**
     * @var bool
     */
    public $enableCsrfValidation = false;

    /**
     * Validates webhooks from Shopify, this only works when is {@see Module::$shopifyApiSecret} set
     * @param Action $action
     * @return bool
     */
    public function beforeAction($action): bool
    {
        $hmacHeader = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? '';
        $data = file_get_contents('php://input');

        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, static::getModule()->shopifyApiSecret, true));

        if (!hash_equals($hmacHeader, $calculatedHmac)) {
            throw new UnauthorizedHttpException();
        }

        return parent::beforeAction($action);
    }

    /**
     * Webhook endpoint for webhook topics "products/create".
     * @return void
     */
    public function actionProductsCreate()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        ProductShopifyAdminRestApiForm::createOrUpdateFromApiData($data);
    }

    /**
     * Webhook endpoint for webhook topics "products/update".
     * @return void
     */
    public function actionProductsUpdate()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        ProductShopifyAdminRestApiForm::createOrUpdateFromApiData($data);
    }

    /**
     * Webhook endpoint for webhook topic "products/delete".
     * @return void
     */
    public function actionProductsDelete()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;

        if (!($product = $id ? Product::findOne($id) : null)) {
            throw new NotFoundHttpException();
        }

        $product->delete();
    }
}