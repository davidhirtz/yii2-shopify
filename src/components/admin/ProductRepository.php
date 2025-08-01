<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components\admin;

use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\skeleton\log\ActiveRecordErrorLogger;

readonly class ProductRepository
{
    public Product $product;

    public function __construct(protected array $data)
    {
        $this->product = (new ProductMapper($data))();
    }

    public function save(): bool
    {
        $this->product->last_import_at = new DateTime();
        $this->product->save();

        if ($this->product->hasErrors()) {
            ActiveRecordErrorLogger::log($this->product);
            return false;
        }

        $this->saveProductImages();
        $this->saveProductVariants();

        $this->product->update(false);

        return true;
    }

    protected function saveProductImages(): void
    {
        (new ProductMediaBatchRepository($this->product, $this->data))->save();
    }

    protected function saveProductVariants(): void
    {
        (new ProductVariantBatchRepository($this->product, $this->data))->save();
    }
}
