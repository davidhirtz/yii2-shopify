<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Controllers;

use Hirtz\Shopify\Models\forms\ProductShopifyAdminRestApiForm;
use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Modules\Admin\Data\ProductActiveDataProvider;
use Hirtz\Shopify\Modules\ModuleTrait;
use Hirtz\Skeleton\Web\Controller;
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
