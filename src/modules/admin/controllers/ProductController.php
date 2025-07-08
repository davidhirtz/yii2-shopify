<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\modules\admin\controllers;

use davidhirtz\yii2\shopify\models\forms\ProductShopifyAdminApiForm;
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
        $api = static::getModule()->getApi();

        if (!$data = $api->getProduct($id)) {
            throw new NotFoundHttpException();
        }

        $product = ProductShopifyAdminApiForm::createOrUpdateFromApiData($data);

        if (!$product->hasErrors()) {
            $this->success(Yii::t('shopify', 'The product was updated via Shopify.'));
        } else {
            $this->error($product);
        }

        return $this->redirect(['index']);
    }

    public function actionUpdateAll(): Response
    {
        $api = Yii::$app->get('shopify')->getAdminApi();

        foreach ($api->getProducts(2) as $result) {
            // Update products from API data
            dump($result['node']);
        }


        $this->error($api->getErrors());
        dd($api->getErrors());
        return $this->redirect(['index']);
    }

    private function updateAllInternal(?string $cursor = null): array|false
    {
        $api = static::getModule()->getApi();
        $limit = 20;

        $results = $api->getProducts($limit, $cursor);

        if ($errors = $api->getErrors()) {
            $this->error($errors);
            return false;
        }

        $productIds = [];

        foreach ($results as $result) {
            $product = ProductShopifyAdminApiForm::createOrUpdateFromApiData($result['node']);

            if ($product->hasErrors()) {
                $this->error($product);
            }

            if (!$product->getIsNewRecord()) {
                $productIds[] = $product->id;
            }
        }

        if (count($results) >= $limit) {
            $nextCursor = end($results)['cursor'];

            $productIds = [
                ...$productIds,
                $this->updateAllInternal($nextCursor),
            ];
        }

        if ($cursor === null) {
            ProductShopifyAdminApiForm::deleteProductsFromApiResult($productIds);
        }

        return $productIds;
    }
}
