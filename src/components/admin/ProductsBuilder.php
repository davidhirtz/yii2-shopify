<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components\admin;

use davidhirtz\yii2\shopify\models\Product;
use Yii;

class ProductsBuilder
{
    private readonly AdminApi $api;
    private array $productIds = [];
    private int $createdCount = 0;
    private int $deletedCount = 0;

    public function __construct()
    {
        $this->api = Yii::$app->get('shopify')->getAdminApi();
    }

    public function save(): void
    {
        foreach ($this->api->getProducts(2) as $result) {
            $repository = new ProductRepository($result['node']);
            $isNewRecord = $repository->product->getIsNewRecord();

            if ($repository->save()) {
                if ($isNewRecord) {
                    $this->createdCount++;
                }

                $this->productIds[] = $repository->product->id;
            }
        }

        $this->deleteRemovedProducts();
    }

    protected function deleteRemovedProducts(): void
    {
        $products = Product::find()
            ->where(['not in', 'id', $this->productIds])
            ->all();

        foreach ($products as $product) {
            if ($product->delete()) {
                $this->deletedCount++;
            }
        }
    }

    public function getCreatedCount(): int
    {
        return $this->createdCount;
    }

    public function getDeletedCount(): int
    {
        return $this->deletedCount;
    }

    public function getErrors(): array
    {
        return $this->api->getErrors();
    }
}
