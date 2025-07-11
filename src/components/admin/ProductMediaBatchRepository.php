<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components\admin;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\models\ProductImage;
use davidhirtz\yii2\skeleton\log\ActiveRecordErrorLogger;
use Yii;

class ProductMediaBatchRepository
{
    private array $imageIds = [];

    public function __construct(protected readonly Product $product, protected readonly array $data)
    {
    }

    public function save(): void
    {
        $edges = $this->data['media']['edges'] ?? [];

        foreach ($edges as $data) {
            $this->saveProductImageFromEdgeData($data);
        }

        if (count($edges) < $this->data['mediaCount']['count']) {
            $cursor = end($edges)['cursor'] ?? null;

            foreach (new ProductMediaBatchQuery($this->product->id, cursor: $cursor) as $data) {
                $this->saveProductImageFromEdgeData($data);
            }
        }

        $this->product->image_id = $this->imageIds[0] ?? null;
        $this->product->image_count = $this->getTotalCount();

        $this->deleteUnusedImages();
    }

    protected function saveProductImageFromEdgeData(array $data): void
    {
        $image = (new ProductMediaMapper($this->product, $data['node']))();
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
            ->andWhere(['not in', 'id', $this->imageIds])
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
