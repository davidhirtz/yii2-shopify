<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\modules\admin\controllers;

use davidhirtz\yii2\shopify\components\admin\ProductBatchRepository;
use davidhirtz\yii2\shopify\components\admin\ProductQuery;
use davidhirtz\yii2\shopify\components\admin\ProductRepository;
use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\modules\admin\data\ProductActiveDataProvider;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\web\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ProductController extends Controller
{
    use ModuleTrait;

    #[\Override]
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
        $provider = Yii::$container->get(ProductActiveDataProvider::class, [], [
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
                $this->success(Yii::t('shopify', 'The product was deleted because it was not found on Shopify anymore.'));
                return $this->redirect(['index']);
            }

            throw new NotFoundHttpException();
        }

        $api = Yii::$app->get('shopify')->getAdminApi();

        if ($api->getErrors()) {
            $this->error($api->getErrors());
            return $this->redirect(['index']);
        }

        $repository = new ProductRepository($data);
        $repository->save();

        $this->errorOrSuccess($repository->product, Yii::t('shopify', 'The product was updated via Shopify.'));
        return $this->redirect(['index']);
    }

    public function actionUpdateAll(): Response
    {
        $repository = new ProductBatchRepository();
        $repository->save();

        $api = Yii::$app->get('shopify')->getAdminApi();
        $this->errorOrSuccess($api->getErrors(), Yii::t('shopify', 'All products updated via Shopify.'));

        return $this->redirect(['index']);
    }
}
