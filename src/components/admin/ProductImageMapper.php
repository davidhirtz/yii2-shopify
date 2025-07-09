<?php

namespace davidhirtz\yii2\shopify\components\admin;

use davidhirtz\yii2\shopify\components\ShopifyId;
use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\models\ProductImage;

readonly class ProductImageMapper
{
    protected ProductImage $image;

    public function __construct(protected Product $product, protected array $data)
    {
        $id = (new ShopifyId($data['id']))->toInt();

        $this->image = ProductImage::findOne($id) ?? ProductImage::create();

        $this->image->id = $id;
        $this->image->populateProductRelation($this->product);

        $this->setAttributes();
    }

    protected function setAttributes(): void
    {
        $this->image->alt_text = $this->data['preview']['image']['altText'];
        $this->image->height = $this->data['preview']['image']['height'] ?? null;
        $this->image->width = $this->data['preview']['image']['width'] ?? null;
        $this->image->src = $this->data['preview']['image']['url'] ?? null;
    }

    public function __invoke(): ProductImage
    {
        return $this->image;
    }
}
