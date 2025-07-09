<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components\admin;

use davidhirtz\yii2\shopify\models\Product;
use Yii;

class ProductsBuilder
{
    private readonly AdminApi $api;
    private array $productIds = [];

    public function __construct()
    {
        $this->api = Yii::$app->get('shopify')->getAdminApi();
    }

    public function save(): void
    {
        foreach ($this->getProducts() as $result) {
            $repository = new ProductRepository($result['node']);

            if ($repository->save()) {
                $this->productIds[] = $repository->product->id;
            }
        }

        $this->deleteRemovedProducts();
    }

    protected function getProducts(): ProductIterator
    {
        return new ProductIterator(20);
    }

    protected function deleteRemovedProducts(): void
    {
        $products = Product::find()
            ->where(['not in', 'id', $this->productIds])
            ->all();

        foreach ($products as $product) {
            $product->delete();
        }
    }

    public function getErrors(): array
    {
        return $this->api->getErrors();
    }
}
