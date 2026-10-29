<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Controllers;

use Hirtz\Shopify\Components\Admin\ProductBatchRepository;
use Hirtz\Shopify\Components\Admin\ProductQuery;
use Hirtz\Shopify\Components\Admin\ProductRepository;
use Hirtz\Shopify\Components\ComponentTrait;
use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Modules\Admin\Data\ProductActiveDataProvider;
use Hirtz\Shopify\Modules\Admin\Module;
use Hirtz\Shopify\Modules\ModuleTrait;
use Hirtz\Skeleton\Web\Controller;
use Override;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @extends Controller<Module>
 */
class ProductController extends Controller
{
    use ComponentTrait;
    use ModuleTrait;

    #[Override]
    public function behaviors(): array
    {
        return [
            ...parent::behaviors(),
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'update', 'update-all'],
                        'roles' => [Product::AUTH_SHOPIFY_PRODUCT],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'update' => ['post'],
                    'update-all' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(?int $status = null, ?string $q = null): Response|string
    {
        $provider = Yii::$container->get(ProductActiveDataProvider::class, config: [
            'status' => $status,
            'searchString' => $q,
        ]);

        return $this->render('index', [
            'provider' => $provider,
        ]);
    }

    public function actionUpdate(int $id): Response|string
    {
        if (!static::getShopify()->isAdminApiConfigured()) {
            $this->error(Yii::t('shopify', 'SHOPIFY_ADMIN_API_ERROR'));
            return $this->redirectToIndex();
        }

        $data = (new ProductQuery($id))();
        $api = static::getShopify()->getAdminApi();

        if ($data === null) {
            $this->error($api->getErrors());
            return $this->redirectToIndex();
        }

        if (!$data) {
            $product = Product::findOne($id);

            if ($product?->delete()) {
                $this->success(Yii::t('shopify', 'PRODUCT_DELETE_SUCCESS'));
                return $this->redirectToIndex();
            }

            throw new NotFoundHttpException();
        }

        $repository = new ProductRepository($data);
        $repository->save();

        $this->error($api->getErrors());
        $this->errorOrSuccess($repository->product, Yii::t('shopify', 'PRODUCT_UPDATE_SUCCESS'));

        return $this->redirectToIndex();
    }

    public function actionUpdateAll(): Response
    {
        if (!static::getShopify()->isAdminApiConfigured()) {
            $this->error(Yii::t('shopify', 'SHOPIFY_ADMIN_API_ERROR'));
            return $this->redirectToIndex();
        }

        $repository = new ProductBatchRepository();
        $repository->save();

        $api = static::getShopify()->getAdminApi();
        $this->errorOrSuccess($api->getErrors(), Yii::t('shopify', 'PRODUCT_RELOAD_SUCCESS'));

        return $this->redirectToIndex();
    }

    /**
     * Back to the grid the sync was started from, its filter, search and page included.
     */
    protected function redirectToIndex(): Response
    {
        return $this->redirect($this->request->getReferrer() ?? ['index']);
    }
}
