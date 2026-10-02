<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Controllers;

use Hirtz\Shopify\Components\Admin\ProductQuery;
use Hirtz\Shopify\Components\Admin\ProductRepository;
use Hirtz\Shopify\Components\ComponentTrait;
use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Module;
use Hirtz\Shopify\Modules\ModuleTrait;
use Hirtz\Skeleton\Web\Controller;
use Override;
use Yii;
use yii\helpers\Json;
use yii\web\HttpException;
use yii\web\UnauthorizedHttpException;

/**
 * @extends Controller<Module>
 */
class WebhookController extends Controller
{
    use ComponentTrait;
    use ModuleTrait;

    /**
     * Disables CSRF validation for webhook endpoints
     */
    #[\Override]
    public function init(): void
    {
        $this->enableCsrfValidation = false;
        parent::init();
    }

    #[Override]
    public function beforeAction($action): bool
    {
        $hmacHeader = (string)$this->request->getHeaders()->get('X-Shopify-Hmac-Sha256');

        if (!static::getShopify()->validateHmac($hmacHeader, $this->getRequestBody())) {
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
     *
     * Shopify retries anything but a 2xx: a product it no longer has is a deletion, and only a failed request is
     * answered with an error, so that it is retried.
     */
    public function actionProductsUpdate(): void
    {
        $id = $this->getProductId();

        if ($id === null) {
            return;
        }

        $data = (new ProductQuery($id))();
        $api = static::getShopify()->getAdminApi();

        if ($data === null) {
            throw new HttpException(503);
        }

        if (!$data) {
            Product::findOne($id)?->delete();
            return;
        }

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
        $id = is_array($data) ? ($data['id'] ?? null) : null;

        return is_numeric($id) ? (int)$id : null;
    }

    private function getRequestBody(): string
    {
        return $this->request->getRawBody();
    }
}
