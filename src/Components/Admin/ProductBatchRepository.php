<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Components\Admin;

use Hirtz\Shopify\Components\ComponentTrait;
use Hirtz\Shopify\Models\Product;

class ProductBatchRepository
{
    use ComponentTrait;

    /**
     * @var list<int>
     */
    private array $productIds = [];

    public function save(): void
    {
        $api = static::getShopify()->getAdminApi();
        $errorCount = count($api->getErrors());

        foreach ($this->getProducts() as $result) {
            $repository = new ProductRepository($result['node']);
            $repository->save();
            $this->productIds[] = (int)$repository->product->id;
        }

        // A failed request ends the batch as if the list were complete
        if (count($api->getErrors()) === $errorCount) {
            $this->deleteRemovedProducts();
        }
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
