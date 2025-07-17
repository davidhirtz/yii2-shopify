<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components\admin;

use davidhirtz\yii2\shopify\components\ShopifyDateTime;
use davidhirtz\yii2\shopify\components\ShopifyId;
use davidhirtz\yii2\shopify\models\Product;
use Yii;

readonly class ProductMapper
{
    protected Product $product;

    public function __construct(protected array $data)
    {
        $id = (new ShopifyId($data['id']))->toInt();

        $this->product = Product::findOne($id) ?? Product::create();
        $this->product->id ??= $id;

        $this->setAttributes();
    }

    protected function setAttributes(): void
    {
        $this->product->status = match ($this->data['status']) {
            'DRAFT' => $this->product::STATUS_DRAFT,
            'ARCHIVED' => $this->product::STATUS_DISABLED,
            default => $this->product::STATUS_ENABLED,
        };

        $this->product->content = $this->data['descriptionHtml'] ?: null;
        $this->product->name = $this->data['title'];
        $this->product->product_type = $this->data['productType'] ?: null;
        $this->product->slug = $this->data['handle'];
        $this->product->tags = $this->data['tags'] ?: null;
        $this->product->vendor = $this->data['vendor'] ?: null;


        Yii::debug($this->data['options'], 'options');
        $options = [];

        if (
            count($this->data['options']) !== 1
            || $this->data['options'][0]['name'] !== 'Title'
            || count($this->data['options'][0]['values']) !== 1
            || $this->data['options'][0]['values'][0] !== 'Default Title'
        ) {
            foreach ($this->data['options'] as $option) {
                $options[$option['name']] = $option['values'];
            }
        }

        ksort($options);
        $this->product->options = $options ?: null;

        $this->product->updated_at = (new ShopifyDateTime($this->data['updatedAt']))->toDateTime();
        $this->product->created_at = (new ShopifyDateTime($this->data['createdAt']))->toDateTime();
    }

    public function __invoke(): Product
    {
        return $this->product;
    }
}
