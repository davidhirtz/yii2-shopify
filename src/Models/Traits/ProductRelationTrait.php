<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Models\Traits;

use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Models\Queries\ProductQuery;

/**
 * The foreign key is deliberately not declared here: a trait `@property` is flattened into the using class, so a
 * second declaration of the same name there silently drops that class's whole PHPDoc scope instead of being
 * reported (monorepo issue #125). Each using model declares the column with its own nullability.
 *
 * @property-read Product|null $product {@see static::getProduct()}
 */
trait ProductRelationTrait
{
    /**
     * @return ProductQuery<Product>
     */
    public function getProduct(): ProductQuery
    {
        /** @var ProductQuery<Product> $relation */
        $relation = $this->hasOne(Product::class, ['id' => 'product_id']);
        return $relation;
    }

    public function populateProductRelation(?Product $product): void
    {
        $this->populateRelation('product', $product);
        $this->product_id = $product?->id;
    }
}
