<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Models\Traits;

use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Models\Queries\ProductQuery;

/**
 * @property int|null $product_id
 * @property-read Product|null $product {@see static::getProduct()}
 */
trait ProductRelationTrait
{
    public function getProduct(): ProductQuery
    {
        /** @var ProductQuery $relation */
        $relation = $this->hasOne(Product::class, ['id' => 'product_id']);
        return $relation;
    }

    public function populateProductRelation(?Product $product): void
    {
        $this->populateRelation('product', $product);
        $this->product_id = $product?->id;
    }
}
