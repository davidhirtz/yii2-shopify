<?php

namespace davidhirtz\yii2\shopify\modules\admin\controllers;

use davidhirtz\yii2\shopify\models\forms\ProductShopifyAdminRestApiForm;
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
                        'actions' => ['create', 'index', 'order', 'update', 'update-all', 'delete'],
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'order' => ['post'],
                    'upload' => ['post'],
                ],
            ],
        ]);
    }

    /**
     * @return string
     */
    public function actionIndex($q = null)
    {
        $provider = new ProductActiveDataProvider([
            'searchString' => $q,
        ]);

        return $this->render('index', [
            'provider' => $provider,
        ]);
    }

    /**
     * @param int $id
     * @return Response
     */
    public function actionUpdate($id)
    {
        $api = static::getModule()->getApi();

        if (!$data = $api->getProduct($id)) {
            throw new NotFoundHttpException();
        }

        $product = ProductShopifyAdminRestApiForm::loadOrCreateFromApiData($data);

        if (!$product->hasErrors()) {
            $this->success(Yii::t('shopify', 'The product was updated via Shopify.'));
        } else {
            $this->error($product);
        }

        return $this->redirect(['index']);
    }

    /**
     * @return Response
     */
    public function actionUpdateAll()
    {
        $api = static::getModule()->getApi();
        $products = $api->getProducts();

        foreach ($products as $data) {
            $product = ProductShopifyAdminRestApiForm::loadOrCreateFromApiData($data);

            if ($product->hasErrors()) {
                $this->error($product);
            }
        }

        ProductShopifyAdminRestApiForm::deleteProductsFromApiResult($products);

        $this->error($api->getErrors());
        return $this->redirect(['index']);
    }
}