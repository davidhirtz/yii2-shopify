<?php

namespace davidhirtz\yii2\shopify\modules\admin\controllers;

use davidhirtz\yii2\shopify\models\forms\ProductShopifyAdminApiForm;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\web\Controller;
use Shopify\Auth\FileSessionStorage;
use Shopify\Auth\OAuth;
use Shopify\Auth\OAuthCookie;
use Shopify\Clients\Rest;
use Shopify\Context;
use Shopify\Rest\Admin2022_04\Product;
use Shopify\Utils;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Cookie;

/**
 * Class ImportController
 * @package davidhirtz\yii2\shopify\modules\admin\controllers
 */
class ImportController extends Controller
{
    use ModuleTrait;

    /**
     */
    public function actionIndex()
    {
        $api = static::getModule()->getApi();
        $products = $api->getProducts();

        foreach ($api->getProducts() as $data) {
            $product = ProductShopifyAdminApiForm::loadOrCreateFromApiData($data);

            if ($product->hasErrors()) {
                $this->error($product);
            }
        }

        ProductShopifyAdminApiForm::deleteProductsFromApiData($products);

        $this->error($api->getErrors());
        return $this->redirect(['/admin/product/index']);
    }
}