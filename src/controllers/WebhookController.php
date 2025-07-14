<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\controllers;

use davidhirtz\yii2\shopify\components\admin\ProductQuery;
use davidhirtz\yii2\shopify\components\admin\ProductRepository;
use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\web\Controller;
use Override;
use Yii;
use yii\helpers\Json;
use yii\web\UnauthorizedHttpException;

class WebhookController extends Controller
{
    use ModuleTrait;

    #[Override]
    public function init(): void
    {
        $this->enableCsrfValidation = false;
        parent::init();
    }

    #[Override]
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
        $this->actionProductsUpdate();
    }

    /**
     * Webhook endpoint for webhook topics "products/update".
     */
    public function actionProductsUpdate(): void
    {
        $id = $this->getProductId();
        $data = (new ProductQuery($id))();

        $api = Yii::$app->get('shopify')->getAdminApi();

        $repository = new ProductRepository($data);
        $repository->save();

        if ($api->getErrors()) {
            Yii::error($api->getErrors());
        }
    }

    /**
     * Webhook endpoint for webhook topic "products/delete".
     */
    public function actionProductsDelete(): void
    {
        $id = $this->getProductId();
        $product = Product::findOne($id);
        $product?->delete();
    }

    private function getProductId(): ?int
    {
        $body = $this->getRequestBody();
        $data = $body ? Json::decode($body) : [];

        return $data['id'] ?? null;
    }

    private function getRequestBody(): string|false
    {
        return file_get_contents('php://input');
    }
}
