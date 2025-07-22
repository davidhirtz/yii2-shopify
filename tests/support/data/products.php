<?php

declare(strict_types=1);


use davidhirtz\yii2\shopify\models\Product;
use yii\db\Expression;

return [
    'product-1' => [
        'id' => 1,
        'status' => Product::STATUS_ENABLED,
        'variant_id' => 1,
        'image_id' => null,
        'name' => 'Test Product',
        'slug' => 'test-product',
        'options' => [
            [
                'name' => 'Size',
                'values' => ['XS', 'SM', 'LG', 'XL'],
            ],
        ],
        'variant_count' => 2,
        'last_import_at' => new Expression('UTC_TIMESTAMP()'),
        'created_at' => new Expression('UTC_TIMESTAMP()'),
    ],
];