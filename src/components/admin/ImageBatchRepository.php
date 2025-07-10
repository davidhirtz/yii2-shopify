<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components\admin;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\skeleton\log\ActiveRecordErrorLogger;
use Yii;

class ImageBatchRepository
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
            // Todo
            Yii::debug('Query for missing images ...');
        }

        $this->product->image_id = $this->imageIds[0] ?? null;
        $this->product->image_count = $this->getTotalCount();

        $this->deleteUnusedImages();
    }

    protected function saveProductImageFromEdgeData(array $data): void
    {
        $image = (new ImageMapper($this->product, $data['node']))();
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
        $images = $this->product->image_count ? $this->product->images : [];

        foreach ($images as $image) {
            if (!in_array($image->id, $this->imageIds)) {
                $image->delete();
            }
        }
    }

    protected function getTotalCount(): int
    {
        return count($this->imageIds);
    }
}
