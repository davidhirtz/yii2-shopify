<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Components\Admin;

use Hirtz\Shopify\Components\ComponentTrait;
use Hirtz\Shopify\Models\Product;
use Hirtz\Skeleton\Log\ActiveRecordErrorLogger;

class ProductMediaBatchRepository
{
    use ComponentTrait;

    /**
     * @var list<int>
     */
    private array $imageIds = [];

    /**
     * @var list<int>
     */
    private array $listedIds = [];

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(protected readonly Product $product, protected readonly array $data)
    {
    }

    public function save(): void
    {
        $edges = $this->data['media']['edges'] ?? [];

        foreach ($edges as $data) {
            $this->saveProductImageFromEdgeData($data);
        }

        $isComplete = true;

        if (count($edges) < $this->data['mediaCount']['count']) {
            $api = static::getShopify()->getAdminApi();
            $errorCount = count($api->getErrors());
            $cursor = end($edges)['cursor'] ?? null;

            foreach (new ProductMediaBatchQuery($this->product->id, cursor: $cursor) as $data) {
                $this->saveProductImageFromEdgeData($data);
            }

            // A failed request ends the batch as if the list were complete
            $isComplete = count($api->getErrors()) === $errorCount;
        }

        $this->product->image_id = $this->imageIds[0] ?? null;
        $this->product->image_count = $this->getTotalCount();

        if ($isComplete) {
            $this->deleteUnusedImages();
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function saveProductImageFromEdgeData(array $data): void
    {
        $image = (new ProductMediaMapper($this->product, $data['node']))();
        $this->listedIds[] = $image->id;
        $image->position = $this->getTotalCount() + 1;

        if ($image->save()) {
            $this->imageIds[] = $image->id;
        }

        if ($image->hasErrors()) {
            ActiveRecordErrorLogger::log($image);
        }
    }

    protected function deleteUnusedImages(): void
    {
        $images = $this->product->getImages()
            ->andWhere(['not in', 'id', $this->listedIds])
            ->all();

        foreach ($images as $image) {
            $image->delete();
        }
    }

    protected function getTotalCount(): int
    {
        return count($this->imageIds);
    }
}
