<?php

namespace davidhirtz\yii2\shopify\models\traits;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\models\queries\ProductQuery;

/**
 * @property-read Product $product {@see static::getProduct()}
 */
trait ProductRelationTrait
{
    public function getProduct(): ProductQuery
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    public function populateProductRelation(?Product $product): void
    {
        $this->populateRelation('product', $product);
        $this->product_id = $product?->id;
    }
}