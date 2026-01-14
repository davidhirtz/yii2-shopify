<?php

declare(strict_types=1);

use Hirtz\Shopify\Models\Product;
use yii\db\Expression;

return [
    'product-1' => [
        'id' => 1,
        'status' => Product::STATUS_ENABLED,
        'variant_id' => 1,
        'image_id' => 1,
        'name' => 'Sample T-Shirt',
        'content' => 'A comfortable cotton t-shirt.',
        'slug' => 'sample-t-shirt',
        'vendor' => 'Sample Vendor',
        'product_type' => 'Clothing',
        'options' => ['Size', 'Color'],
        'image_count' => 2,
        'variant_count' => 2,
        'total_inventory_quantity' => 15,
        'last_import_at' => new Expression('UTC_TIMESTAMP()'),
        'created_at' => new Expression('UTC_TIMESTAMP()'),
    ],
    'product-2' => [
        'id' => 2,
        'status' => Product::STATUS_ENABLED,
        'variant_id' => 3,
        'image_id' => 3,
        'name' => 'Sample Mug',
        'content' => 'A ceramic coffee mug.',
        'slug' => 'sample-mug',
        'vendor' => 'Another Vendor',
        'product_type' => 'Accessories',
        'options' => [],
        'image_count' => 1,
        'variant_count' => 1,
        'total_inventory_quantity' => 0,
        'last_import_at' => new Expression('UTC_TIMESTAMP()'),
        'created_at' => new Expression('UTC_TIMESTAMP()'),
    ],
];
