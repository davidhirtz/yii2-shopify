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
use yii\web\UnauthorizedHttpException;

/**
 * @extends Controller<Module>
 */
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
     */
    public function actionProductsUpdate(): void
    {
        $id = $this->getProductId();
        $data = (new ProductQuery($id))();

        $api = static::getShopify()->getAdminApi();

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

    private function getRequestBody(): string
    {
        return $this->request->getRawBody();
    }
}
