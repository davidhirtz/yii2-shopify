<?php

namespace davidhirtz\yii2\shopify\modules\admin\controllers;

use davidhirtz\yii2\shopify\models\forms\ProductShopifyAdminRestApiForm;
use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\modules\admin\data\ProductActiveDataProvider;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\web\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class ProductController.
 * @package davidhirtz\yii2\shopify\modules\admin\controllers
 */
class ProductController extends Controller
{
    use ModuleTrait;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
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
        ]);
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

        $product = ProductShopifyAdminRestApiForm::createOrUpdateFromApiData($data);

        if (!$product->hasErrors()) {
            $this->success(Yii::t('shopify', 'The product was updated via Shopify.'));
        } else {
            $this->error($product);
        }

        return $this->redirect(['index']);
    }

    public function actionUpdateAll(): Response
    {
        $api = static::getModule()->getApi();
        $products = $api->getProducts();

        foreach ($products as $data) {
            $product = ProductShopifyAdminRestApiForm::createOrUpdateFromApiData($data);

            if ($product->hasErrors()) {
                $this->error($product);
            }
        }

        ProductShopifyAdminRestApiForm::deleteProductsFromApiResult($products);

        $this->error($api->getErrors());
        return $this->redirect(['index']);
    }
}