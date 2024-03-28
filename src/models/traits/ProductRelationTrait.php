<?php

namespace davidhirtz\yii2\shopify\models\traits;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\models\queries\ProductQuery;

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
