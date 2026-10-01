<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Components\Admin;

use Hirtz\Shopify\Components\ComponentTrait;
use Hirtz\Shopify\Models\Product;
use Hirtz\Skeleton\Log\ActiveRecordErrorLogger;

class ProductVariantBatchRepository
{
    use ComponentTrait;

    /**
     * @var list<int>
     */
    private array $variantIds = [];

    /**
     * @var list<int>
     */
    private array $listedIds = [];
    private int $totalInventoryQuantity = 0;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(protected readonly Product $product, protected readonly array $data)
    {
    }

    public function save(): void
    {
        $edges = $this->data['variants']['edges'] ?? [];

        foreach ($edges as $data) {
            $this->saveProductVariantFromEdgeData($data);
        }

        $isComplete = true;

        if (count($edges) < $this->data['variantsCount']['count']) {
            $api = static::getShopify()->getAdminApi();
            $errorCount = count($api->getErrors());
            $cursor = end($edges)['cursor'] ?? null;

            foreach (new ProductVariantBatchQuery($this->product->id, cursor: $cursor) as $data) {
                $this->saveProductVariantFromEdgeData($data);
            }

            // A failed request ends the batch as if the list were complete
            $isComplete = count($api->getErrors()) === $errorCount;
        }

        $this->product->variant_id = $this->variantIds[0] ?? null;
        $this->product->total_inventory_quantity = $this->totalInventoryQuantity;
        $this->product->variant_count = $this->getTotalCount();

        if ($isComplete) {
            $this->deleteUnusedVariants();
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function saveProductVariantFromEdgeData(array $data): void
    {
        $variant = (new ProductVariantMapper($this->product, $data['node']))();
        $this->listedIds[] = $variant->id;
        $variant->position = $this->getTotalCount() + 1;

        if ($variant->save()) {
            $this->totalInventoryQuantity += $variant->inventory_tracked ? (int)$variant->inventory_quantity : 0;
            $this->variantIds[] = $variant->id;
        }

        if ($variant->hasErrors()) {
            ActiveRecordErrorLogger::log($variant);
        }
    }

    protected function deleteUnusedVariants(): void
    {
        $variants = $this->product->getVariants()
            ->andWhere(['not in', 'id', $this->listedIds])
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
