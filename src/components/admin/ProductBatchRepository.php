<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components\admin;

use davidhirtz\yii2\shopify\models\Product;

class ProductBatchRepository
{
    private array $productIds = [];

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

    protected function getProducts(): ProductBatchQuery
    {
        return new ProductBatchQuery(20);
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
}
