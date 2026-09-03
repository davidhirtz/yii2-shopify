<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Controllers;

use Hirtz\Skeleton\I18n\Lang;
use Hirtz\Shopify\Components\Admin\ProductBatchRepository;
use Hirtz\Shopify\Components\Admin\ProductQuery;
use Hirtz\Shopify\Components\Admin\ProductRepository;
use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Modules\Admin\Data\ProductActiveDataProvider;
use Hirtz\Shopify\Modules\ModuleTrait;
use Hirtz\Skeleton\Web\Controller;
use Override;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ProductController extends Controller
{
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
                        'roles' => [Product::AUTH_PRODUCT_UPDATE],
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
        $data = (new ProductQuery($id))();

        if (!$data) {
            $product = Product::findOne($id);

            if ($product->delete()) {
                $this->success(Lang::t('shopify', 'PRODUCT_SUCCESS_DELETED'));
                return $this->redirect(['index']);
            }

            throw new NotFoundHttpException();
        }

        $api = Yii::$app->get('shopify')->getAdminApi();

        $repository = new ProductRepository($data);
        $repository->save();

        $this->error($api->getErrors());
        $this->errorOrSuccess($repository->product, Lang::t('shopify', 'PRODUCT_SUCCESS_UPDATED_SHOPIFY'));

        return $this->redirect(['index']);
    }

    public function actionUpdateAll(): Response
    {
        $repository = new ProductBatchRepository();
        $repository->save();

        $api = Yii::$app->get('shopify')->getAdminApi();
        $this->errorOrSuccess($api->getErrors(), Lang::t('shopify', 'PRODUCT_SUCCESS_UPDATED_PRODUCTS'));

        return $this->redirect(['index']);
    }
}
