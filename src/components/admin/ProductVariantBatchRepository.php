<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components\admin;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\skeleton\log\ActiveRecordErrorLogger;

class ProductVariantBatchRepository
{
    private array $variantIds = [];
    private int $totalInventoryQuantity = 0;

    public function __construct(protected readonly Product $product, protected readonly array $data)
    {
    }

    public function save(): void
    {
        $edges = $this->data['variants']['edges'] ?? [];

        foreach ($edges as $data) {
            $this->saveProductVariantFromEdgeData($data);
        }

        if (count($edges) < $this->data['variantsCount']['count']) {
            $cursor = end($edges)['cursor'] ?? null;

            foreach (new ProductVariantBatchQuery($this->product->id, cursor: $cursor) as $data) {
                $this->saveProductVariantFromEdgeData($data);
            }
        }

        $this->product->variant_id = $this->variantIds[0] ?? null;
        $this->product->total_inventory_quantity = $this->totalInventoryQuantity;
        $this->product->variant_count = $this->getTotalCount();

        $this->deleteUnusedVariants();
    }

    protected function saveProductVariantFromEdgeData(array $data): void
    {
        $variant = (new ProductVariantMapper($this->product, $data['node']))();
        $variant->position = $this->getTotalCount() + 1;

        if ($variant->save()) {
            //            $this->totalInventoryQuantity += $variant->inventory_tracked ? $variant->inventory_quantity : 0;
            $this->totalInventoryQuantity += $variant->inventory_quantity;
            $this->variantIds[] = $variant->id;
        }

        if ($variant->hasErrors()) {
            ActiveRecordErrorLogger::log($variant);
        }
    }

    protected function deleteUnusedVariants(): void
    {
        $variants = $this->product->getVariants()
            ->andWhere(['not in', 'id', $this->variantIds])
            ->all();

        foreach ($variants as $variant) {
            $variant->delete();
        }
    }

    protected function getTotalCount(): int
    {
        return count($this->variantIds);
    }
}
