<?php

namespace davidhirtz\yii2\shopify\components\admin;

use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\shopify\models\Product;

readonly class AdminApiProductDataMapper
{
    public Product $product;

    public function __construct(protected array $data)
    {
        $this->product = Product::findOne($data['id']) ?? Product::create([
            'id' => $data['id'],
        ]);

        $this->product->last_import_at = new DateTime();
        $this->setAttributes();
    }

    private function setAttributes(): void
    {
        $this->product->status = match ($this->data['status']) {
            'DRAFT' => $this->product::STATUS_DRAFT,
            'ARCHIVED' => $this->product::STATUS_ARCHIVED,
            default => $this->product::STATUS_ENABLED,
        };

        $this->product->content = $this->data['descriptionHtml'] ?: null;
        $this->product->name = $this->data['title'];
        $this->product->product_type = $this->data['productType'] ?: null;
        $this->product->slug = $this->data['handle'];
        $this->product->tags = $this->data['tags'] ?: null;
        $this->product->vendor = $this->data['vendor'] ?: null;

        $options = [];

        if (
            count($this->data['options']) !== 1
            || $this->data['options'][0]['name'] !== 'Title'
            || count($this->data['options'][0]['values']) !== 1
            || $this->data['options'][0]['values'][0] !== 'Default Title'
        ) {
            foreach ($this->data['options'] as $option) {
                $options[$option['position']] = [
                    'name' => $option['name'],
                    'values' => $option['values'],
                ];
            }
        }

        ksort($options);
        $this->product->options = $options ?: null;
    }
}