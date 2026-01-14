<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Components\Admin;

use Hirtz\Shopify\Components\ShopifyId;
use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Models\ProductImage;

readonly class ProductMediaMapper
{
    protected ProductImage $image;

    public function __construct(protected Product $product, protected array $data)
    {
        $id = (new ShopifyId($data['id']))->toInt();

        $this->image = ProductImage::findOne([
            'id' => $id,
            'product_id' => $this->product->id,
        ]) ?? ProductImage::create();

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
